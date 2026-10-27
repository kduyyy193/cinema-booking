<?php
declare(strict_types=1);

class WebhookController
{
    public static function mockPayment(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            jsonResponse(['error' => 'method_not_allowed'], 405);
        }
        $raw = (string)file_get_contents('php://input');
        $signature = (string)($_SERVER['HTTP_X_MOCK_SIGNATURE'] ?? '');
        $timestamp = (string)($_SERVER['HTTP_X_MOCK_TIMESTAMP'] ?? '');
        if ($signature === '' || !ctype_digit($timestamp) || !verifyMockWebhookSignature($raw, $signature, (int)$timestamp)) {
            jsonResponse(['error' => 'invalid_signature'], 401);
        }
        [$code, $body] = self::processRaw($raw);
        jsonResponse($body, $code);
    }

    public static function processRaw(string $raw): array
    {
        $data = json_decode($raw, true);
        if (!is_array($data)) return [400, ['error' => 'invalid_json']];

        $eventId = $data['event_id'] ?? null;
        $type = $data['type'] ?? null;
        $code = $data['transaction_code'] ?? null;
        $amount = $data['amount'] ?? null;
        if (!is_string($eventId) || $eventId === '' || !is_string($code) || $code === '' || !is_int($amount)
            || !in_array($type, ['payment.succeeded', 'payment.failed'], true)) {
            return [400, ['error' => 'invalid_payload']];
        }

        $result = Payment::applyWebhook([
            'event_id' => $eventId, 'type' => $type, 'transaction_code' => $code, 'amount' => $amount, 'payload' => $raw,
        ]);
        return match ($result) {
            'processed' => [200, ['status' => 'processed']],
            'duplicate' => [200, ['status' => 'duplicate']],
            'not_found' => [404, ['error' => 'transaction_not_found']],
            default     => [400, ['error' => 'amount_mismatch']],
        };
    }
}
