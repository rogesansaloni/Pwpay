<?php

declare(strict_types=1);

namespace  pwpay\group19\Model;

use Psr\Http\Message\ResponseInterface as Response;

interface Renderer{
    public function render(Response $response, string $twig, array $params) : Response;
}