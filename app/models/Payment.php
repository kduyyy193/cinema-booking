<?php
declare(strict_types=1);

class Payment
{
    public static function generateTransactionCode(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 12; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        return 'TXN' . $code;
    }

    public static function getOrCreatePending(int $bookingId): array
    {
        return withTransaction(function () use ($bookingId): array {
            $pdo = getPDO();
            $st = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? AND status IN ('pending','processing') ORDER BY id DESC LIMIT 1");
            $st->execute([$bookingId]);
            $existing = $st->fetch();
            if ($existing) return $existing;

            $b = $pdo->prepare('SELECT total_amount FROM bookings WHERE id = ?');
            $b->execute([$bookingId]);
            $amount = $b->fetchColumn();
            if ($amount === false) throw new InvalidArgumentException('Không tìm thấy đơn.');

            $insert = $pdo->prepare("INSERT INTO payments (booking_id, transaction_code, amount, method, status, created_at) VALUES (?, ?, ?, 'qr_demo', 'pending', ?)");
            $insert->execute([$bookingId, self::generateTransactionCode(), (int)$amount, nowString()]);
            return self::find((int)$pdo->lastInsertId());
        });
    }

    public static function find(int $id): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM payments WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findByTransactionCode(string $code): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM payments WHERE transaction_code = ?');
        $st->execute([$code]);
        return $st->fetch() ?: null;
    }

    public static function markProcessing(int $id): bool
    {
        $st = getPDO()->prepare("UPDATE payments SET status = 'processing' WHERE id = ? AND status = 'pending'");
        $st->execute([$id]);
        return $st->rowCount() === 1;
    }

    public static function verifyPaymentAmount(array $payment, array $booking): bool
    {
        return (int)$payment['amount'] === (int)$booking['total_amount'];
    }

    public static function applyWebhook(array $event): string
    {
        $pdo = getPDO();
        $pdo->exec('BEGIN IMMEDIATE');
        try {
            $insert = $pdo->prepare('INSERT INTO payment_webhook_events (event_id, transaction_code, event_type, payload, received_at) VALUES (?, ?, ?, ?, ?)');
            try {
                $insert->execute([$event['event_id'], $event['transaction_code'], $event['type'], $event['payload'], nowString()]);
            } catch (PDOException $ex) {
                if (str_contains($ex->getMessage(), 'UNIQUE constraint failed')) {
                    $pdo->exec('ROLLBACK');
                    return 'duplicate';
                }
                throw $ex;
            }
            $eventRowId = (int)$pdo->lastInsertId();

            $payment = self::findByTransactionCode($event['transaction_code']);
            if (!$payment) {
                $pdo->exec('ROLLBACK');
                logApplicationError('Webhook: không tìm thấy giao dịch ' . $event['transaction_code']);
                return 'not_found';
            }
            if ((int)$payment['amount'] !== (int)$event['amount']) {
                $pdo->exec('ROLLBACK');
                logApplicationError('Webhook: số tiền lệch cho giao dịch ' . $event['transaction_code']);
                return 'amount_mismatch';
            }

            self::transition($payment, $event['type']);

            $pdo->prepare('UPDATE payment_webhook_events SET payment_id = ?, processed_at = ? WHERE id = ?')
                ->execute([$payment['id'], nowString(), $eventRowId]);
            $pdo->exec('COMMIT');
            return 'processed';
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->exec('ROLLBACK');
            throw $ex;
        }
    }

    private static function transition(array $payment, string $type): void
    {
        $pdo = getPDO();
        if (in_array($payment['status'], ['success', 'refunded'], true)) return;

        if ($type === 'payment.succeeded') {
            $b = $pdo->prepare('SELECT status FROM bookings WHERE id = ?');
            $b->execute([$payment['booking_id']]);
            if ($b->fetchColumn() === 'pending') {
                $pdo->prepare("UPDATE payments SET status = 'success', paid_at = ? WHERE id = ?")->execute([nowString(), $payment['id']]);
                $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?")->execute([$payment['booking_id']]);
            } else {
                $pdo->prepare("UPDATE payments SET status = 'failed' WHERE id = ?")->execute([$payment['id']]);
            }
            return;
        }
        if (in_array($payment['status'], ['pending', 'processing'], true)) {
            $pdo->prepare("UPDATE payments SET status = 'failed' WHERE id = ?")->execute([$payment['id']]);
        }
    }

    public static function status(int $id): string
    {
        $st = getPDO()->prepare('SELECT status FROM payments WHERE id = ?');
        $st->execute([$id]);
        return (string)$st->fetchColumn();
    }

    public static function forBooking(int $bookingId): ?array
    {
        $st = getPDO()->prepare('SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$bookingId]);
        return $st->fetch() ?: null;
    }

    public static function allForBooking(int $bookingId): array
    {
        $st = getPDO()->prepare('SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC');
        $st->execute([$bookingId]);
        return $st->fetchAll();
    }
}
