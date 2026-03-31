<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

/* ========================================
   CORE MAIL BUILDER (NEW)
======================================== */
function buildMailer()
{
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = 0;
    $mail->CharSet   = "UTF-8";
    $mail->Timeout   = 10;

    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->Port       = $_ENV['MAIL_PORT'];
    $mail->SMTPAuth   = true;

    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];

    if ($_ENV['MAIL_ENCRYPTION'] === "tls") {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($_ENV['MAIL_ENCRYPTION'] === "ssl") {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }

    $mail->setFrom(
        $_ENV['MAIL_FROM_ADDRESS'],
        $_ENV['MAIL_FROM_NAME']
    );

    return $mail;
}

/* ========================================
   LEGACY FUNCTION (TETAP DIPERTAHANKAN)
======================================== */
function sendEmailMessage($subject, $recipients, $htmlBody)
{
    if (!($_ENV['MAIL_ENABLED'] ?? false)) {
        error_log("[MAIL] Disabled");
        return false;
    }

    try {

        $mail = buildMailer();

        /* RECIPIENT */
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

        error_log("[MAIL ERROR] " . $mail->ErrorInfo);

        return false;
    }
}

/* ========================================
   NEW ADVANCED FUNCTION (EXTENDED)
======================================== */
function sendMail($subject, $recipients, $htmlBody, $options = [])
{
    if (!($_ENV['MAIL_ENABLED'] ?? false)) {
        error_log("[MAIL] Disabled");
        return false;
    }

    try {

        $mail = buildMailer();

        /* TO */
        if (is_array($recipients)) {
            foreach ($recipients as $email) {
                $mail->addAddress($email);
            }
        } else {
            $mail->addAddress($recipients);
        }

        /* CC */
        if (!empty($options['cc'])) {
            foreach ($options['cc'] as $cc) {
                $mail->addCC($cc);
            }
        }

        /* BCC */
        if (!empty($options['bcc'])) {
            foreach ($options['bcc'] as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        /* ATTACHMENTS */
        if (!empty($options['attachments'])) {
            foreach ($options['attachments'] as $file) {
                $mail->addAttachment($file);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        if (!empty($options['altBody'])) {
            $mail->AltBody = $options['altBody'];
        }

        $mail->send();

        return true;
    } catch (Exception $e) {

        error_log("[MAIL ERROR] " . $mail->ErrorInfo);

        return false;
    }
}

/* ========================================
   TEMPLATE RENDERER (NEW)
======================================== */
function renderEmailTemplate($template, $data = [])
{
    $file = __DIR__ . "/../../views/emails/$template.php";

    if (!file_exists($file)) {
        return "Template tidak ditemukan";
    }

    extract($data);

    ob_start();
    include $file;
    return ob_get_clean();
}

/* ========================================
   PRESET: ADMIN NOTIFICATION
======================================== */
function sendAdminNotification($data)
{
    $body = renderEmailTemplate('admin_notification', $data);

    return sendMail(
        "Registrasi Baru - Approval Dibutuhkan",
        $_ENV['ADMIN_EMAIL'],
        $body
    );
}

/* ========================================
   PRESET: ACTIVATION EMAIL
======================================== */
function sendActivationEmail($email, $token)
{
    $link = $_ENV['APP_URL'] . "/activate?token=$token";

    $body = renderEmailTemplate('activation', [
        'link' => $link
    ]);

    return sendMail(
        "Aktivasi Akun",
        $email,
        $body
    );
}

/* ========================================
   PRESET: APPROVAL EMAIL (NEW)
======================================== */
function sendApprovalEmail($email, $name)
{
    $body = renderEmailTemplate('approval', [
        'name' => $name
    ]);

    return sendMail(
        "Akun Anda Telah Disetujui",
        $email,
        $body
    );
}
