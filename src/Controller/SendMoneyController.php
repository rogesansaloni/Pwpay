<?php

declare(strict_types=1);

namespace pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SendMoneyController{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showSendMoneyForm(Request $request,Response $response):Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        return $this->container->get('renderer')->render(
            $response,
            'send-money.twig',
            $data
        );
    }

    private function checkForErrors(string $email,string $amount): array{
        $errors = [];

        $this->container->get('validator')->verifyEmail($email,$errors);
        $this->container->get('validator')->verifyMoney($amount,$errors);

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

        return $response->withHeader('Location','/account/money/send')->withStatus(302);
    }

    public function sendMoneyAction(Request $request, Response $response):Response{
        $data = $request->getParsedBody();

        $error = $this->checkForErrors($data['TargetEmail'],$data['Amount']);

        if(!empty($error)){
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }

        if($this->container->get('transaction-repository')->getBalance((string)$this->container->get('user-id'))<$data['Amount']){
            $error['amount_error'] = 'Error, you don\'t have enough money to do that';
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }

        $targetid = $this->container->get('user_repository')->getId($data['TargetEmail']);
        if($targetid == $this->container->get('user-id')){
            $error['email'] = 'You cannot send money to yourself';
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
        }
        if($targetid>0){
            $senderid = $this->container->get('user-id');
            $this->container->get('transaction-repository')->sendMoney((string)$targetid,(string)$senderid,(int)$data['Amount']);
            return $response->withHeader('Location','/account/money/send')->withStatus(302);
        }else{
            $error['email'] = 'Error, user not registered or activated';
            return $this->getErrorResponse($response,$error,$data['TargetEmail'],$data['Amount']);
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
}