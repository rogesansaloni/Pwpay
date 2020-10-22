<?php

declare(strict_types=1);

namespace pwpay\group19\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


final class ProfileController{
    private ContainerInterface $container;
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads/';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfilePage(Request $request,Response $response):Response{
        $messages = $this->container->get('flash')->getMessages();

        $user = $this->container->get('user_repository')->getUser($this->container->get('user-id'));

        $data = $messages['errors'][0] ?? [];

        $path = '/uploads/' . $this->container->get('user-id') . '.png';

        $hasPhoto = is_readable (substr($path, 1));


        return $this->container->get('renderer')->render(
            $response,
            'profile.twig',
            [
                "email" => $user->email(),
                "birthday" => $user->birthdate()->format('Y-m-d H:i:s'),
                "cellphone" => $user->cellphone(),
                "hasPhoto" => $hasPhoto,
                "data" => $data,
            ]
        );
    }


    public function changeProfile(Request $request, Response $response): Response{
        $errorsPhoto = [];
        $errors = [];
        if (isset($request->getParsedBody()['cellphone'])) {
            $phone = $request->getParsedBody()['cellphone'];
            $errors = $this->validate($request->getParsedBody());
        }


        if (!empty ($request->getUploadedFiles()) && $request->getUploadedFiles()['photoToUpload']-> getSize() > 0) {
            $photo = $request->getUploadedFiles()['photoToUpload'];

            $errorsPhoto = $this->validatePhoto($request->getUploadedFiles());
        }

        if (isset($phone)) {
            if (empty($errors)) {
                $user = $this->container->get('user_repository')->getUser($this->container->get('user-id'));
                if ($phone != $user->cellphone()) {
                    $this->container->get('user_repository')->updatePhone($phone, $this->container->get('user-id'));
                }
            } else {
                return $this->getErrorResponse($response,$errors,$phone);
            }
        }

        if (isset($photo)) {
            if (empty($errorsPhoto)) {
                $info = getimagesize($photo->getStream()->getMetadata()['uri']);
                $width = $info[0];
                $height = $info[1];
                $image = imagecreatefrompng($photo->getStream()->getMetadata()['uri']);

                if ($width > 400 || $height > 400) {

                    $image = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400]);
                }
                imagepng($image, self::UPLOADS_DIR.$this->container->get('user-id').'.png');
            } else {
                return $this->getErrorResponsePhoto($response,$errorsPhoto,$photo);
            }
        }

        return $response->withHeader('Location','/')->withStatus(302);

    }

    private function getErrorResponsePhoto(Response $response,array $errors,array $photo): Response{
        $data = [];

        if(isset($errors['photoToUpload'])){
            $data['photoToUpload_error'] = $errors['photoToUpload'];
        }

         $this->container->get('flash')->addMessage('errors', $data);


        return $response->withHeader('Location','/profile')->withStatus(302);
    }

    private function getErrorResponse(Response $response,array $errors,string $phone): Response{
        $data = [];
        if(isset($errors['cellphone'])){
            $data['cellphone_error'] = $errors['cellphone'];
        }

        $data['cellphone_data'] = $phone;

        $this->container->get('flash')->addMessage('errors', $data);

        return $response->withHeader('Location','/profile')->withStatus(302);
    }

    public function changePasswordPage(Request $request,Response $response):Response{

        $messages = $this->container->get('flash')->getMessages();

        $data = $messages['errors'][0] ?? [];

        return $this->container->get('renderer')->render(
            $response,
            'passwordChange.twig',
            $data
        );

    }

    public function changePassword(Request $request,Response $response):Response{
        $data = $request->getParsedBody();
        $old_password = $data['old_password'];
        $new_password = $data['new_password'];
        $confirm_password = $data['confirm_password'];

        $user = $this->container->get('user_repository')->getUser($this->container->get('user-id'));

        $errors = $this->validatePasswords($data, $user->password());


        if(empty($errors)) {
            $encrypted = password_hash($confirm_password,PASSWORD_ARGON2ID);
            $this->container->get('user_repository')->updatePassword($encrypted, $this->container->get('user-id'));
            return $response->withHeader('Location','/')->withStatus(301); //TODO make dashboard

        }else{
            return $this->getErrorResponsePasswords($response,$errors,$old_password,$new_password, $confirm_password);
        }

    }


    private function getErrorResponsePasswords(Response $response,array $errors,string $old_password,string $new_password, string $confirm_password): Response{
        $data = [];
        if(isset($errors['old_password'])){
            $data['error_old_password'] = $errors['old_password'];
        }
        if(isset($errors['new_password'])){
            $data['error_new_password'] = $errors['new_password'];
        }

        if(isset($errors['confirm_password'])){
            $data['error_confirm_password'] = $errors['confirm_password'];
        }

        if(isset($errors['confirm_password'])){
            $data['error_confirm_password'] = $errors['confirm_password'];
        }

        if(isset($errors['password'])){
            $data['error_confirm_password'] = $errors['password'];
        }

        $data['old_password'] = $old_password;
        $data['new_password'] = $new_password;
        $data['confirm_password'] = $confirm_password;

        $this->container->get('flash')->addMessage('errors', $data);

        return $response->withHeader('Location','/profile/security')->withStatus(302);
    }

    private function validatePasswords(array $data, string $password): array{

        $errors = [];

        if (empty($data['old_password'])) {
            $errors['old_password'] = 'Error, old password can\'t be empty';
        } else {
            if(!password_verify($data['old_password'],$password)) {
                $errors['old_password'] = 'Error, the old password is incorrect';
            }
        }

        if (empty($data['new_password'])) {
            $errors['new_password'] = 'Error, new password can\'t be empty';
        } else {
            $this->container->get('validator')->verifyPassword($data['new_password'],$errors);
        }

        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Error, confirm password can\'t be empty';
        } else {
            if (!empty($data['new_password'])) {
                if (strcmp ($data['new_password'], $data['confirm_password'] ) != 0) {
                    $errors['confirm_password'] = 'Error, the passwords are not equal';
                }
            }
        }

        return $errors;
    }

    private function validate(array $data): array{
        $errors = [];
        if (!empty($data)) {
            $this->container->get('validator')->verifyCellphone($data['cellphone'],$errors);
        }
        return $errors;
    }

    private function validatePhoto(array $data): array{
        $errors = [];


        $this->container->get('validator')->verifyPhoto($data['photoToUpload'],$errors);

        return $errors;
    }

}