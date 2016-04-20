<?php

require 'classes/User.php';

function redirect_failed(){
    header('Location: contribute.html');
    exit();
}

function redirect_success(){
    header('Location: index.php');
    exit();
}

//
//$username = $_POST['username'];
//$email = $_POST['email'];
//
//
//if (empty ($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])){
//    redirect_failed();
//}
//
//$username = $_POST['username'];
//$email = $_POST['email'];
//$password = $_POST['password'];

$username = 're2ato4';
$email = 'ab22c@abc.com';
$password = 'renato1';

$user = new User($username, $email, $password);

$register = $user->create();
var_dump($register);
echo $user->getUserId();
exit();

$user->create();

header('Location: index.php');
