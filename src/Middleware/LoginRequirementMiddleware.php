<?php

namespace pwpay\group19\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as Resp;

final class LoginRequirementMiddleware{
    public function __invoke(Request $request, RequestHandler $next): Response
    {
        if(isset($_SESSION['uid'])){
            return $next->handle($request);
        }else{
            $response = new Resp();
            return $response->withStatus(301)->withHeader('Location','/sign-in');
        }
    }
}