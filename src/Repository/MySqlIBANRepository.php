<?php

declare(strict_types=1);

namespace  pwpay\group19\Repository;

use DateTime;
use PDO;
use pwpay\group19\Model\IBANRepository;

final class MySqlIBANRepository implements IBANRepository{
    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function iban(string $id): string{
        $query = <<<'QUERY'
        SELECT IBAN FROM bank_details WHERE u_id = :id
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id',$id,PDO::PARAM_INT);
        $statement->execute();

        $row = $statement->fetchAll();
        return $row[0]['IBAN'] ?? '';
    }

    public function addIban(string $id, string $iban, string $name):void{
        $query = <<<'QUERY'
        INSERT INTO bank_details (u_id,IBAN,IBAN_name) VALUES(:uid,:IBAN,:IBAN_name);
QUERY;
        $statment = $this->database->connection()->prepare($query);
        $statment->bindParam('uid',$id,PDO::PARAM_STR);
        $statment->bindParam('IBAN',$iban,PDO::PARAM_STR);
        $statment->bindParam('IBAN_name',$name,PDO::PARAM_STR);

        $statment->execute();
    }

}