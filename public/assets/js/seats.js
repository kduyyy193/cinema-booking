(function () {
    var app = document.getElementById('seat-app');
    if (!app) return;

    var maxSeats = parseInt(app.dataset.max, 10) || 8;
    var showtime = app.dataset.showtime;
    var list = document.getElementById('selected-list');
    var totalEl = document.getElementById('total');
    var message = document.getElementById('seat-msg');
    var next = document.getElementById('continue');
    var selected = new Map();

    function money(value) {
        return value.toLocaleString('vi-VN') + 'đ';
    }

    function render() {
        var total = 0;
        selected.forEach(function (seat) { total += seat.price; });
        totalEl.textContent = money(total);
        list.textContent = '';
        if (selected.size === 0) {
            list.textContent = 'Chưa chọn ghế';
        } else {
            selected.forEach(function (seat) {
                var chip = document.createElement('span');
                chip.className = 'badge bg-primary me-1 mb-1';
                chip.textContent = seat.label + ' · ' + money(seat.price);
                list.appendChild(chip);
            });
        }
        next.disabled = selected.size === 0;
    }

    app.addEventListener('click', function (event) {
        var button = event.target.closest('.seat[data-seat-id]');
        if (!button || button.classList.contains('taken') || button.classList.contains('locked')) return;

        var id = button.dataset.seatId;
        message.textContent = '';
        if (selected.has(id)) {
            selected.delete(id);
            button.classList.remove('selected');
        } else {
            if (selected.size >= maxSeats) {
                message.textContent = 'Mỗi đơn chỉ được chọn tối đa ' + maxSeats + ' ghế.';
                return;
            }
            selected.set(id, { label: button.dataset.label, price: parseInt(button.dataset.price, 10) || 0 });
            button.classList.add('selected');
        }
        render();
    });

    next.addEventListener('click', function () {
        if (selected.size === 0) return;
        var query = ['showtime=' + encodeURIComponent(showtime)];
        selected.forEach(function (_, id) { query.push('seats[]=' + encodeURIComponent(id)); });
        window.location.href = '/checkout?' + query.join('&');
    });

    render();
})();
