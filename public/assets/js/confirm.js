(function () {
    var modalEl = document.getElementById('confirm-modal');
    if (!modalEl || !window.bootstrap) return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var titleEl = modalEl.querySelector('[data-confirm-title]');
    var bodyEl = modalEl.querySelector('[data-confirm-body]');
    var okButton = modalEl.querySelector('[data-confirm-ok]');
    var pending = null;

    function pick(form, submitter, name) {
        var key = 'confirm' + name;
        if (submitter && submitter.dataset[key]) return submitter.dataset[key];
        return form.dataset[key] || '';
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.confirmed) { form.confirmed = false; return; }
        var submitter = event.submitter || null;
        var message = pick(form, submitter, '');
        if (!message) return;
        event.preventDefault();
        pending = { form: form, submitter: submitter };
        titleEl.textContent = pick(form, submitter, 'Title') || 'Xác nhận';
        bodyEl.textContent = message;
        okButton.textContent = pick(form, submitter, 'Button') || 'Xác nhận';
        modal.show();
    });

    okButton.addEventListener('click', function () {
        if (!pending) return;
        var target = pending;
        pending = null;
        target.form.confirmed = true;
        modal.hide();
        if (target.form.requestSubmit) target.form.requestSubmit(target.submitter || undefined);
        else target.form.submit();
    });

    modalEl.addEventListener('hidden.bs.modal', function () { pending = null; });
})();
