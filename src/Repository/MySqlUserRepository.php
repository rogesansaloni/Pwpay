<?php

declare(strict_types=1);

namespace  pwpay\group19\Repository;

use DateTime;
use PDO;
use pwpay\group19\Model\User;
use pwpay\group19\Model\UserRepository;
//CREAR AQUI FUNCIONES DE BBDD
final class MySqlUserRepository implements UserRepository{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    
    private PDOSingleton $database;
    
    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }
    
    public function register(User $user): string
    {

        $query = <<<'QUERY'
        SELECT * FROM user WHERE email = :email
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();

        $statement->bindParam('email',$email,PDO::PARAM_STR);

        $statement->execute();

        if(!empty($statement->fetchAll())){
            return '';
        }


        $query = <<<'QUERY'
        INSERT INTO user(email, password,birthdate,cellphone,created_at,updated_at,activated)
        VALUES( :email, :password, :birthdate, :cellphone, :created_at, :updated_at, :activated)
QUERY;

        $statement = $this->database->connection()->prepare($query);

        $password = $user->password();
        $birthdate = $user->birthdate()->format(self::DATE_FORMAT);
        $cellphone = $user->cellphone();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->updatedAt()->format(self::DATE_FORMAT);
        $activated = false;


        $statement->bindParam('email',$email,PDO::PARAM_STR);
        $statement->bindParam('password',$password,PDO::PARAM_STR);
        $statement->bindParam('birthdate',$birthdate,PDO::PARAM_STR);
        $statement->bindParam('cellphone', $cellphone, PDO::PARAM_STR);
        $statement->bindParam('created_at',$createdAt,PDO::PARAM_STR);
        $statement->bindParam('updated_at',$updatedAt,PDO::PARAM_STR);
        $statement->bindParam('activated',$activated,PDO::PARAM_BOOL);

        $statement->execute();

        $query = <<<'QUERY'
        SELECT id FROM user WHERE email = :email and password = :password
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email',$email,PDO::PARAM_STR);
        $statement->bindParam('password',$password,PDO::PARAM_STR);

        $statement->execute();

        $row = $statement->fetchAll();

        return (string)$row[0]['id'];

    }

    public function activate(string $token): bool{
        $query = <<<'QUERY'
        SELECT activated FROM user WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id',$token,PDO::PARAM_INT);

        $statement->execute();

        $row = $statement->fetchAll();

        if(empty($row)){
            return false;
        }

        if($row[0]['activated']){
            return false;
        }

        $query = <<<'QUERY'
        SELECT id FROM user WHERE password = "admin"
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->execute();
        $row = $statement->fetchAll();

        $adminId = $row[0]['id'];

        $query = <<<'QUERY'
        UPDATE user SET activated = true WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$token,PDO::PARAM_INT);

        $statement->execute();

        $query = <<<'QUERY'
        INSERT INTO transaction(sender_id,u_id,transaction_time,amount) VALUES(:sender_id, :u_id, :transaction_time, :amount)
QUERY;

        $transactionTime = new DateTime();
        $transactionTimeStr = $transactionTime->format(self::DATE_FORMAT);

        $amount = '20.0';

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('sender_id',$adminId,PDO::PARAM_INT);
        $statement->bindParam('u_id',$token,PDO::PARAM_INT);
        $statement->bindParam('transaction_time',$transactionTimeStr,PDO::PARAM_STR);
        $statement->bindParam('amount',$amount,PDO::PARAM_STR);

        $statement->execute();

        return true;
    }
    public function login(string $email, string  $password): int{
        $query = <<<'QUERY'
        SELECT * FROM user WHERE email = :email
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email',$email,PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetchAll();
        if(empty($row)){
            return -1;
        }

        if(password_verify($password,$row[0]['password'])){
            if(!$row[0]['activated']){
                return -2;
            }
            return (int)$row[0]['id'];
        }else{
            return -1;
        }



    }

    public function updatePassword (string $password, int $id):void {
        $this->updateUpdated_at($id);
        $query = <<<'QUERY'
        UPDATE user SET password = :password WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$id,PDO::PARAM_INT);
        $statement->bindParam('password',$password,PDO::PARAM_STR);

        $statement->execute();
    }

    public function updatePhone (string $cellphone, int $id):void {
        $this->updateUpdated_at($id);
        $query = <<<'QUERY'
        UPDATE user SET cellphone = :cellphone WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$id,PDO::PARAM_INT);
        $statement->bindParam('cellphone',$cellphone,PDO::PARAM_STR);

        $statement->execute();
    }


    public function updateUpdated_at (int $id):void {
        $updated_at = new DateTime();
        $updated_at = $updated_at->format(SELF::DATE_FORMAT);

        $query = <<<'QUERY'
        UPDATE user SET updated_at = :updated_at WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$id,PDO::PARAM_INT);
        $statement->bindParam('updated_at',$updated_at,PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUser(int $id): User{
        $query = <<<'QUERY'
        SELECT * FROM user WHERE id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$id,PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetchAll();

        if(empty($row)){
            return false;

        } else {

            $email = (string)$row[0]['email'];
            $password = $row[0]['password'];
            $birthdate = DateTime::createFromFormat('Y-m-d H:i:s', $row[0]['birthdate']);
            $cellphone = (string)$row[0]['cellphone'];
            if (isset($row[0]['createdAt'])) {
                $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $row[0]['createdAt']);
            } else {
                $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', "2022-04-28 18:10:07");
            }
            if (isset($row[0]['updatedAt'])) {
                $updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $row[0]['updatedAt']);
            } else {
                $updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', "2022-04-28 18:10:07");
            }

            $activated = (boolean)$row[0]['activated'];


            $user = new User($email, $password, $birthdate, $createdAt, $updatedAt, $activated, $cellphone, false);

            return $user;
        }

    }


    public function getEmail(string $u_id): string {
        $query = <<<'QUERY'
        SELECT email FROM user WHERE id = :u_id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_id',$u_id,PDO::PARAM_STR);
        $statement->execute();

        $data = $statement->fetchAll();
        return (string)$data[0]['email'];
    }

    public function getId(string $email):int{
        $query = <<<'QUERY'
        SELECT id FROM user WHERE email = :email and activated = 1;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('email',$email,PDO::PARAM_STR);
        $statement->execute();

        $data = $statement->fetchAll();
        return (int)($data[0]['id'] ?? 0);

    }

}
