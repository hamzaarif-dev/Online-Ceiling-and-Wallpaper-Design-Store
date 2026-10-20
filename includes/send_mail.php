<?php
// Function to send email using PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . "/PHPMailer/src/Exception.php";
require_once dirname(__DIR__) . "/PHPMailer/src/PHPMailer.php";
require_once dirname(__DIR__) . "/PHPMailer/src/SMTP.php";

function sendEmail($to, $subject, $body)
{
    if (!file_exists(dirname(__DIR__) . "/PHPMailer/src/PHPMailer.php")) {
        // Fallback to error log if PHPMailer isn't installed
        error_log(
            "PHPMailer is not installed. Wanted to send to $to: $subject",
        );
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com"; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = "bc220408895har@vu.edu.pk"; // SMTP username
        $mail->Password = "mwub hqie espx zwja"; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Anti-Spam Tweaks for Localhost Development
        $mail->XMailer = " "; // Hide PHPMailer signature
        $mail->MessageID = "<" . md5(uniqid(time())) . "@gmail.com>"; // Realistic Message-ID

        // Recipients
        $mail->setFrom("mrhamza261@gmail.com", "DecorSlot");
        $mail->addReplyTo("mrhamza261@gmail.com", "DecorSlot");
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log(
            "Message could not be sent. Mailer Error: {$mail->ErrorInfo}",
        );
        return false;
    }
}
?>
