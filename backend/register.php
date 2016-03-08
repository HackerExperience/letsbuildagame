<?php

require_once 'Subscription.php';

$user = new User($_POST['user_email'], $_POST['user_pass']);
$user->setName($_POST['user_name']);
$user->create();

