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
    var source = null;

    if (qrBox && window.QRCode) {
        new QRCode(qrBox, { text: qrBox.dataset.qr, width: 180, height: 180 });
    }

    function pad(n) { return n < 10 ? '0' + n : String(n); }

    function tick() {
        if (!countdown) return;
        var left = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
        countdown.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        if (left === 0) {
            stopWatching();
            window.location.reload();
        }
    }

    function stopWatching() {
        if (polling) { clearInterval(polling); polling = null; }
        if (source) { source.close(); source = null; }
    }

    function handle(data) {
        if (data.status === 'success' || data.booking_status === 'confirmed') {
            stopWatching();
            window.location.href = '/payment/success?code=' + encodeURIComponent(data.booking_code);
        } else if (data.status === 'failed' || data.status === 'expired') {
            stopWatching();
            window.location.reload();
        }
    }

    function poll() {
        fetch('/payment/status?transaction_code=' + encodeURIComponent(transaction), { credentials: 'same-origin' })
            .then(function (response) { return response.json(); })
            .then(handle)
            .catch(function () {});
    }

    function startPolling() {
        if (!polling) polling = setInterval(poll, 1000);
    }

    function watchWithSse() {
        source = new EventSource('/payment/stream?transaction_code=' + encodeURIComponent(transaction));
        source.addEventListener('status', function (event) { handle(JSON.parse(event.data)); });
        source.addEventListener('done', function () { if (source) { source.close(); source = null; } });
        source.onerror = function () {
            if (source && source.readyState === EventSource.CLOSED) { source = null; startPolling(); }
        };
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

    if (status === 'processing') {
        if (root.dataset.mode === 'sse' && window.EventSource) watchWithSse();
        else startPolling();
    }
    tick();
    setInterval(tick, 1000);
})();
