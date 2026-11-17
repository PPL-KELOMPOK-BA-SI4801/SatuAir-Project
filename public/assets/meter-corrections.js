(() => {
    const form = document.querySelector('#meter-form[data-correction="1"]');
    if (form) {
        const input = form.querySelector('#meter-akhir');
        const reason = form.querySelector('#correction-reason');
        const save = form.querySelector('[data-correction-save]');
        function update() {
            const [whole, decimal = ''] = input.value.trim().replace(',', '.').split('.');
            const value = Number(whole) * 100 + Number(decimal.padEnd(2, '0'));
            save.disabled = !input.value.trim() || input.getAttribute('aria-invalid') === 'true'
                || value === Number(form.dataset.original) || !reason.value.trim();
        }
        form.addEventListener('input', update);
        update();
    }

    const dialog = document.querySelector('#cancel-reading-dialog');
    if (!dialog) return;
    const reason = dialog.querySelector('#cancel-reason');
    const confirm = dialog.querySelector('[data-confirm-cancel]');
    const update = () => { confirm.disabled = !reason.value.trim(); };
    document.querySelector('[data-open-cancel]').addEventListener('click', () => dialog.showModal());
    dialog.querySelector('[data-close-cancel]').addEventListener('click', () => dialog.close());
    reason.addEventListener('input', update);
    update();
    if (dialog.dataset.reopen === '1') dialog.showModal();
})();
