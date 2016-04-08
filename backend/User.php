<?php

require_once 'connection.php';
require_once 'LDAP.php';

class User {
    private $_user_id;
    private $_name;
    private $_email;
    private $_password;
    private $_date_registered;
    private $_dbo;

    public function __construct($email, $password) {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->_dbo = PDO_DB::factory();
    }

    public function getUserId() {
        return $this->_user_id;
    }

    public function setUserId($_user_id) {
        $this->_user_id = $_user_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($_name) {
        $this->_name = $_name;
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
        $this->_password = User::passwordHash($password);
    }

    public function getDateRegistered() {
        return $this->_date_registered;
    }

    public function setDateRegistered($_date_registered) {
        $this->_date_registered = $_date_registered;
    }


    private function insertUserLDAP() {
        $ldap = new LDAP();

        $ldap->createUser($this->getName(), $this->getEmail(), $this->getPassword());

    }

    public function create() {
        $sql_query = "INSERT INTO users(name, email, password) VALUES (?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getEmail(), $this->getPassword()));

        $this->insertUserLDAP();
    }

    public static function read($user_id) {
        $dbo = PDO_DB::factory();
        $sql_query = "SELECT * FROM users WHERE users.users_id = :user_id";
        $stmt = $dbo->prepare($sql_query);
        $stmt->execute(array(':user_id' => $user_id));

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update() {
        $sql_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE users.users_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getEmail(), $this->getPassword(), $this->getUserId()));
    }

    public function delete() {
        $sql_query = "DELETE FROM users WHERE users.users_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }

    private static function passwordHash($password) {
        $options = [
            'cost' => 13,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
}
