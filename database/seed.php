<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/config/database.php';
date_default_timezone_set(config('timezone'));

$dbFile = config('db_path');
foreach ([$dbFile, "$dbFile-wal", "$dbFile-shm"] as $f) { if (is_file($f)) unlink($f); }

$pdo = getPDO();
$pdo->exec(file_get_contents(config('schema_path')));
$pdo->beginTransaction();

$u = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
$u->execute(['Quản trị viên', 'admin@cinema.test', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
$u->execute(['Khách demo', 'customer@cinema.test', password_hash('customer123', PASSWORD_DEFAULT), 'customer']);
$u->execute(['Khách thứ hai', 'customer2@cinema.test', password_hash('customer123', PASSWORD_DEFAULT), 'customer']);

$genres = ['Hành động', 'Hài', 'Kinh dị', 'Tình cảm', 'Hoạt hình', 'Khoa học viễn tưởng'];
$g = $pdo->prepare('INSERT INTO genres (name) VALUES (?)');
foreach ($genres as $name) $g->execute([$name]);

$movies = [
    ['Biệt đội siêu anh hùng', 125, 'now_showing', [1, 6], 'T13'],
    ['Đêm kinh hoàng', 98,  'now_showing', [3], 'T16'],
    ['Chuyến phiêu lưu của Luca', 101, 'now_showing', [5, 2], 'P'],
    ['Mùa yêu', 110, 'now_showing', [4], 'T13'],
    ['Cuộc đua tử thần', 132, 'now_showing', [1], 'T16'],
    ['Hành tinh bí ẩn', 140, 'upcoming', [6], 'T13'],
    ['Nhà có ma', 95, 'upcoming', [3, 2], 'T16'],
    ['Vương quốc kẹo ngọt', 88, 'upcoming', [5], 'P'],
];
$m  = $pdo->prepare('INSERT INTO movies (title,description,director,cast_list,age_rating,duration,release_date,status) VALUES (?,?,?,?,?,?,?,?)');
$mg = $pdo->prepare('INSERT INTO movie_genres (movie_id,genre_id) VALUES (?,?)');
foreach ($movies as $i => [$title, $dur, $status, $gids, $age]) {
    $rel = date('Y-m-d', strtotime($status === 'upcoming' ? '+' . (10 + $i) . ' day' : '-' . (3 + $i) . ' day'));
    $m->execute([$title, "Nội dung giới thiệu phim \"$title\" (dữ liệu mẫu).", 'Đạo diễn mẫu', 'Diễn viên A, Diễn viên B', $age, $dur, $rel, $status]);
    $mid = (int)$pdo->lastInsertId();
    foreach ($gids as $gid) $mg->execute([$mid, $gid]);
}

$pdo->exec("INSERT INTO cinemas (name,address,phone) VALUES ('CineBooking Ninh Kiều','1 Đường 3/2, Cần Thơ','0292 000 111'),('CineBooking Cái Răng','12 Võ Nguyên Giáp, Cần Thơ','0292 000 222')");
$room = $pdo->prepare('INSERT INTO rooms (cinema_id,name) VALUES (?,?)');
$seat = $pdo->prepare('INSERT INTO seats (room_id,seat_row,seat_number,seat_type) VALUES (?,?,?,?)');
$roomIds = [];
foreach ([[1, 'Phòng 1'], [1, 'Phòng 2'], [2, 'Phòng 1']] as [$cid, $rname]) {
    $room->execute([$cid, $rname]);
    $rid = (int)$pdo->lastInsertId();
    $roomIds[] = $rid;
    foreach (range('A', 'G') as $ri => $row) {
        for ($n = 1; $n <= 12; $n++) {
            $seat->execute([$rid, $row, $n, $ri >= 5 ? 'vip' : 'standard']);
        }
    }
}

$buffer = 15;
$st = $pdo->prepare('INSERT INTO showtimes (movie_id,room_id,start_time,end_time,base_price,vip_surcharge) VALUES (?,?,?,?,?,?)');
$slots = ['10:00', '14:00', '18:00', '21:00'];
for ($d = 0; $d < 7; $d++) {
    foreach ([1, 2, 3, 4, 5] as $k => $movieId) {
        $roomId = $roomIds[$k % count($roomIds)];
        $dur = $movies[$movieId - 1][1];
        foreach ([$slots[$k % 4], $slots[($k + 2) % 4]] as $slot) {
            $start = strtotime(date('Y-m-d', strtotime("+$d day")) . " $slot");
            $end = $start + ($dur + $buffer) * 60;
            $c = $pdo->prepare('SELECT COUNT(*) FROM showtimes WHERE room_id=? AND start_time < ? AND end_time > ?');
            $c->execute([$roomId, date('Y-m-d H:i:s', $end), date('Y-m-d H:i:s', $start)]);
            if ((int)$c->fetchColumn() === 0) {
                $st->execute([$movieId, $roomId, date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end), 90000, 20000]);
            }
        }
    }
}

$s = $pdo->prepare('INSERT INTO settings (key,value) VALUES (?,?)');
foreach ([['site_name', 'CineBooking'], ['cancel_before_minutes', '120'], ['showtime_buffer_minutes', (string)$buffer], ['hold_minutes', '10']] as $row) $s->execute($row);

$uploadDir = dirname(__DIR__) . '/public/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
foreach (glob($uploadDir . '/seed-poster-*.jpg') ?: [] as $old) unlink($old);
$setPoster = $pdo->prepare('UPDATE movies SET poster = ? WHERE id = ?');
foreach (range(1, count($movies)) as $movieId) {
    $source = __DIR__ . "/sample-posters/poster$movieId.jpg";
    if (!is_file($source)) continue;
    $name = "seed-poster-$movieId.jpg";
    copy($source, "$uploadDir/$name");
    $setPoster->execute([$name, $movieId]);
}

$pastShowtimeIds = [];
foreach ([-13, -10, -7, -5, -3, -1] as $i => $offset) {
    $movieId = ($i % 5) + 1;
    $roomId = $roomIds[$i % count($roomIds)];
    $start = strtotime(date('Y-m-d', strtotime("$offset day")) . ' 19:00');
    $end = $start + ($movies[$movieId - 1][1] + $buffer) * 60;
    $st->execute([$movieId, $roomId, date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end), 90000, 20000]);
    $pastShowtimeIds[] = (int)$pdo->lastInsertId();
}

$tomorrow = $pdo->prepare('SELECT id FROM showtimes WHERE date(start_time) = ? ORDER BY id LIMIT 4');
$tomorrow->execute([date('Y-m-d', strtotime('+1 day'))]);
$futureIds = array_map('intval', $tomorrow->fetchAll(PDO::FETCH_COLUMN));

function seedRandomCode(string $prefix, int $length): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    return $prefix . $out;
}

function seedBooking(PDO $pdo, int $userId, int $showtimeId, array $labels, string $status, string $paymentStatus,
    string $createdAt, ?string $paidAt, string $expiresAt, int $active, ?string $cancelledAt = null): void
{
    $show = $pdo->prepare('SELECT room_id, base_price, vip_surcharge FROM showtimes WHERE id = ?');
    $show->execute([$showtimeId]);
    $s = $show->fetch();
    $seatQuery = $pdo->prepare('SELECT id, seat_type FROM seats WHERE room_id = ? AND seat_row = ? AND seat_number = ?');
    $lines = [];
    $total = 0;
    foreach ($labels as $label) {
        $seatQuery->execute([$s['room_id'], $label[0], (int)substr($label, 1)]);
        $seat = $seatQuery->fetch();
        $price = (int)$s['base_price'] + ($seat['seat_type'] === 'vip' ? (int)$s['vip_surcharge'] : 0);
        $lines[] = [(int)$seat['id'], $price];
        $total += $price;
    }
    $pdo->prepare('INSERT INTO bookings (user_id, showtime_id, booking_code, total_amount, status, expires_at, created_at, cancelled_at) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$userId, $showtimeId, seedRandomCode('CB' . date('ymd', strtotime($createdAt)), 6), $total, $status, $expiresAt, $createdAt, $cancelledAt]);
    $bookingId = (int)$pdo->lastInsertId();
    $line = $pdo->prepare('INSERT INTO booking_seats (booking_id, showtime_id, seat_id, price, active) VALUES (?,?,?,?,?)');
    foreach ($lines as [$seatId, $price]) $line->execute([$bookingId, $showtimeId, $seatId, $price, $active]);
    $pdo->prepare('INSERT INTO payments (booking_id, transaction_code, amount, method, status, created_at, paid_at) VALUES (?,?,?,?,?,?,?)')
        ->execute([$bookingId, seedRandomCode('TXN', 12), $total, 'qr_demo', $paymentStatus, $createdAt, $paidAt]);
}

$fmt = fn(int $ts): string => date('Y-m-d H:i:s', $ts);
$startOf = function (int $showtimeId) use ($pdo): int {
    $q = $pdo->prepare('SELECT start_time FROM showtimes WHERE id = ?');
    $q->execute([$showtimeId]);
    return strtotime((string)$q->fetchColumn());
};

$pastOrders = [
    [2, 0, ['D5', 'D6']], [3, 1, ['F3', 'F4']], [2, 2, ['C7']],
    [3, 3, ['G1', 'G2', 'G3']], [2, 4, ['B2', 'B3']], [3, 5, ['E8', 'E9', 'E10']],
];
foreach ($pastOrders as [$userId, $idx, $labels]) {
    $showtimeId = $pastShowtimeIds[$idx];
    $paid = $fmt($startOf($showtimeId) - 3 * 3600);
    seedBooking($pdo, $userId, $showtimeId, $labels, 'confirmed', 'success', $paid, $paid, $paid, 1);
}

if (count($futureIds) >= 4) {
    $now = time();
    $paidRecently = $fmt($now - 1800);
    seedBooking($pdo, 2, $futureIds[0], ['E5', 'E6'], 'confirmed', 'success', $paidRecently, $paidRecently, $paidRecently, 1);
    seedBooking($pdo, 2, $futureIds[1], ['B3', 'B4'], 'pending', 'pending', $fmt($now - 60), null, $fmt($now + 1800), 1);
    seedBooking($pdo, 3, $futureIds[2], ['B3'], 'expired', 'expired', $fmt($now - 7200), null, $fmt($now - 6000), 0);
    seedBooking($pdo, 3, $futureIds[3], ['A1', 'A2'], 'cancelled', 'refunded', $fmt($now - 86400), $fmt($now - 86000), $fmt($now - 85000), 0, $fmt($now - 43200));
}

$pdo->commit();
echo "OK. Database: $dbFile\nAdmin: admin@cinema.test / admin123\nCustomer: customer@cinema.test / customer123\n";
