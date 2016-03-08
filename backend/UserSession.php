<?php

require_once 'connection.php';
/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 8:11 PM
 */
class UserSession
{
    private $email;
    private $password;
    private $user;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function login() {
        $u = $this->checkCredentials();
        if ($u) {
            $this->setUser($u);
            $_SESSION['user_id'] = $u['id'];
            return $u['id'];
        } else {
            return false;
        }
    }

    private function checkCredentials() {
        $pdo = PDO_DB::factory();
        $sql_query = "SELECT * FROM users WHERE email=?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getEmail()));

        if ($sql_reg->rowCount() > 0) {
            $u = $sql_reg->fetch(PDO::FETCH_ASSOC);
            // TODO Password hashing
            if ($u['password'] == $u['password']) {
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
