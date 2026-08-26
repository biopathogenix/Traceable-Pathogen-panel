<?php
/**
 * Handles the "Free Panel Assessment" form submission:
 * emails the request to the BioPathogenix team, and (optionally)
 * sends the submitter a confirmation.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/template.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function bpx_respond(bool $success, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bpx_respond(false, 'Invalid request method.', 405);
}

if (!is_file(__DIR__ . '/config.php')) {
    error_log('BPX mail: mail/config.php is missing — copy mail/config.sample.php and fill in credentials.');
    bpx_respond(false, 'The form is not fully configured yet. Please email Order@BioPathogenix.com directly.', 500);
}
$config = require __DIR__ . '/config.php';

// Honeypot: a hidden field real users never fill in. Bots usually do.
if (!empty($_POST['website'])) {
    bpx_respond(true, 'Thanks — we will be in touch shortly.');
}

$name     = trim((string)($_POST['name'] ?? ''));
$email    = trim((string)($_POST['email'] ?? ''));
$org      = trim((string)($_POST['org'] ?? ''));
$phone    = trim((string)($_POST['phone'] ?? ''));
$interest = trim((string)($_POST['interest'] ?? ''));
$message  = trim((string)($_POST['message'] ?? ''));

if ($name === '' || $email === '') {
    bpx_respond(false, 'Please fill in your name and work email.', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    bpx_respond(false, 'Please enter a valid email address.', 422);
}
if (mb_strlen($name) > 150 || mb_strlen($email) > 150 || mb_strlen($org) > 150 ||
    mb_strlen($phone) > 50 || mb_strlen($interest) > 150 || mb_strlen($message) > 5000) {
    bpx_respond(false, 'One of the fields is too long.', 422);
}

$fields = [
    'name'     => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    'email'    => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
    'org'      => htmlspecialchars($org, ENT_QUOTES, 'UTF-8'),
    'phone'    => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
    'interest' => htmlspecialchars($interest, ENT_QUOTES, 'UTF-8'),
    'message'  => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
];

function bpx_configure_smtp(PHPMailer $mail, array $config): void
{
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->Port       = $config['smtp_port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = $config['smtp_secure'] === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = 'UTF-8';
}

try {
    // 1. Notification to the BioPathogenix team.
    $mail = new PHPMailer(true);
    bpx_configure_smtp($mail, $config);
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    $mail->addReplyTo($email, $name);

    $notification = bpx_notification_email($fields);
    $mail->isHTML(true);
    $mail->Subject = $notification['subject'];
    $mail->Body    = $notification['html'];
    $mail->AltBody = $notification['text'];
    $mail->send();

    // 2. Confirmation to the submitter (best-effort — does not fail the request).
    if (!empty($config['send_confirmation'])) {
        try {
            $confirm = new PHPMailer(true);
            bpx_configure_smtp($confirm, $config);
            $confirm->setFrom($config['from_email'], $config['from_name']);
            $confirm->addAddress($email, $name);

            $confirmation = bpx_confirmation_email($name);
            $confirm->isHTML(true);
            $confirm->Subject = $confirmation['subject'];
            $confirm->Body    = $confirmation['html'];
            $confirm->AltBody = $confirmation['text'];
            $confirm->send();
        } catch (PHPMailerException $e) {
            error_log('BPX mail: confirmation email failed: ' . $e->getMessage());
        }
    }

    bpx_respond(true, 'Thanks — a BioPathogenix scientist will follow up shortly.');
} catch (PHPMailerException $e) {
    error_log('BPX mail: notification email failed: ' . $e->getMessage());
    bpx_respond(false, 'Something went wrong sending your request. Please email Order@BioPathogenix.com directly.', 500);
}
