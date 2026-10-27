<div class="container py-4" style="max-width:760px">
    <h3 class="mb-3">Xác nhận đơn đặt vé</h3>
    <div class="panel">
        <div class="d-flex gap-3 mb-3">
            <div style="width:80px;aspect-ratio:2/3;border-radius:6px;background:linear-gradient(160deg,#3a2a6a,#14142b) center/cover;<?= $showtime['poster'] ? "background-image:url('/uploads/" . e($showtime['poster']) . "')" : '' ?>"></div>
            <div>
                <h5 class="mb-1"><?= e($showtime['movie_title']) ?></h5>
                <div class="text-muted-2"><?= e($showtime['cinema_name']) ?> – <?= e($showtime['room_name']) ?></div>
                <div class="text-muted-2"><?= e(formatDateTime($showtime['start_time'], 'd/m/Y H:i')) ?></div>
            </div>
        </div>
        <table class="table table-dark align-middle mb-3">
            <thead><tr><th>Ghế</th><th>Loại</th><th class="text-end">Giá</th></tr></thead>
            <?php foreach ($seats as $s): ?>
                <tr><td><?= e($s['seat_row'] . $s['seat_number']) ?></td><td><?= $s['seat_type'] === 'vip' ? 'VIP' : 'Thường' ?></td><td class="text-end"><?= e(formatVnd((int)$s['price'])) ?></td></tr>
            <?php endforeach; ?>
            <tfoot><tr><th colspan="2">Tổng cộng</th><th class="text-end fs-5"><?= e(formatVnd((int)$total)) ?></th></tr></tfoot>
        </table>
        <p class="small text-muted-2">Ghế được giữ <?= (int)$holdMinutes ?> phút sau khi xác nhận. Hết thời gian mà chưa thanh toán, đơn sẽ tự hủy.</p>
        <form method="post" action="/checkout" class="d-flex gap-2">
            <?= csrfField() ?>
            <input type="hidden" name="showtime" value="<?= (int)$showtime['id'] ?>">
            <?php foreach ($seats as $s): ?><input type="hidden" name="seats[]" value="<?= (int)$s['id'] ?>"><?php endforeach; ?>
            <a class="btn btn-outline-light" href="/select-seats?showtime=<?= (int)$showtime['id'] ?>">Chọn lại ghế</a>
            <button class="btn btn-buy flex-grow-1">Xác nhận và thanh toán</button>
        </form>
    </div>
</div>
