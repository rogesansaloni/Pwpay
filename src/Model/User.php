<?php

declare(strict_types=1);

namespace  pwpay\group19\Model;

use DateTime;

final class User{
    private int $id;
    private string $email;
    private string $password;
    private DateTime $birthdate;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private bool $activated;
    private string $IBAN;
    private string $cellphone;


    public function __construct(string $email,string $password, DateTime $birthdate, DateTime $createdAt, DateTime $updatedAt,bool $activated, string $cellphone,  bool $encrypt = true,string $IBAN="")
    {
        $this->email = $email;
        if($encrypt){
            $this->password = password_hash($password,PASSWORD_ARGON2ID);
        }else{
            $this->password = $password;
        }
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->activated = $activated;
        $this->birthdate = $birthdate;
        $this->cellphone = $cellphone;
        $this->IBAN = $IBAN;
    }


    public function id():int{
        return $this->id;
    }

    public function setId(int $id): self{
        $this->id = $id;
        return $this;
    }

    public function IBAN(): string{
        return $this->IBAN;
    }

    public function activate(){
        $this->activated = true;
    }

    public function activated(){
        return $this->activated;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self{
        $this->password = password_hash($password,PASSWORD_ARGON2ID);
        return $this;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function cellphone(): string{
        return $this->cellphone;
    }

    public function birthdate(): DateTime{
        return  $this->birthdate;
    }
}
