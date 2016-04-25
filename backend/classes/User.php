<?php

require_once 'classes/connection.php';
require_once 'classes/EmailVerification.php';
require_once 'classes/Session.php';


class User {
    private $_user_id;
    private $_username;
    private $_email;
    private $_password;
    private $_date_registered;
    private $_dbo;

    public function __construct($username, $email, $password) {
        $this->setUsername((string)$username);
        $this->setEmail((string)$email);
        $this->setPassword((string)$password);
        $this->_dbo = PDO_DB::factory();
    }

    public function getUserId() {
        return $this->_user_id;
    }

    public function setUserId($_user_id) {
        $this->_user_id = $_user_id;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setUsername($_name) {
        $this->_username = $_name;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function setEmail($_email) {
        $this->_email = $_email;
    }

    public function getPassword() {
        return $this->_password;
    }

    public function setPassword($password) {
        $this->_password = $password;
    }

    public function getDateRegistered() {
        return $this->_date_registered;
    }

    public function setDateRegistered($_date_registered) {
        $this->_date_registered = $_date_registered;
    }

    public function validateUsername() {
        
        $username = $this->getUsername();
        
        // Do not accept usernames made only of numbers
        if (ctype_digit($username)) {
            //return $this->_validateReturn(FALSE, 'ERR_INVALID_USERNAME');
        }
        
        // Do not accept too big or too small usernames
        if (strlen($username) >= 15) {
            return $this->_returnArray(FALSE, 'ERR_USERNAME_TOO_BIG');
        } elseif (strlen($username) < 2) {
            return $this->_returnArray(FALSE, 'ERR_USERNAME_TOO_SMALL');
        }
        
        // Assert username starts with a character or any of: _.-
        if (!preg_match('/^[a-zA-Z0-9_.-]{2,15}$/', $username)) {
            return $this->_returnArray(FALSE, 'ERR_INVALID_USERNAME');
        }
        
        //Check if user already exists
        if ($this->read($username, 'username')) { 
           return $this->_returnArray(FALSE, 'ERR_USERNAME_EXISTS');
        }
        
        // Username is valid.
        return $this->_returnArray(TRUE);
    }
    
    public function validateEmail() {
        
        $email = $this->getEmail();
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_returnArray(FALSE, 'ERR_INVALID_EMAIL');
        }
        
        // Check if email already exists
        if ($this->read($email, 'email')) { 
           return $this->_returnArray(FALSE, 'ERR_EMAIL_EXISTS');
        }
        
        // Email is valid.
        return $this->_returnArray(TRUE);
        
    }
    
    public function validatePassword(){
        
        $password = $this->getPassword();
        
        if(strlen($password) < 6){
            return $this->_returnArray(FALSE, 'ERR_PASSWORD_SMALL');
        }
        
        if($password == $this->getUsername()){
            return $this->_returnArray(FALSE, 'ERR_PASSWORD_WEAK');
        }
        
        return $this->_returnArray(TRUE);
        
    }
    
    public function validateAll(){
        list($vu, $vu_msg) = $this->validateUsername();
        if (!$vu){
            return $this->_returnArray(FALSE, $vu_msg);
        }
        
        list($ve, $ve_msg) = $this->validateEmail();
        if(!$ve){
            return $this->_returnArray(FALSE, $ve_msg);
        }
        
        list($vp, $vp_msg) = $this->validatePassword();
        if(!$vp){
            return $this->_returnArray(FALSE, $vp_msg);
        }
        
        return $this->_returnArray(TRUE);
    }
    
    private function _returnArray($status, $msg = '') {
        return Array($status, $msg);
    }

    public function login(){
        
        $session = new Session();
        
        if($session->exists()){
            return $this->_returnArray(TRUE);
        }
        
        list($auth_success, $auth_msg) = $this->_authenticate();
        
        if(!$auth_success){
            return $this->_returnArray(FALSE, $auth_msg);
        }
        
        $session->setUserId($this->getUserId());
        $session->create();
        
        return $this->_returnArray(TRUE);
        
    }
    
    public function logout(){
        
        $session = new Session();
        
        if(!$session->exists()){
            return $this->_returnArray(FALSE, 'NOT_LOGGED');
        }
        
        $session->destroy();
        
        return Array(TRUE, '');
        
    }
    
    private function _authenticate(){

        $username = $this->getUsername();
        $password = $this->hashPassword($this->getPassword());
        
        $search_value = $username;
        $search_method = 'username';
        
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $this->setEmail($username);
            $this->setUsername(NULL);
            $search_method = 'email';
        }
        
        $query = $this->read($search_value, $search_method);
                
        if(!$query){
            return $this->_returnArray(FALSE, 'USERNAME_DOESNT_EXISTS');
        }
        
        if (!password_verify($this->getPassword(), $password)) {
            return $this->_returnArray(FALSE, 'PASSWORD_MISMATCH');
        }
        
        $this->setUserId($query->user_id);
        $this->setUsername($query->username);
        $this->setEmail($query->email);
        
        return $this->_returnArray(TRUE);
        
    }
    
    public function create() {
        
        list($validate_success, $validate_msg) = $this->validateAll();
        
        if(!$validate_success){
            return Array(FALSE, $validate_msg);
        }
        
        $sql_query = "INSERT INTO users(username, email, password) VALUES (?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUsername(), $this->getEmail(), User::hashPassword($this->getPassword())));

        $userID = $this->_dbo->lastInsertId('users_user_id_seq');
        
        $this->setUserId($userID);
        
        $email_verification = new EmailVerification('', $this);
        $verification_code = $email_verification->generateCode();
        
        //send email
        
        return Array(TRUE, $verification_code);
        
        //$this->insertUserLDAP();
    }

    private function read($search_value, $search_method='id', $limit = 1) {
                
        if ($search_method == 'id') {
            $column_name = 'user_id';
        } elseif($search_method == 'username' || $search_method == 'name' ) {
            $column_name = 'username';
        } elseif($search_method == 'email') {
            $column_name = 'email';
        } else {
            throw new Exception('No valid arguments for user read.');
        }
        
        if ($limit === FALSE) {
            $limit = '';
        } elseif (is_int($limit)) {
           $limit = 'LIMIT ' . (int)$limit; 
        } else {
            throw new Exception('Invalid limit parameter');
        }
        
        $dbo = PDO_DB::factory();
        $sql_query = "SELECT * FROM users WHERE users.".$column_name." = :value $limit";
        $stmt = $dbo->prepare($sql_query);
        $stmt->execute(array(':value' => $search_value));
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update() {
        $sql_query = "UPDATE users SET username = ?, email = ?, password = ? WHERE users.users_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUsername(), $this->getEmail(), $this->getPassword(), $this->getUserId()));
    }

    public function delete() {
        $sql_query = "DELETE FROM users WHERE users.users_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }

    private static function hashPassword($password) {
        $options = [
            'cost' => 14,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
}