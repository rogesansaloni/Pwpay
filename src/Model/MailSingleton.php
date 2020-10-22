<?php

namespace  pwpay\group19\Model;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

final class MailSingleton{
    private static ?MailSingleton $instance = null;

    private PHPMailer $mail;

    private function __construct(string $smtpHost, string $smtpUser, string $smtpPassword, int $port)
    {
        $this->mail = new PHPMailer(true);


        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->isSMTP();
        $this->mail->Host = $smtpHost;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $smtpUser;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Password = $smtpPassword;
        $this->mail->Port = $port;
        try {
            $this->mail->setFrom("noreplay@pwpay.com", 'pwpay');
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public static function getInstance(string $smtpHost, string $smtpUser, string $smtpPassword, int $port):self{
        if(self::$instance===null){
            self::$instance = new self($smtpHost,$smtpUser,$smtpPassword,$port);
        }
        return self::$instance;
    }

    public function mailAuthToUser(string $id,string $targetEmail):bool{

        try {
            $body = 'Buenos dias, para activar su cuenta presione el siguiente link: <a href="http://localhost:8030/activate?token='.$id.'" target="_blank">Activar Cuenta</a>';
            $this->mail->addAddress($targetEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Activate your account at pwpay';
            $this->mail->Body = $body;
            $this->mail->AltBody = "No tienes html en tu cliente, para activar tu cuenta accede a http://localhost:8030/activate?token=".$id;
            $this->mail->send();

            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            return true;
        } catch (Exception $e) {
            return false;
        }

    }
}
