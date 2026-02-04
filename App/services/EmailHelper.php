<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
require_once 'EmailConfig.php';

class EmailHelper {
    
    private static function createMailer() {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = EmailConfig::getHost();
            $mail->SMTPAuth = true;
            $mail->Username = EmailConfig::getUserName();
            $mail->Password = EmailConfig::getPassword();
            $mail->SMTPSecure = EmailConfig::getEncryption();
            $mail->Port = EmailConfig::getPort();
            $mail->CharSet = EmailConfig::CHARSET;
   
            $mail->setFrom(EmailConfig::getFromEmail(), EmailConfig::getFromName());
            
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