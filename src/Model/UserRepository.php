<?php

declare(strict_types=1);
//LLAMAR AQUI FUNCIONES DE BBDD
namespace  pwpay\group19\Model;

interface UserRepository{
    public function register(User $user): string;
    public function activate(string $token): bool;
    public function login(string $email, string  $password): int;

    public function getEmail(string $u_id): string;

    public function getUser (int $id):User;
    public function updatePhone (string $phone, int $id):void;
    public function updatePassword (string $password, int $id):void;
    public function updateUpdated_at (int $id);

    public function getId(string $email):int;
}
