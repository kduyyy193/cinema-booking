(function () {
    if (!window.Chart) return;
    var tick = '#a3a3bd';
    var grid = 'rgba(255,255,255,.08)';
    var palette = ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
    var vnd = function (v) { return Number(v).toLocaleString('vi-VN') + ' đ'; };
    var data = function (id) {
        var el = document.getElementById(id);
        return el ? { el: el, rows: JSON.parse(el.dataset.chart || '[]') } : null;
    };
    var axes = function (horizontal) {
        var money = { ticks: { color: tick, callback: function (v) { return Number(v).toLocaleString('vi-VN'); } }, grid: { color: grid }, beginAtZero: true };
        var label = { ticks: { color: tick }, grid: { display: false } };
        return horizontal ? { x: money, y: label } : { x: label, y: money };
    };
    var tooltip = { callbacks: { label: function (c) { return ' ' + vnd(c.parsed.y !== undefined && !c.chart.options.indexAxis ? c.parsed.y : c.parsed.x); } } };

    var rev = data('revenue-chart');
    if (rev) {
        // Dài hơn 31 ngày thì vẽ đường cho gọn, ngắn hơn thì vẽ cột.
        var line = rev.rows.length > 31;
        new Chart(rev.el, {
            type: line ? 'line' : 'bar',
            data: {
                labels: rev.rows.map(function (r) { return r.day.slice(8, 10) + '/' + r.day.slice(5, 7); }),
                datasets: [{
                    label: 'Doanh thu', data: rev.rows.map(function (r) { return r.revenue; }),
                    backgroundColor: line ? 'rgba(59,130,246,.2)' : '#3b82f6', borderColor: '#3b82f6',
                    borderRadius: 4, fill: line, tension: .3, pointRadius: line ? 0 : 3
                }]
            },
            options: { plugins: { legend: { display: false }, tooltip: tooltip }, scales: axes(false) }
        });
    }

    var movie = data('movie-chart');
    if (movie && movie.rows.length) {
        new Chart(movie.el, {
            type: 'bar',
            data: {
                labels: movie.rows.map(function (r) { return r.name; }),
                datasets: [{ data: movie.rows.map(function (r) { return r.revenue; }), backgroundColor: palette, borderRadius: 4 }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false }, tooltip: tooltip }, scales: axes(true) }
        });
    }

    var cinema = data('cinema-chart');
    if (cinema && cinema.rows.length) {
        new Chart(cinema.el, {
            type: 'doughnut',
            data: {
                labels: cinema.rows.map(function (r) { return r.name; }),
                datasets: [{ data: cinema.rows.map(function (r) { return r.revenue; }), backgroundColor: palette, borderColor: '#1c1c2e' }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { color: tick } },
                    tooltip: { callbacks: { label: function (c) { return ' ' + c.label + ': ' + vnd(c.parsed); } } }
                }
            }
        });
    }
})();
