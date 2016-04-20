<?php

require_once 'classes/Session.php';
require_once 'classes/User.php';
require_once 'classes/EmailVerification.php';
require_once 'classes/Project.php';

$result = Array();
$result['status'] = 0;
$result['redirect'] = '';
$result['msg'] = '';

function update_result($status, $msg, $redirect = FALSE){
    global $result;
    $result['status'] = $status;
    $result['msg'] = $msg;
    if($redirect) {
        $result['redirect'] = $redirect;
    }
}


$post = Array(
    'func' => 'register-teams',
    'data' => Array(
        'team-dev' => TRUE, 
        'team-art' => TRUE, 
        'team-mgt' => TRUE, 
        'team-gd' => FALSE, 
        'adsfaqwfdqs' => FALSE, 
    )
);

$_POST = $post;
$_SERVER['REQUEST_METHOD'] = 'POST';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['func'])) {
    
    $function = $_POST['func'];
    
    switch($function){
        
        case 'validate-user':
            
            if (!isset($_POST['data'])) {
                break;
            }
            
            $username = $_POST['data'];
            
            $user = new User($username, '', '');
            
            list($result['status'], $result['msg']) = $user->validateUsername();
                                    
            break;
        
        case 'validate-email':
            
            if (!isset($_POST['data'])) {
                break;
            }
            
            $email = $_POST['data'];
            
            $user = new User('', $email, '');
            
            list($result['status'], $result['msg']) = $user->validateEmail();
            
            break;
        
        case 'register-user':
            
            if(!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])){
                break;
            }
            
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = new User($username, $email, $password);
            list($registration_success, $registration_msg) = $user->create();
           
            if ($registration_success) {
                $user->login();
            }
            
            update_result($registration_success, $registration_msg);
            
            break;
            
        case 'verify-email':
            
            if (!isset($_POST['code'])) {
                break;
            }
            
            $code = $_POST['code'];
            
            $verification = new EmailVerification($code, '');
            $result['status'] = $verification->validate();

            break;
            
        case 'register-teams':
            
            $session = new Session();
            if (!$session->exists()) {
                update_result(FALSE, 'SYSTEM_ERROR');
                break;
            }
            
            if(!isset($_POST['data'])) {
                break;
            }
            
            $teams = all_teams();
            
            $team_array = $_POST['data'];
            $join_list = Array();
            
            foreach($team_array as $key => $value){
                if ($value !== TRUE) {
                    continue;
                }
                
                if (strpos($key, 'team-') === false){
                    continue;
                }
                
                $team_id = substr($key, 5);
                
                
                if (!array_key_exists($team_id, $teams)){
                    continue;
                }
                
                $team_obj = $teams[$team_id];
                
                var_dump($team_obj);
                
                $team_obj->join($session->getUserId());
                
                echo $team_id;
            }
            
            update_result(TRUE, '');
            
            break;
            
    }
    
}

# Return the content in JSON format
header('Content-type: application/json');
die(json_encode($result));