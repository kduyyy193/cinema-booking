(function () {
    var box = document.getElementById('ticket-qr');
    if (box && window.QRCode) {
        new QRCode(box, { text: box.dataset.code, width: 150, height: 150 });
    }
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
    var printButton = document.getElementById('print-ticket');
    if (printButton) printButton.addEventListener('click', function () { window.print(); });
})();
