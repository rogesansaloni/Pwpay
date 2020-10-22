<?php

namespace  pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class TransactionsController {
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    public function showTransactionsPage(Request $request,Response $response):Response{
            $data = $messages['errors'][0] ?? [];
            $transactions = $this->manageTransactions();
            return $this->container->get('renderer')->render(
                $response,
                'transactions.twig',
                [
                    "id" => $this->container->get('user-id'),
                    "transactions" => $transactions,
                    "data" => $data],
                );
    }

    public function manageTransactions(): array{
        $transactions = $this->container->get('transaction-repository')->getTransactionsAndRequests($this->container->get('user-id'));


        $transactions_table = array();
        for ($i = 0; $i < count($transactions); $i++) {
            $transactions_table[$i]['amount'] = $transactions[$i]['amount'];
            $transactions_table[$i]['already_paid'] = $transactions[$i]['already_paid'];
            $id_s = $transactions[$i]['sender_id'];
            $transactions_table[$i]['sender_id'] = $id_s;
            $email_s = $this->container->get('user_repository')->getEmail($id_s);
            $transactions_table[$i]['sender'] = $email_s;
            $id_r = $transactions[$i]['u_id'];
            $transactions_table[$i]['receiver_id'] = $id_r;
            $email_r = $this->container->get('user_repository')->getEmail($id_r);
            $transactions_table[$i]['receiver'] = $email_r;

        }
        return $transactions_table;
    }

}