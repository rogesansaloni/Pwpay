<?php

declare(strict_types=1);

namespace pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SignInController{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showSignUpForm(Request $request,Response $response):Response{
        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];
        return $this->container->get('renderer')->render(
            $response,
            'signin.twig',
            $data
        );
    }

    public function logoutAction(Request $request, Response $response): Response{
        if($this->container->get('session-manager')->isLogged()){
            $this->container->get('session-manager')->logout();
        }else{
            $this->container->get('flash')->addMessage('warnings', "Warning, you are not logged in while trying to logout");
        }
        return $response->withHeader('Location','/')->withStatus(301);
    }

    public function signinAction(Request $request, Response $response): Response{
        $email = $_POST['email'];
        $password = $_POST['password'];

        $errors = $this->validate($_POST);

        if(empty($errors)) {
            $uid = $this->container->get('user_repository')->login($email,$password);
            if($uid>=0){
                $this->container->get('session-manager')->setLogin($uid);
                return $response->withHeader('Location','/')->withStatus(301); //TODO make dashboard
            }else if($uid===-1){
                $errors['email'] = 'Authentication error, email or password is wrong';
            }else{
                $errors['email'] = 'Error, account not activated';
            }
            return $this->getErrorResponse($response,$errors,$email,$password);
        }else{
            return $this->getErrorResponse($response,$errors,$email,$password);
        }


    }


    private function getErrorResponse(Response $response,array $errors,string $email,string $password): Response{
        $data = [];
        if(isset($errors['email'])){
            $data['email_error'] = $errors['email'];
        }
        if(isset($errors['password'])){
            $data['password_error'] = $errors['password'];
        }

        $data['email_data'] = $email;
        $data['password_data'] = $password;

        $this->container->get('flash')->addMessage('errors', $data);

        return $response->withHeader('Location','/sign-in')->withStatus(302);
    }

    private function validate(array $data): array{
        $errors = [];

        $this->container->get('validator')->verifyEmail($data['email'],$errors);
        $this->container->get('validator')->verifyPassword($data['password'],$errors);

        return $errors;
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