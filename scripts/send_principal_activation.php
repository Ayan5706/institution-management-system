<?php

declare(strict_types=1);

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

use App\Models\UserModel;
use App\Models\ActivationTokenModel;
use App\Services\MailService;

$enabledRaw = (string) env('PRINCIPAL_ACTIVATION_ENABLED', '1');
$enabled = in_array(strtolower(trim($enabledRaw)), ['1', 'true', 'yes', 'on'], true);

if (!$enabled) {
    echo "Principal activation sender is disabled.\n";
    exit(0);
}

$users = new UserModel();
$tokens = new ActivationTokenModel();
$mailer = new MailService();

$principals = $users->where('role', 'PRINCIPAL');
if ($principals === []) {
    echo "No principal accounts found.\n";
    exit(0);
}

$nowUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$sent = 0;
$skipped = 0;

foreach ($principals as $principal) {
    $userId = (int) ($principal['id'] ?? 0);
    $email = (string) ($principal['email'] ?? '');
    $fullName = (string) ($principal['full_name'] ?? '');

    if ($userId <= 0 || $email === '') {
        $skipped++;
        continue;
    }

    $existingTokens = $tokens->where('user_id', $userId);
    $hasValidToken = false;

    foreach ($existingTokens as $record) {
        $usedAt = (string) ($record['used_at'] ?? '');
        $expiresAt = (string) ($record['expires_at'] ?? '');

        if ($usedAt !== '') {
            continue;
        }

        if ($expiresAt !== '') {
            $expires = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $expiresAt, new DateTimeZone('UTC'));
            if ($expires instanceof DateTimeImmutable && $expires > $nowUtc) {
                $hasValidToken = true;
                break;
            }
        }
    }

    if ($hasValidToken) {
        $skipped++;
        continue;
    }

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = $nowUtc->modify('+24 hours')->format('Y-m-d H:i:s');

    $tokens->create([
        'user_id' => $userId,
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
        'created_by' => null,
        'created_at' => $nowUtc->format('Y-m-d H:i:s'),
    ]);

    $activationLink = url('activate/' . $token);
    $subject = 'Activate your IMS account';

    $safeName = $fullName !== '' ? $fullName : 'Administrator';
    $htmlBody = sprintf(
        '<p>Hello %s,</p><p>Your IMS account has been created. Please activate your account using the link below (valid for 24 hours):</p><p><a href="%s">Activate Account</a></p>',
        e($safeName),
        e($activationLink)
    );
    $textBody = "Hello {$safeName},\n\nYour IMS account has been created. Activate your account using the link below (valid for 24 hours):\n{$activationLink}\n";

    $mailer->sendMail($email, $subject, $htmlBody, $textBody);
    $sent++;
}

echo "Activation emails sent: {$sent}. Skipped: {$skipped}.\n";
