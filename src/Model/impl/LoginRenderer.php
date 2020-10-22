<?php

namespace  pwpay\group19\Model\impl;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use pwpay\group19\Model\Renderer;

final class LoginRenderer implements Renderer{

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function render(Response $response, string $twig, array $params): Response
    {
        $isLogged =  $this->container->get('session-manager')->isLogged();

        if ($isLogged) {
            $path = '/uploads/' . $this->container->get('user-id') . '.png';
            $hasPhoto = is_readable (substr($path, 1));
            if (!$hasPhoto) {
                $path = '/uploads/defaultProfile.png';
            }
        } else {
            $path = '/uploads/defaultProfile.png';
        }

        $params["path_to_photo"] = $path;
        $params["islogged"] = $isLogged;
        return $this->container->get('view')->render($response,$twig,$params);
    }
}