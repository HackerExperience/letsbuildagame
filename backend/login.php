<?php
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 11:52 PM
 */

require_once 'UserSession.php';

$user_session = new UserSession();
$user_session->setEmail($_POST['user_email']);
$user_session->setPassword($_POST['user_pass']);

header('Location: index.php');