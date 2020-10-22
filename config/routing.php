<?php

use pwpay\group19\Controller\BankController;
use \pwpay\group19\Controller\HomeController;
use \pwpay\group19\Controller\ProfileController;
use pwpay\group19\Controller\SendMoneyController;
use pwpay\group19\Controller\RequestMoneyController;
use pwpay\group19\Controller\SignInController;
use \pwpay\group19\Controller\SignUpController;
use pwpay\group19\Middleware\LoginRequirementMiddleware;
use \pwpay\group19\Middleware\StartSessionMiddleware;
use \pwpay\group19\Controller\DashboardController;
use \pwpay\group19\Controller\TransactionsController;

$app->add(StartSessionMiddleware::class);
$app->get('/', HomeController::class.':showHomePage')->setName('home');

$app->get('/sign-up',SignUpController::class.':showLoginForm')->setName('signup-form');

$app->get('/profile',ProfileController::class.':showProfilePage')->setName('profile')->add(LoginRequirementMiddleware::class);

$app->get('/profile/security',ProfileController::class.':changePasswordPage')->setName('password_change_page')->add(LoginRequirementMiddleware::class);;

$app->post('/profile/security',ProfileController::class.':changePassword')->setName('password_change')->add(LoginRequirementMiddleware::class);;

$app->post('/profile',ProfileController::class.':changeProfile')->setName('profile_change')->add(LoginRequirementMiddleware::class);;

$app->post('/sign-up',SignUpController::class.':signupAction')->setName('signup_action');

$app->get('/activate',SignUpController::class.':activateUser')->setName('activate-register');

$app->get('/sign-in',SignInController::class.':showSignUpForm')->setName('signin-form');

$app->post('/sign-in',SignInController::class.':signinAction')->setName('signin-action');

$app->post('/logout',SignInController::class.':logoutAction')->setName('logout-action');

$app->get('/account/summary', DashboardController::class.':showDashboardPage')->setName('dashboard')->add(LoginRequirementMiddleware::class);

$app->get('/account/bank-account',BankController::class.':showFormIBAN')->setName('iban-form')->add(LoginRequirementMiddleware::class);

$app->post('/account/bank-account',BankController::class.':actionAddIban')->setName('iban-action')->add(LoginRequirementMiddleware::class);

$app->post('/account/bank-account/load',BankController::class.':actionLoadMoney')->setName('load-money')->add(LoginRequirementMiddleware::class);


$app->get('/account/transactions', TransactionsController::class.':showTransactionsPage')->setName('transactions')->add(LoginRequirementMiddleware::class);


$app->get('/account/money/send',SendMoneyController::class.':showSendMoneyForm')->setName('send-money')->add(LoginRequirementMiddleware::class);

$app->post('/account/money/send',SendMoneyController::class.':sendMoneyAction')->setName('send-action')->add(LoginRequirementMiddleware::class);

$app->get('/account/money/requests',RequestMoneyController::class.':showRequestMoneyForm')->setName('request-money')->add(LoginRequirementMiddleware::class);

$app->post('/account/money/requests',RequestMoneyController::class.':requestMoneyAction')->setName('request-action')->add(LoginRequirementMiddleware::class);

$app->get('/account/money/requests/pending',RequestMoneyController::class.':showPendingRequestsForm')->setName('request-pending')->add(LoginRequirementMiddleware::class);

$app->get('/account/money/requests/{id}/accept', RequestMoneyController::class.':validateTransaction')->setName('pay-request')->add(LoginRequirementMiddleware::class);
