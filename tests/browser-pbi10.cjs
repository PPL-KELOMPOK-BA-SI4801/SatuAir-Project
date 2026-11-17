// Gunakan database baru berisi DatabaseSeeder dan periode aktif. Jangan jalankan pada database kerja.
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const path = require('node:path');
const base = process.env.METER_TEST_BASE_URL || 'http://127.0.0.1:8002';

(async () => {
    const browser = await chromium.launch({channel: 'msedge', headless: true});
    try {
        const errors = [];
        const officer = await browser.newContext({viewport: {width: 1440, height: 1100}});
        const treasurer = await browser.newContext({viewport: {width: 1440, height: 1100}});
        const petugas = await officer.newPage();
        const admin = await treasurer.newPage();
        for (const page of [petugas, admin]) page.on('pageerror', error => errors.push(error.message));
        async function login(page, role, email) {
            await page.goto(base + '/login');
            await page.waitForFunction(() => Boolean(window.Alpine));
            await page.getByRole('button', {name: role, exact: true}).click();
            await page.getByLabel('Email', {exact: true}).fill(email);
            await page.getByLabel('Kata Sandi', {exact: true}).fill('password123');
            await Promise.all([page.waitForURL('**/dashboard'), page.getByRole('button', {name: 'Masuk ke Akun', exact: true}).click()]);
        }
        await login(petugas, 'Petugas', 'petugas@gmail.com');
        await petugas.goto(base + '/petugas/meters');
        await petugas.locator('tbody tr').filter({hasText: 'Budi Santoso'}).getByRole('link', {name: 'Catat', exact: true}).click();
        assert.equal(await petugas.locator('#meter-awal').inputValue(), '317,00');
        await petugas.locator('#meter-akhir').fill('342,00');
        assert.equal(await petugas.locator('#total-pemakaian').innerText(), '25,00 m³');
        await Promise.all([petugas.waitForURL(base + '/petugas/meters'), petugas.getByRole('button', {name: 'Simpan Pencatatan', exact: true}).click()]);
        assert.match(await petugas.locator('tbody tr').filter({hasText: 'Budi Santoso'}).innerText(), /Menunggu pemeriksaan/);
        await petugas.getByRole('link', {name: 'Rincian Budi Santoso', exact: true}).click();
        const officerDetail = petugas.url();

        await login(admin, 'Bendahara', 'bendahara@gmail.com');
        await admin.goto(base + '/bendahara/pemeriksaan');
        await admin.getByPlaceholder('Cari pelanggan atau meter').fill('MTR-CBD-002');
        await admin.getByRole('button', {name: 'Filter', exact: true}).click();
        assert.equal(await admin.locator('tbody tr').count(), 1);
        await admin.getByRole('link', {name: 'Periksa Budi Santoso', exact: true}).click();
        const readingDetail = admin.url();
        const checkIdentity = () => admin.getByLabel('Identitas pelanggan dan periode sudah sesuai', {exact: true});
        const checkMeter = () => admin.getByLabel('Angka meter, bukti yang tersedia, dan hasil pemakaian sudah diperiksa', {exact: true});
        async function draft(notes, checked = true) {
            await admin.goto(readingDetail);
            await admin.getByRole('link', {name: 'Buat Pemeriksaan', exact: true}).click();
            if (checked) { await checkIdentity().check(); await checkMeter().check(); }
            await admin.getByLabel('Catatan Pemeriksaan', {exact: true}).fill(notes);
            await Promise.all([admin.waitForURL(/\/bendahara\/pemeriksaan\/\d+$/), admin.getByRole('button', {name: 'Simpan Draf', exact: true}).click()]);
        }
        await draft('Draf pemeriksaan pertama.');
        await admin.getByRole('link', {name: 'Edit Draf', exact: true}).click();
        await admin.getByLabel('Catatan Pemeriksaan', {exact: true}).fill('Draf pertama diperbarui sebelum dibatalkan.');
        await Promise.all([admin.waitForURL(/\/bendahara\/pemeriksaan\/\d+$/), admin.getByRole('button', {name: 'Simpan Perubahan Draf', exact: true}).click()]);
        assert.match(await admin.getByRole('region', {name: 'Hasil pemeriksaan'}).innerText(), /Draf pertama diperbarui/);
        admin.once('dialog', dialog => dialog.accept());
        await Promise.all([admin.waitForURL(readingDetail), admin.getByRole('button', {name: 'Batalkan Draf', exact: true}).click()]);
        assert.match(await admin.getByRole('region', {name: 'Riwayat pemeriksaan'}).innerText(), /Draf dibatalkan/);

        await draft('Angka foto berbeda. Koreksi meter akhir menjadi 347,00.', false);
        const returnedUrl = admin.url();
        await Promise.all([admin.waitForNavigation(), admin.getByRole('button', {name: 'Kembalikan untuk Koreksi', exact: true}).click()]);
        assert.match(await admin.locator('main').innerText(), /Dikembalikan untuk koreksi/);
        async function correct(value) {
            await petugas.goto(officerDetail + '/edit');
            await petugas.locator('#meter-akhir').fill(value);
            await petugas.locator('#correction-reason').fill('Koreksi angka sesuai pemeriksaan Bendahara.');
            await Promise.all([petugas.waitForURL(officerDetail), petugas.getByRole('button', {name: 'Simpan Perubahan', exact: true}).click()]);
        }
        await correct('347,00');
        assert.equal(await petugas.locator('#total-pemakaian').innerText(), '30,00 m³');
        assert.match(await petugas.locator('main').innerText(), /Menunggu pemeriksaan ulang/);
        await admin.goto(returnedUrl);
        assert.match(await admin.getByRole('region', {name: 'Snapshot pemeriksaan'}).innerText(), /25,00 m³/);

        await draft('Angka diperiksa setelah koreksi.');
        const finalUrl = admin.url();
        await correct('352,00');
        await admin.reload();
        assert.equal(await admin.getByRole('button', {name: 'Setujui & Terbitkan Tagihan', exact: true}).isDisabled(), true);
        await admin.getByRole('link', {name: 'Edit Draf', exact: true}).click();
        assert.equal(await checkIdentity().isChecked(), false);
        assert.equal(await checkMeter().isChecked(), false);
        await checkIdentity().check(); await checkMeter().check();
        await admin.getByLabel('Catatan Pemeriksaan', {exact: true}).fill('Angka terbaru 352,00 sudah diperiksa.');
        await Promise.all([admin.waitForURL(finalUrl), admin.getByRole('button', {name: 'Simpan Perubahan Draf', exact: true}).click()]);
        const artifactDir = process.env.METER_TEST_ARTIFACT_DIR;
        if (artifactDir) await admin.screenshot({path: path.join(artifactDir, 'pbi10-draft-desktop.png'), fullPage: true});
        await Promise.all([admin.waitForNavigation(), admin.getByRole('button', {name: 'Setujui & Terbitkan Tagihan', exact: true}).click()]);
        assert.match(await admin.locator('main').innerText(), /Tagihan #\d+ telah diterbitkan untuk pemakaian 35,00 m³/);
        assert.equal(await admin.getByRole('link', {name: 'Edit Draf', exact: true}).count(), 0);
        await petugas.goto(officerDetail + '/edit');
        assert.equal(await petugas.getByRole('button', {name: 'Edit Pencatatan', exact: true}).isDisabled(), true);
        await admin.setViewportSize({width: 390, height: 844});
        assert.equal(await admin.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
        if (artifactDir) await admin.screenshot({path: path.join(artifactDir, 'pbi10-approved-mobile.png'), fullPage: true});
        assert.deepEqual(errors, []);
        console.log('PASS PBI-10 browser: pencatatan menunggu, filter/rincian, CRUD draf, pengembalian, koreksi PBI-09, snapshot riwayat, perlindungan draf lama, pemeriksaan ulang, persetujuan/tagihan 35 m³, penguncian Petugas, desktop/mobile tanpa error JavaScript.');
    } finally { await browser.close(); }
})().catch(error => {console.error(error); process.exitCode = 1;});
