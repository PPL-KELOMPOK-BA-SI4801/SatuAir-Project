// Jalankan dengan SQLite baru berisi Pbi09DemoSeeder dan server terpisah; lihat docs/PBI-09.md.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const path = require('node:path');
const baseUrl = process.env.METER_TEST_BASE_URL || 'http://127.0.0.1:8001';

(async () => {
    const browser = await chromium.launch({channel: 'msedge', headless: true});
    try {
        const context = await browser.newContext({viewport: {width: 1600, height: 1280}});
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', e => errors.push(e.message));
        await page.goto(baseUrl + '/petugas/meters');
        assert.match(page.url(), /\/login$/);
        await page.waitForFunction(() => Boolean(window.Alpine));
        await page.getByRole('button', {name: 'Petugas', exact: true}).click();
        assert.equal(await page.locator('#role_field').inputValue(), 'petugas');
        await page.getByLabel('Email', {exact: true}).fill('petugas@gmail.com');
        await page.getByLabel('Kata Sandi', {exact: true}).fill('password123');
        await Promise.all([
            page.waitForURL(baseUrl + '/petugas/meters'),
            page.getByRole('button', {name: 'Masuk ke Akun', exact: true}).click(),
        ]);

        const catat = name => page.locator('tbody tr').filter({hasText: name})
            .getByRole('link', {name: 'Catat', exact: true});
        await catat('Rina Marlina').click();
        assert.equal(await page.locator('#meter-form h2').innerText(), 'Rina Marlina · CUST-00001');
        assert.equal(await page.locator('#meter-awal').inputValue(), '530,00');
        await page.getByRole('link', {name: 'Batal', exact: true}).click();
        await catat('Budi Santoso').click();
        const formBudi = page.url();
        const readingDate = await page.locator('#reading-date').inputValue();
        assert.equal(await page.locator('#meter-form h2').innerText(), 'Budi Santoso · CUST-00002');
        assert.equal(await page.locator('#meter-awal').inputValue(), '317,00');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '— m³');
        const input = page.locator('#meter-akhir');
        await input.fill('342,00');
        assert.equal(await page.locator('#rumus').innerText(), '342,00 − 317,00 = 25,00 m³');
        await input.fill('317,00');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '0,00 m³');
        await input.fill('342.25');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '25,25 m³');
        for (const value of ['316', 'abc', '-1', '', '342,001', '100000000']) {
            await input.fill(value);
            assert.equal(await page.locator('#total-pemakaian').innerText(), '— m³');
        }
        await input.fill('316');
        await Promise.all([
            page.waitForNavigation(),
            page.getByRole('button', {name: 'Simpan & Buat Tagihan', exact: true}).click(),
        ]);
        assert.match(await page.locator('main').innerText(), /Pencatatan belum disimpan/);
        assert.equal(await page.locator('#meter-error').innerText(), 'Meter akhir tidak boleh lebih kecil dari meter awal.');
        assert.equal(await page.locator('#meter-form').getAttribute('data-invalid'), '1');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '— m³');

        await page.getByRole('link', {name: 'Batal', exact: true}).click();
        await page.getByPlaceholder('Cari nama atau nomor meter').fill('MTR-CBD-002');
        await page.getByRole('button', {name: 'Filter', exact: true}).click();
        assert.equal(await page.locator('tbody tr').count(), 1);
        assert.match(await page.locator('tbody').innerText(), /Budi Santoso/);
        await page.getByPlaceholder('Cari nama atau nomor meter').fill('');
        await page.locator('select[name="area_id"]').selectOption({label: 'Dusun I - Cibodas Krajan'});
        await page.getByRole('button', {name: 'Filter', exact: true}).click();
        assert.equal(await page.locator('tbody tr').count(), 1);
        assert.match(await page.locator('tbody').innerText(), /Rina Marlina/);

        await page.goto(formBudi);
        await input.fill('342,00');
        const artifactDir = process.env.METER_TEST_ARTIFACT_DIR;
        if (artifactDir) await page.screenshot({path: path.join(artifactDir, 'pbi09-desktop.png'), fullPage: true});
        await page.setViewportSize({width: 390, height: 844});
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
        const startBox = await page.locator('#meter-awal').boundingBox();
        const endBox = await input.boundingBox();
        assert.ok(endBox.y > startBox.y + startBox.height, 'Input harus bertumpuk pada layar ponsel.');
        if (artifactDir) await page.screenshot({path: path.join(artifactDir, 'pbi09-mobile.png'), fullPage: true});

        await Promise.all([
            page.waitForURL(baseUrl + '/petugas/meters'),
            page.getByRole('button', {name: 'Simpan & Buat Tagihan', exact: true}).click(),
        ]);
        assert.match(await page.locator('body').innerText(), /berhasil disimpan/);
        assert.match(await page.locator('tbody tr').filter({hasText: 'Budi Santoso'}).innerText(), /342\.00 m³/);
        await page.goto(formBudi);
        assert.match(await page.locator('main').innerText(), /sudah dicatat/);
        assert.equal(await input.isDisabled(), true);
        assert.equal(await page.getByRole('button', {name: 'Simpan & Buat Tagihan', exact: true}).isDisabled(), true);

        const token = await page.locator('#meter-form input[name="_token"]').inputValue();
        const meterId = await page.locator('input[name="water_meter_id"]').inputValue();
        const duplicate = await context.request.post(baseUrl + '/petugas/meters', {
            form: {_token: token, water_meter_id: meterId, reading_value: '999', reading_date: readingDate},
            headers: {Referer: formBudi}, maxRedirects: 0,
        });
        assert.equal(duplicate.status(), 302);
        await page.goto(formBudi);
        assert.match(await page.locator('main').innerText(), /Pelanggan ini sudah dicatat pada periode yang sama/);

        await page.getByRole('link', {name: 'Lihat rincian pencatatan', exact: true}).click();
        const detailUrl = page.url();
        assert.equal(await page.locator('#total-pemakaian').innerText(), '25,00 m³');
        const stalePage = await context.newPage();
        await stalePage.goto(detailUrl + '/edit');
        await page.getByRole('link', {name: 'Edit Pencatatan', exact: true}).click();
        const saveChanges = page.getByRole('button', {name: 'Simpan Perubahan', exact: true});
        assert.equal(await saveChanges.isDisabled(), true);
        await page.locator('#correction-reason').fill('Koreksi sesuai foto meter.');
        await input.fill('316,00');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '— m³');
        assert.equal(await saveChanges.isDisabled(), true);
        await input.fill('317,00');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '0,00 m³');
        await input.fill('347,00');
        assert.equal(await page.locator('#total-pemakaian').innerText(), '30,00 m³');
        if (artifactDir) await page.screenshot({path: path.join(artifactDir, 'pbi09-edit-mobile.png'), fullPage: true});
        await Promise.all([page.waitForURL(detailUrl), saveChanges.click()]);
        assert.equal(await page.locator('#total-pemakaian').innerText(), '30,00 m³');
        assert.match(await page.getByRole('region', {name: 'Riwayat pencatatan'}).innerText(), /342,00 menjadi 347,00/);

        await stalePage.locator('#meter-akhir').fill('350,00');
        await stalePage.locator('#correction-reason').fill('Perubahan dari tab lama.');
        await Promise.all([stalePage.waitForNavigation(), stalePage.getByRole('button', {name: 'Simpan Perubahan', exact: true}).click()]);
        assert.match(await stalePage.locator('main').innerText(), /Data telah berubah sejak halaman dibuka/);
        await stalePage.close();

        await page.setViewportSize({width: 1600, height: 1280});
        await page.getByRole('button', {name: 'Batalkan Pencatatan', exact: true}).click();
        const dialog = page.getByRole('dialog');
        const confirmCancel = dialog.getByRole('button', {name: 'Ya, Batalkan Pencatatan', exact: true});
        assert.equal(await confirmCancel.isDisabled(), true);
        await page.locator('#cancel-reason').press('Escape');
        assert.equal(await dialog.isVisible(), false);
        await page.getByRole('button', {name: 'Batalkan Pencatatan', exact: true}).click();
        await page.locator('#cancel-reason').fill('Pencatatan pelanggan ini dibatalkan untuk diperbaiki.');
        if (artifactDir) await page.screenshot({path: path.join(artifactDir, 'pbi09-cancel-dialog.png'), fullPage: true});
        await Promise.all([page.waitForNavigation(), confirmCancel.click()]);
        assert.match(await page.locator('main').innerText(), /Pencatatan dibatalkan/);
        assert.equal(await page.getByRole('button', {name: 'Edit Pencatatan', exact: true}).isDisabled(), true);
        assert.equal(await page.getByRole('button', {name: 'Batalkan Pencatatan', exact: true}).isDisabled(), true);
        await page.getByRole('link', {name: 'Daftar pencatatan'}).click();
        assert.match(await page.locator('tbody tr').filter({hasText: 'Budi Santoso'}).innerText(), /317\.00 m³/);
        assert.match(await page.locator('tbody tr').filter({hasText: 'Budi Santoso'}).innerText(), /Pencatatan terakhir dibatalkan/);

        await page.goto(formBudi);
        assert.equal(await input.isEnabled(), true);
        assert.equal(await page.locator('#meter-awal').inputValue(), '317,00');
        await input.fill('342,00');
        await Promise.all([page.waitForURL(baseUrl + '/petugas/meters'), page.getByRole('button', {name: 'Simpan & Buat Tagihan', exact: true}).click()]);
        await page.getByRole('link', {name: 'Rincian Budi Santoso', exact: true}).click();
        assert.equal(await page.locator('#total-pemakaian').innerText(), '25,00 m³');
        assert.deepEqual(errors, []);
        console.log('PASS browser: login Petugas, daftar/filter PBI-08, live 25/0/25,25, validasi, simpan/duplikat, edit dan hitung ulang, riwayat, konflik dua tab, dialog pembatalan, meter awal kembali dan pencatatan ulang, desktop/mobile, tanpa error JavaScript.');
    } finally {
        await browser.close();
    }
})().catch(error => {console.error(error); process.exitCode = 1;});
