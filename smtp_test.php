<?php
require 'vendor/autoload.php';
require 'bootstrap/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = App\Config\Config::get('mail');

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 3;
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG[$level]: $str";
    };
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['port'];
    $mail->setFrom($config['from_address'], $config['from_name']);
    $mail->addAddress($config['from_address']);
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = '<p>SMTP test</p>';
    $mail->AltBody = 'SMTP test';
    $mail->send();
    echo "sent\n";
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
