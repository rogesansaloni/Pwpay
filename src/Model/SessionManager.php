<?php

declare(strict_types=1);

namespace  pwpay\group19\Model;

 class SessionManager{

     private static ?SessionManager $instance = null;

     private function __construct()
     {
     }

     public static function getInstance(){
         if(self::$instance == null){
             self::$instance = new self();
         }
         return self::$instance;
     }

     public function setLogin(int $uid){
         $_SESSION['uid']  = $uid;
     }

     public function logout(){
         unset($_SESSION['uid']);
     }

     public function isLogged():bool{
         return isset($_SESSION['uid']);
     }

     public function getLogged(){
         return $_SESSION['uid'];
     }
 }