<?php

declare(strict_types=1);

namespace pwpay\group19\Controller;

use DateTime;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use pwpay\group19\Model\User;

class SignUpController{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function activateUser(Request $request, Response $response): Response{
        $response->getBody()->write(var_export(empty($_GET),true));
        if(empty($_GET) || !isset($_GET['token']) || !ctype_digit($_GET['token'])){
            $this->container->get('flash')->addMessage('warnings', "Warning, accessing activate without parameters");
        }else if($this->container->get('session-manager')->isLogged()){
            $this->container->get('flash')->addMessage('warnings', "Warning, cannot activate logged");
        }
        else{

            if(!$this->container->get('user_repository')->activate($_GET['token'])){
                $this->container->get('flash')->addMessage('warnings', "Warning, invalid token");
            }

        }

        return $response->withHeader('Location','/')->withStatus(302);
    }

    public function showLoginForm(Request $request, Response $response): Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        return $this->container->get('renderer')->render(
            $response,
            'signup.twig',
            $data
        );
    }

    public function signupAction(Request $request, Response $response): Response{

        $email = $_POST['email'];
        $password = $_POST['password'];
        $birthday= $_POST['birthday'];
        $cellphone = $_POST['cellphone'];

        $errors = $this->validate($_POST);
        if(empty($errors)){

            $user = new User($email,$password,DateTime::createFromFormat('Y-m-d',$birthday),new DateTime(),new DateTime(),false,$cellphone, true);
            $uid = $this->container->get('user_repository')->register($user);
            if(empty($uid)){
                $errors['email'] = 'Error, user already registered';
                return $this->getErrorResponse($response,$errors,$email,$password,$birthday,$cellphone);
            }else{
                $mailer = $this->container->get('mail');
                $mailer->mailAuthToUser($uid,$email);
            }
            return $response->withHeader('Location','/')->withStatus(302);
        }else{
            return $this->getErrorResponse($response,$errors,$email,$password,$birthday,$cellphone);
        }

    }

    private function getErrorResponse(Response $response,array $errors,string $email,string $password, string $birthday, string $cellphone): Response{
        $data = [];
        if(isset($errors['email'])){
            $data['email_error'] = $errors['email'];
        }
        if(isset($errors['password'])){
            $data['password_error'] = $errors['password'];
        }
        if(isset($errors['birthday'])){
            $data['birthday_error'] = $errors['birthday'];
        }
        if(isset($errors['cellphone'])){
            $data['cellphone_error'] = $errors['cellphone'];
        }

        $data['email_data'] = $email;
        $data['password_data'] = $password;
        $data['birthday_data'] = $birthday;
        $data['cellphone_data'] = $cellphone;

        $this->container->get('flash')->addMessage('errors', $data);

        return $response->withHeader('Location','/sign-up')->withStatus(302);
    }

    private function validate(array $data):array{
        $errors = [];

        $this->container->get('validator')->verifyCellphone($data['cellphone'],$errors);
        $this->container->get('validator')->verifyEmail($data['email'],$errors);
        $this->container->get('validator')->verifyPassword($data['password'],$errors);
        $this->container->get('validator')->verifyBirthday($data['birthday'],$errors);

        return $errors;
    }









}