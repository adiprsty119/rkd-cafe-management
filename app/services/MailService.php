<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendEmailMessage($subject, $recipients, $htmlBody)
{
    if (!($_ENV['MAIL_ENABLED'] ?? false)) {
        error_log("Mail disabled");
        return false;
    }

    try {

        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 0;
        $mail->CharSet = "UTF-8";
        $mail->Timeout = 10;

        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->Port       = $_ENV['MAIL_PORT'];
        $mail->SMTPAuth   = true;

        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];

        if ($_ENV['MAIL_ENCRYPTION'] === "tls") {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        if ($_ENV['MAIL_ENCRYPTION'] === "ssl") {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'],
            $_ENV['MAIL_FROM_NAME']
        );

        if (is_array($recipients)) {
            foreach ($recipients as $email) {
                $mail->addAddress($email);
            }
        } else {
            $mail->addAddress($recipients);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();

        return true;
    } catch (Exception $e) {

        error_log("Email Error: " . $mail->ErrorInfo);

        return false;
    }
}
