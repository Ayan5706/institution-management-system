<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use RuntimeException;

final class MailService
{
    public function sendMail(string $to, string $subject, string $htmlBody, string $textBody = ''): void
    {
        $config = (array) Config::get('mail', []);

        $host = (string) ($config['host'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $port = (int) ($config['port'] ?? 587);
        $encryption = (string) ($config['encryption'] ?? 'tls');
        $fromAddress = trim((string) ($config['from_address'] ?? ''));
        $fromName = trim((string) ($config['from_name'] ?? ''));

        if ($fromAddress === '' && filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $fromAddress = $username;
        }

        $missingKeys = [];
        if ($host === '') {
            $missingKeys[] = 'MAIL_HOST';
        }
        if ($username === '') {
            $missingKeys[] = 'MAIL_USERNAME';
        }
        if ($password === '') {
            $missingKeys[] = 'MAIL_PASSWORD';
        }
        if ($fromAddress === '') {
            $missingKeys[] = 'MAIL_FROM_ADDRESS';
        }

        if ($missingKeys !== []) {
            $message = 'Mail configuration is incomplete. Missing: ' . implode(', ', $missingKeys) . '.';
            \App\Helpers\logger_helper('mail_error', $message);
            throw new RuntimeException($message);
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->SMTPAuth = true;
            $mailer->Username = $username;
            $mailer->Password = $password;
            $mailer->Port = $port;
            $mailer->SMTPSecure = $encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($to);

            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

            $mailer->send();
        } catch (MailException $e) {
            \App\Helpers\logger_helper('mail_error', 'Failed to send email: ' . $e->getMessage());
            throw new RuntimeException('Failed to send email: ' . $e->getMessage());
        } catch (\Throwable $e) {
            \App\Helpers\logger_helper('mail_error', 'Unexpected mail error: ' . $e->getMessage());
            throw new RuntimeException('Failed to send email: ' . $e->getMessage());
        }
    }
}
