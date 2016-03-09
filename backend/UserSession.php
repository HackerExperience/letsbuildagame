<?php

require_once 'connection.php';

/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 8:11 PM
 */
class UserSession {
    private $_email;
    private $_password;
    private $_user;
    private $_dbo;

    public function __construct($email, $password, $user) {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setUser($user);
        $this->_dbo = PDO_DB::factory();
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * @param mixed $_email
     */
    public function setEmail($_email) {
        $this->_email = $_email;
    }

    /**
     * @return mixed
     */
    public function getPassword() {
        return $this->_password;
    }

    /**
     * @param mixed $_password
     */
    public function setPassword($_password) {
        $this->_password = $_password;
    }

    /**
     * @return mixed
     */
    public function getUser() {
        return $this->_user;
    }

    /**
     * @param mixed $_user
     */
    public function setUser($_user) {
        $this->_user = $_user;
    }

    public function login() {
        $u = $this->checkCredentials();
        if ($u) {
            session_regenerate_id();
            $_SESSION['user_id'] = $u['user_id'];
            return $u['id'];
        } else {
            return false;
        }
    }

    private function checkCredentials() {
        $sql_query = "SELECT * FROM users WHERE email=?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getEmail()));

        if ($sql_reg->rowCount() > 0) {
            $u = $sql_reg->fetch(PDO::FETCH_ASSOC);

            if (password_verify($this->getPassword(), $u['password'])) {
                return $u;
            }
        }
        return false;
    }

    private function logout() {
        session_destroy();
        unset($_SESSION['user_id']);
    }
}
