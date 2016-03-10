<?php
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 11:52 PM
 */

require_once 'UserSession.php';

if ($_POST['idtoken']) {
    UserSession::login_google_auth($_POST['idtoken']);
    echo $_SESSION['user_id'];

} else {
    $user_session->setEmail($_POST['user_email']);
    $user_session->setPassword($_POST['user_pass']);
    $user_session->login();
}

header('Location: index.php');

