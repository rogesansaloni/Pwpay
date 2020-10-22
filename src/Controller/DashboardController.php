<?php

namespace  pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class DashboardController {
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    public function showDashboardPage(Request $request,Response $response):Response{
            $data = $messages['errors'][0] ?? [];
            $balance = $this->container->get('transaction-repository')->getBalance($this->container->get('user-id'));
            $transactions = $this->manageTransactions();
            return $this->container->get('renderer')->render(
                $response,
                'dashboard.twig',
                [
                    "balance" => $balance,
                    "transactions" => $transactions,
                    "data" => $data],
                );
    }

    public function manageTransactions(): array{
        $transactions = $this->container->get('transaction-repository')->recentTransactions($this->container->get('user-id'));
        $transactions_dashboard = array();
        for ($i = 0; $i < count($transactions); $i++) {
            $transactions_dashboard[$i] = abs($transactions[$i]['amount']) . "€";
            if($transactions[$i]['amount'] < 0){
                $transactions_dashboard[$i] .= " to " ;
            } else{
                $transactions_dashboard[$i] .= " from " ;
            }
            $id = $transactions[$i]['sender_id'];
            $email = $this->container->get('user_repository')->getEmail($id);
            $transactions_dashboard[$i] .= $email;
        }
        return $transactions_dashboard;
    }
}