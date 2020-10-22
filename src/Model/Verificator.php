<?php


declare(strict_types=1);

namespace  pwpay\group19\Model;

use DateTime;

class Verificator{

    public function verifyCellphone(string $cell, array & $errors){
        if(!empty($cell)){
            if($this->beginsWith($cell,'+')){
                if($this->beginsWith($cell,'+34')){
                    if(!$this->verifyPhoneNumber(substr($cell,3))){
                        $errors['cellphone'] = 'Error, the phone number is invalid';
                    }
                }else{
                    $errors['cellphone'] = 'Error, you have a bad country code';
                }
            }else{
                if(!$this->verifyPhoneNumber($cell)){
                    $errors['cellphone'] = 'Error, the phone number is invalid';
                }
            }
        }
    }

    private function endsWith($haystack, $needle): bool
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    private function beginsWith($haystack,$needle): bool{
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    private function verifyPhoneNumber($phone): bool{
        if(strlen($phone)!=9){
            return false;
        }
        $beggining = (int)(substr($phone,0,3));

        if($beggining>=600&&$beggining<=699)return true;
        if($beggining>=710&&$beggining<=799) return true;
        if($beggining===822) return true;
        if($beggining===824) return true;
        if($beggining===828) return true;
        if($beggining===843) return true;
        if($beggining===848) return true;
        if($beggining===850) return true;
        if($beggining===856) return true;
        if($beggining===858) return true;
        if($beggining===868) return true;
        if($beggining>=871 && $beggining<=873) return true;
        if($beggining>=876 && $beggining<=877) return true;
        if($beggining===881) return true;
        if($beggining===886) return true;
        if($beggining>=911 && $beggining<=918) return true;
        if($beggining>=920 && $beggining<=928) return true;
        if($beggining>=931 && $beggining<=938) return true;
        if($beggining>=941 && $beggining<=969) return true;
        if($beggining>=971 && $beggining<=988) return true;

        return false;
    }

    public function verifyPhoto( $photo, array & $errors){
        if ($photo-> getSize() != 0) {
            if ($photo-> getSize() > 1024*1024) {
                $errors['photoToUpload'] = 'Error, the image is too big';
            }

            $info = getimagesize($photo->getStream()->getMetadata()['uri']);
            $width = $info[0];
            $height = $info[1];
            if ($width < 400 || $height < 400) {
                $errors['photoToUpload'] = 'Error, the dimensions must be greater than 400x400';

            }
            if ($photo->getClientMediaType( )  != 'image/png') {
                $errors['photoToUpload'] = 'Error, the image must be png';
            }

        }
    }

    public function verifyEmail(string $email, array & $errors){
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Error, malformed email';
        }
        else if(!$this->endsWith($email,'.salle.url.edu')&&!$this->endsWith($email,"@salle.url.edu")){
            $errors['email'] = 'Error, domain of email is invalid';
        }
    }

    public function verifyPassword(string $password, array & $errors){
        if(strlen($password)<5){
            $errors['password'] = 'Error, length of password must be at least 5 characters long';
        }else{
            if(!preg_match('/[A-Z]/', $password)){
                $errors['password'] = 'Error, password must contain at least one uppercase letter';
            }else{
                if(!preg_match('/[a-z]/',$password)){
                    $errors['password'] = 'Error, password must contain at least one lowercase letter';
                }else{
                    if(!preg_match('/[0-9]/',$password)){
                        $errors['password'] = 'Error, password must contain at least one number';
                    }
                }
            }
        }
    }

    public function verifyBirthday(string $birthday, array & $errors){
        if(!$this->validateDate($birthday)){
            $errors['birthday'] = 'Error, invalid date format, it should be yyyy-mm-dd';
        }else {
            if(!$this->isLegalAge($birthday)){
                $errors['birthday'] = 'Error, you must be an adult in order to register';
            }
        }
    }

    private function isLegalAge(string $date,int $age = 18):bool{
        $dtDate = DateTime::createFromFormat('Y-m-d',$date);
        $dateNow = new DateTime();

        $interval = date_diff($dtDate,$dateNow);

        if($interval->format('%y')<$age){
            return false;
        }
        return true;
    }

    private function validateDate($date, $format = 'Y-m-d'):bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function verifyMoney(string $number, array & $errors){
        if(!is_numeric($number)){
            $errors['amount_error'] = 'Error, amount is not a number';
        }else if(!filter_var($number, FILTER_VALIDATE_INT)){
            $errors['amount_error'] = 'Error, amount must be a decimal number';
        }else if(((int)$number)<=0){
            $errors['amount_error'] = 'Error, amount must be a positive number';
        }
    }

}