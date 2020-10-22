<?php

declare(strict_types=1);

namespace  pwpay\group19\Repository;

use DateTime;
use PDO;
use pwpay\group19\Model\Request;
use pwpay\group19\Model\TransactionRepository;

final class MySqlTransactionRepository implements TransactionRepository{
    private PDOSingleton $database;
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function addFunds(string $uid, int $amount)
    {
        $query = <<<'QUERY'
        INSERT INTO `transaction` (sender_id,u_id,transaction_time,amount) VALUES (:sender_id,:u_id,:transaction_time,:amount);
QUERY;
        $transactionTime = new DateTime();
        $transactionTimeStr = $transactionTime->format(self::DATE_FORMAT);

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('sender_id',$uid,PDO::PARAM_INT);
        $statement->bindParam('u_id',$uid,PDO::PARAM_INT);
        $statement->bindParam('transaction_time',$transactionTimeStr,PDO::PARAM_STR);
        $statement->bindParam('amount',$amount,PDO::PARAM_STR);

        $statement->execute();
    }

    public function recentTransactions (string $uid):array {
        $query = <<<'QUERY'
        SELECT * FROM `transaction` WHERE u_id = :u_id ORDER BY transaction_time DESC LIMIT 5;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_id',$uid,PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll();
        return $data;
    }

    public function getTransactionsAndRequests (string $uid):array {
        $query = <<<'QUERY'
               
        SELECT *, "2" as already_paid FROM `transaction` WHERE u_id = :u_id 
        UNION 
        SELECT u_requester_id as sender_id, u_requested_id as u_id, request_time as transaction_time, money_requested as amount,  already_paid
        FROM request WHERE u_requested_id = :u_id OR u_requester_id = :u_id
        
        ORDER BY transaction_time DESC;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_id',$uid,PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll();

        return $data;
    }

    public function getBalance(string $uid): float{
        $query = <<<'QUERY'
        SELECT SUM(amount) as balance FROM `transaction` WHERE u_id = :u_id;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_id',$uid,PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetchAll();
        $balance = $data[0]['balance'];
        return (float)$balance;
    }

    public function sendMoney(string $recipient, string $sender, int $amount){
        $query = <<<'QUERY'
        INSERT INTO `transaction` (sender_id,u_id,transaction_time,amount) VALUES(:sender,:uid,:transaction_time,:amount);
QUERY;
        $transactionTime = new DateTime();
        $transactionTimeStr = $transactionTime->format(self::DATE_FORMAT);

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('sender',$sender,PDO::PARAM_INT);
        $statement->bindParam('uid',$recipient,PDO::PARAM_INT);
        $statement->bindParam('transaction_time',$transactionTimeStr,PDO::PARAM_STR);
        $statement->bindParam('amount',$amount,PDO::PARAM_STR);

        $statement->execute();
        $amount *= -1;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('sender',$recipient,PDO::PARAM_INT);
        $statement->bindParam('uid',$sender,PDO::PARAM_INT);
        $statement->bindParam('transaction_time',$transactionTimeStr,PDO::PARAM_STR);
        $statement->bindParam('amount',$amount,PDO::PARAM_STR);
        $statement->execute();

    }

    public function requestMoney(string $requested, string $requester, int $amount){
        $isPaid = false;
        $query = <<<'QUERY'

        INSERT INTO `request` (u_requester_id,u_requested_id,money_requested,already_paid, request_time) VALUES(:u_requester_id,:u_requested_id,:money_requested,:already_paid, :request_time);
QUERY;

        $requestTime = new DateTime();
        $requestTimeStr = $requestTime->format(self::DATE_FORMAT);

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_requester_id',$requester,PDO::PARAM_INT);
        $statement->bindParam('u_requested_id',$requested,PDO::PARAM_INT);
        $statement->bindParam('money_requested',$amount,PDO::PARAM_STR);
        $statement->bindParam('already_paid',$isPaid,PDO::PARAM_BOOL);

        $statement->bindParam('request_time',$requestTimeStr,PDO::PARAM_STR);


        $statement->execute();

    }

    public function updateRequest (int $id_request, int $recipient, int $sender, float $amount) {
        $query = <<<'QUERY'
        UPDATE `request` SET already_paid = TRUE WHERE request_id = :id_request;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id_request',$id_request,PDO::PARAM_INT);
        $statement->execute();

        $this->sendMoney((string)$recipient, (string)$sender,(int) $amount);
    }

    public function pendingRequests (int $uid):array {
        $query = <<<'QUERY'
        SELECT * FROM `request` WHERE u_requested_id = :u_id AND already_paid = FALSE;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('u_id',$uid,PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll();
        return $data;
    }

    public function getRequest (string $id_request): Request {
        $query = <<<'QUERY'
        SELECT * FROM `request` WHERE request_id = :id_request;
QUERY;
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id_request',$id_request,PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetchAll();
        $request_id = (int)$row[0]['request_id'];
        $requester_id = (int)$row[0]['u_requester_id'];
        $requested_id = (int)$row[0]['u_requested_id'];
        $money_requested = (float)$row[0]['money_requested'];
        $already_paid = (bool)$row[0]['already_paid'];
        $request = new Request($request_id, $requester_id, $requested_id, $money_requested, $already_paid);
        return $request;
    }
}