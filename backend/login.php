<?php
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 11:52 PM
 */

require_once 'UserSession.php';
require_once 'GoogleSession.php';

if (isset($_POST['idtoken'])) {
    $gsession = new GoogleSession($_POST['idtoken']);
    $gsession->login();
    echo $_SESSION['user_id'];

} else {
    $user_session = new UserSession();
    $user_session->setUser($_POST['user_name']);
    $user_session->setEmail($_POST['user_email']);
    $user_session->setPassword($_POST['user_pass']);
    $user_session->login();
}

header('Location: index.php');
