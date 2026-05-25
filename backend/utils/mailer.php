<?php
// backend/utils/mailer.php — Basic email sender (configure php.ini sendmail for XAMPP)
function sendEmail($to, $subject, $body, $from = 'noreply@eduplanning.local') {
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    // In production use PHPMailer + SMTP
    @mail($to, $subject, $body, $headers);
    // For dev: log to file instead of real send if mail() disabled
    // Use @ to prevent warnings from polluting JSON responses
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    @file_put_contents($logDir . '/email.log', date('c') . " TO:$to SUBJ:$subject\n$body\n\n", FILE_APPEND | LOCK_EX);
    return true; // assume sent
}
?>
