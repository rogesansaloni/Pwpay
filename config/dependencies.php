<?php

use DI\Container;
use Iban\Validation\Validator;
use pwpay\group19\Model\impl\BasicRenderer;
use pwpay\group19\Model\impl\LoginRenderer;
use pwpay\group19\Model\MailSingleton;
use pwpay\group19\Model\SessionManager;
use pwpay\group19\Model\Verificator;
use pwpay\group19\Repository\MySqlIBANRepository;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use pwpay\group19\Repository\MySqlUserRepository;
use pwpay\group19\Repository\PDOSingleton;
use Psr\Container\ContainerInterface;
use pwpay\group19\Repository\MySqlTransactionRepository;


$container = new Container();

$container->set(
    'view',
    function (){
        return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
    }
);

$container->set(
    'renderer',
    function (ContainerInterface $container) {
        return new LoginRenderer($container);
    }
);

$container->set(
    'flash',
    function (){
        return new Messages();
    }
);

$container->set('db',function(){
    return PDOSingleton::getInstance(
        $_ENV['MYSQL_USER'],
        $_ENV['MYSQL_ROOT_PASSWORD'],
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_PORT'],
        $_ENV['MYSQL_DATABASE']
    );
});

$container->set('mail',function(){
   return MailSingleton::getInstance(
       $_ENV['EMAIL_HOST'],
       $_ENV['EMAIL_USER'],
       $_ENV['EMAIL_PASS'],
       $_ENV['EMAIL_PORT']
   );
});


$container->set('user_repository', function(ContainerInterface $container){
   return new MySqlUserRepository($container->get('db'));
});

$container->set('iban-repository',function (ContainerInterface $container){
   return new MySqlIBANRepository($container->get('db'));
});

$container->set('iban-check',function (ContainerInterface $container){
    return new Validator();
});

$container->set('transaction-repository',function (ContainerInterface $container){
    return new MySqlTransactionRepository($container->get('db'));
});

$container->set('session-manager',function(ContainerInterface $container){
    return SessionManager::getInstance();
});

$container->set('user-id',function (ContainerInterface $container){
   return $container->get('session-manager')->getLogged();
});

$container->set('validator',function (ContainerInterface $container){
    return new Verificator();
});