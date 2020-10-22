<?php

namespace  pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class BankController{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    public function showFormIBAN(Request $request, Response $response): Response{

        $iban = $this->container->get("iban-repository")->iban($this->container->get('user-id'));
        if(empty($iban)){
            return $this->RegisterIban($request,$response);
        }else{
            return $this->LoadAmount($iban,$request,$response);
        }
    }

    public function actionLoadMoney(Request $request, Response $response):Response{
        $data = $request->getParsedBody();
        $error = [];
        $this->container->get('validator')->verifyMoney($data['Amount'],$error);
        if(!empty($error)){
            $error['amount_data'] = $data['Amount'];
        }else{
            $this->container->get('transaction-repository')->addFunds($this->container->get('user-id'),$data['Amount']);
        }

        if(!empty($error)){
            $this->container->get('flash')->addMessage('errors', $error);
        }

        return $response->withHeader('Location','/account/bank-account')->withStatus(301);;
    }

    public function actionAddIban(Request $request, Response $response): Response{
        $data = $request->getParsedBody();
        if(!$this->container->get('iban-check')->validate($data['IBAN'])){
            $errors = [];
            $errors['iban_data'] = $data['IBAN'];
            $errors['iban_o_data'] = $data['IBANOwner'];
            $errors['iban_error'] = implode(',',$this->container->get('iban-check')->getViolations());
            $this->container->get('flash')->addMessage('errors', $errors);
        }else{
            $this->container->get("iban-repository")->addIban($this->container->get('user-id'),$data['IBAN'],$data['IBANOwner']);
        }
        $response->withHeader('Location','/account/bank-account')->withStatus(301);
        return $response;
    }

    private function  RegisterIban(Request $request, Response $response):Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        return $this->container->get('renderer')->render(
            $response,
            'add_iban.twig',
            $data
        );
    }

    private function LoadAmount(string $iban,Request $request, Response $response): Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        $data['iban_data'] = substr($iban,-4);
        return $this->container->get('renderer')->render(
            $response,
            'add_money.twig',
            $data
        );
    }
}