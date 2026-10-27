(function () {
    var root = document.getElementById('pay-root');
    if (!root) return;

    var transaction = root.dataset.transaction;
    var status = root.dataset.status;
    var expiresAt = parseInt(root.dataset.expires, 10) * 1000;
    var countdown = document.getElementById('countdown');
    var qrBox = document.getElementById('qr-box');
    var form = document.getElementById('card-form');
    var polling = null;

    if (qrBox && window.QRCode) {
        new QRCode(qrBox, { text: qrBox.dataset.qr, width: 180, height: 180 });
    }

    function pad(n) { return n < 10 ? '0' + n : String(n); }

    function tick() {
        if (!countdown) return;
        var left = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        countdown.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        if (left === 0) {
            stopPolling();
            window.location.reload();
        }
    }

    function stopPolling() {
        if (polling) { clearInterval(polling); polling = null; }
    }

    function poll() {
        fetch('/payment/status?transaction_code=' + encodeURIComponent(transaction), { credentials: 'same-origin' })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.status === 'success' || data.booking_status === 'confirmed') {
                    stopPolling();
                    window.location.href = '/payment/success?code=' + encodeURIComponent(data.booking_code);
                } else if (data.status === 'failed' || data.status === 'expired') {
                    stopPolling();
                    window.location.reload();
                }
            })
            .catch(function () {});
    }

    function startPolling() {
        if (!polling) polling = setInterval(poll, 1000);
    }

    if (form) {
        var number = form.querySelector('[name="card_number"]');
        var expiry = form.querySelector('[name="card_expiry"]');
        number.addEventListener('input', function () {
            var digits = number.value.replace(/\D/g, '').slice(0, 19);
            number.value = digits.replace(/(.{4})/g, '$1 ').trim();
        });
        expiry.addEventListener('input', function () {
            var digits = expiry.value.replace(/\D/g, '').slice(0, 4);
            expiry.value = digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
        });
        form.addEventListener('submit', function () {
            var submit = form.querySelector('button[type="submit"]');
            if (submit) {
                submit.disabled = true;
                submit.textContent = 'Đang xử lý...';
            }
        });
    }

    if (status === 'processing') startPolling();
    tick();
    setInterval(tick, 1000);
})();
