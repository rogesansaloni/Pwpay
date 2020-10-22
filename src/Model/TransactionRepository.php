<?php

declare(strict_types=1);

namespace  pwpay\group19\Model;

interface TransactionRepository{
    public function addFunds(string $uid, int $amount);
    public function recentTransactions (string $uid): array;
    public function getTransactionsAndRequests (string $uid): array;
    public function getBalance(string $uid): float;
    public function sendMoney(string $recipient, string $sender, int $amount);
    public function requestMoney(string $requested, string $requester, int $amount);
    public function updateRequest (int $requestId, int $recipient, int $sender, float $amount);
    public function pendingRequests (int $uid): array;
    public function getRequest (string $id_request): Request;


}
