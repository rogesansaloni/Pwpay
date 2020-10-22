<?php

declare(strict_types=1);

namespace pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class RequestMoneyController{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    public function showRequestMoneyForm(Request $request, Response $response): Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        return $this->container->get('renderer')->render(
            $response,
            'request-money.twig',
            $data
        );
    }

    public function showPendingRequestsForm(Request $request, Response $response): Response{
        $info = $this->getPendingRequests();
        $messages = $this->container->get('flash')->getMessages();
        $data = $messages['errors'][0] ?? "";
        return $this->container->get('renderer')->render(
            $response,
            'pending-requests.twig',
            [
                "info" => $info,
                "data" => $data
            ]
        );
    }

    public function getPendingRequests():array {
        $data = $this->container->get('transaction-repository')->pendingRequests($this->container->get('user-id'));
        $information = [];
        $pendingRequests = array();
        $requestsId = array();
        for ($i = 0; $i < count($data); $i++) {
            $pendingRequests[$i] = "You owe " . $data[$i]['money_requested'] . "€ to ";
            $emailRequester = $this->container->get('user_repository')->getEmail($data[$i]['u_requester_id']);
            $pendingRequests[$i] .= $emailRequester;
            $requestsId[$i] = $data[$i]['request_id'];
        }
        $information['requestsText'] = $pendingRequests;
        $information['requestsId'] = $requestsId;
        return $information;
    }

    public function validateTransaction(Request $request, Response $response, $args): Response{
            $request_id = $request->getAttribute('id');
            $requestInfo = $this->container->get('transaction-repository')->getRequest($request_id);
            $requested_id = $requestInfo->getURequestedId();
            if($requested_id != $this->container->get('user-id')){
                $log_error = "Error, you were trying to pay a request that wasn't yours.";
                $this->container->get('flash')->addMessage('errors', $log_error);
            }else{
                $balanceUser = $this->container->get('transaction-repository')->getBalance((string)$this->container->get('user-id'));
                $moneyRequested = $requestInfo->getMoneyRequested();
                if($moneyRequested > $balanceUser){
                    $pay_error = "Error, you don't have enough money to pay that request.";
                    $this->container->get('flash')->addMessage('errors', $pay_error);
                }else{
                    $request_id = $requestInfo->getRequestId();
                    $sender_id = $requestInfo->getURequestedId();
                    $recipient_id =  $requestInfo->getURequesterId();
                    $this->container->get('transaction-repository')->updateRequest($request_id, $recipient_id, $sender_id, $moneyRequested);
                    $pay_successful = "Payment completed.";
                    $this->container->get('flash')->addMessage('errors', $pay_successful);
                }
            }
            return $response->withHeader('Location','/account/money/requests/pending')->withStatus(302);
    }

    private function checkForErrors(string $email,string $amount): array{
        $errors = [];

        $this->container->get('validator')->verifyEmail($email, $errors);
        $this->container->get('validator')->verifyMoney($amount, $errors);

        return $errors;
    }

    private function getErrorResponse(Response $response,array $errors,string $email,string $amount): Response{
        $data = [];
        if(isset($errors['email'])){
            $data['target_email_error'] = $errors['email'];
        }
        if(isset($errors['amount_error'])){
            $data['amount_error'] = $errors['amount_error'];
        }

        $data['target_email_data'] = $email;
        $data['amount_data'] = $amount;

        $this->container->get('flash')->addMessage('errors', $data);

        return $response->withHeader('Location','/account/money/requests')->withStatus(302);
    }

    public function requestMoneyAction(Request $request, Response $response):Response{
        $data = $request->getParsedBody();

        $error = $this->checkForErrors($data['TargetEmail'],$data['Amount']);

        if(!empty($error)){
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }

        $requestedid = $this->container->get('user_repository')->getId($data['TargetEmail']);
        if($requestedid == $this->container->get('user-id')){
            $error['email'] = 'You cannot request money from yourself';
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }
        if($requestedid>0){
            $requesterid = $this->container->get('user-id');
            $this->container->get('transaction-repository')->requestMoney((string)$requestedid,(string)$requesterid,(int)$data['Amount']);
            return $response->withHeader('Location','/account/money/requests')->withStatus(302);
        }else{
            $error['email'] = 'Error, the user you want to request money from is not registered or activated';
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }
    }

}
