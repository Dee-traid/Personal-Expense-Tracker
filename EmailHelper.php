<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
require_once 'EmailConfig.php';

class EmailHelper {
    
    private static function createMailer() {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = EmailConfig::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::SMTP_USERNAME;
            $mail->Password = EmailConfig::SMTP_PASSWORD;
            $mail->SMTPSecure = EmailConfig::SMTP_ENCRYPTION;
            $mail->Port = EmailConfig::SMTP_PORT;
            $mail->CharSet = EmailConfig::CHARSET;
   
            $mail->setFrom(EmailConfig::FROM_EMAIL, EmailConfig::FROM_NAME);
            
            return $mail;
            
        } catch (Exception $e) {
            echo "Email setup failed: {$mail->ErrorInfo}\n";
            return null;
        }
    }
    
    public static function sendEmail(string $toEmail, string $toName, string $subject, string $body, bool $isHTML = true) {
        $mail = self::createMailer();
        
        if (!$mail) {
            return false;
        }
        
        try {
            $mail->addAddress($toEmail, $toName);
            
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHTML) {
                $mail->AltBody = strip_tags($body);
            }
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            echo "Email sending failed: {$mail->ErrorInfo}\n";
            return false;
        }
    }
}