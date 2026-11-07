(() => {
    const form = document.querySelector('#meter-form');
    if (!form) return;
    const input = document.querySelector('#meter-akhir');
    const total = document.querySelector('#total-pemakaian');
    const formula = document.querySelector('#rumus');
    const error = document.querySelector('#meter-error');
    const start = Number(form.dataset.awal);
    const format = value => (value / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    function update() {
        const value = input.value.trim().replace(',', '.');
        total.textContent = '— m³';
        formula.textContent = 'Isi meter akhir yang valid untuk melihat perhitungan.';
        error.textContent = '';
        input.removeAttribute('aria-invalid');
        if (!value) return;
        if (!/^\d{1,9}(?:\.\d{1,2})?$/.test(value)) {
            error.textContent = 'Isi angka positif atau nol, maksimal 9 digit dan 2 desimal.';
            input.setAttribute('aria-invalid', 'true');
            return;
        }
        const [whole, decimals = ''] = value.split('.');
        const end = Number(whole) * 100 + Number(decimals.padEnd(2, '0'));
        if (end < start) {
            error.textContent = 'Meter akhir tidak boleh lebih kecil dari meter awal.';
            input.setAttribute('aria-invalid', 'true');
            return;
        }
        total.textContent = `${format(end - start)} m³`;
        formula.textContent = `${format(end)} − ${format(start)} = ${format(end - start)} m³`;
    }
    input.addEventListener('input', update);
    // Setelah validasi server gagal, jangan menampilkan hasil lama saat halaman dimuat.
    if (form.dataset.invalid !== '1' && !input.disabled) update();
})();
