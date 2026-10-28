<?php
declare(strict_types=1);

function signMockWebhook(string $rawBody, int $timestamp): string
{
    return hash_hmac('sha256', $timestamp . '.' . $rawBody, (string)config('webhook_secret'));
}

function verifyMockWebhookSignature(string $rawBody, string $signature, int $timestamp, int $tolerance = 300): bool
{
    if (abs(time() - $timestamp) > $tolerance) return false;
    return hash_equals(signMockWebhook($rawBody, $timestamp), $signature);
}

function generatePaymentQr(string $transactionCode): string
{
    $payment = \App\Models\Payment::findByTransactionCode($transactionCode);
    $amount = $payment ? (int)$payment['amount'] : 0;
    return 'CINEBOOKING|' . $transactionCode . '|' . $amount;
}

function validateDemoCard(array $data): array
{
    $errors = [];
    $name = trim((string)($data['card_name'] ?? ''));
    $number = preg_replace('/\s+/', '', (string)($data['card_number'] ?? ''));
    $expiry = trim((string)($data['card_expiry'] ?? ''));
    $cvv = trim((string)($data['card_cvv'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) $errors['card_name'] = 'Tên chủ thẻ bắt buộc, tối đa 100 ký tự.';
    if (!preg_match('/^\d{13,19}$/', $number)) $errors['card_number'] = 'Số thẻ gồm 13–19 chữ số.';
    if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $m)) {
        $errors['card_expiry'] = 'Hạn thẻ theo định dạng MM/YY.';
    } else {
        $endOfMonth = mktime(23, 59, 59, (int)$m[1] + 1, 0, 2000 + (int)$m[2]);
        if ($endOfMonth < time()) $errors['card_expiry'] = 'Thẻ đã hết hạn.';
    }
    if (!preg_match('/^\d{3}$/', $cvv)) $errors['card_cvv'] = 'CVV gồm 3 chữ số.';
    return $errors;
}

function simulateGatewayResult(string $cardNumber): string
{
    return preg_replace('/\s+/', '', $cardNumber) === '4242424242424242' ? 'success' : 'failed';
}

function webhookBaseUrl(): string
{
    $configured = config('webhook_base_url');
    if (is_string($configured) && $configured !== '') return rtrim($configured, '/');
    $port = (int)($_SERVER['SERVER_PORT'] ?? 8000);
    return 'http://127.0.0.1:' . $port;
}

function webhookLoopbackAvailable(): bool
{
    if (PHP_SAPI !== 'cli-server') return true;
    return PHP_OS_FAMILY !== 'Windows' && (int)getenv('PHP_CLI_SERVER_WORKERS') > 1;
}

function paymentWatchMode(): string
{
    $mode = (string)config('payment_watch_mode');
    if ($mode === 'sse' || $mode === 'poll') return $mode;
    return webhookLoopbackAvailable() ? 'sse' : 'poll';
}

function sendMockWebhook(string $type, array $payment): bool
{
    $raw = json_encode([
        'event_id' => 'evt_' . bin2hex(random_bytes(8)),
        'type' => $type,
        'transaction_code' => $payment['transaction_code'],
        'amount' => (int)$payment['amount'],
        'created_at' => nowString(),
    ], JSON_UNESCAPED_UNICODE);
    if (!webhookLoopbackAvailable()) {
        [$code] = \App\Controllers\WebhookController::processRaw($raw);
        return $code === 200;
    }
    $timestamp = time();
    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nX-Mock-Signature: " . signMockWebhook($raw, $timestamp) . "\r\nX-Mock-Timestamp: $timestamp\r\n",
        'content' => $raw,
        'timeout' => 3,
        'ignore_errors' => true,
    ]]);
    $response = @file_get_contents(webhookBaseUrl() . '/webhooks/mock-payment', false, $context);
    if ($response !== false) return true;
    logApplicationError('Webhook HTTP thất bại, xử lý trực tiếp trong tiến trình.');
    [$code] = \App\Controllers\WebhookController::processRaw($raw);
    return $code === 200;
}
