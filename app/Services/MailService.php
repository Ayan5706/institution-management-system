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
        $fromAddress = (string) ($config['from_address'] ?? '');
        $fromName = (string) ($config['from_name'] ?? '');

        if ($host === '' || $username === '' || $password === '' || $fromAddress === '') {
            throw new RuntimeException('Mail configuration is incomplete.');
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
            throw new RuntimeException('Failed to send email: ' . $e->getMessage());
        }
    }
}
