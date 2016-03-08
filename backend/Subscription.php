<?php

require_once 'connection.php';


/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 3:37 AM
 */

class User
{
    private $_user_id;
    private $_name;
    private $_email;
    private $_password;
    private $_date_registered;

    public function __construct($email, $password) {
        $this->setEmail($email);
        $this->setPassword($password);
    }

    public function getUserId()
    {
        return $this->_user_id;
    }

    public function setUserId($_user_id)
    {
        $this->_user_id = $_user_id;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($_name)
    {
        $this->_name = $_name;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function setEmail($_email)
    {
        $this->_email = $_email;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($_password)
    {
        $this->_password = $_password;
    }

    public function getDateRegistered()
    {
        return $this->_date_registered;
    }

    public function setDateRegistered($_date_registered)
    {
        $this->_date_registered = $_date_registered;
    }


    public function create() {
        $pdo = PDO_DB::factory();
        $sql_query = "INSERT INTO users(name, email, password) VALUES (?, ?, ?)";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getEmail(), $this->getPassword()));
    }

    public static function read($user_id) {
        $pdo = PDO_DB::factory();
        $sql_query = "SELECT * FROM users WHERE users.id = :user_id";
        $stmt = $pdo->prepare($sql_query);
        $stmt->execute(array(':user_id' => $user_id));

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function update() {
        $pdo = PDO_DB::factory();
        $sql_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE users.id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getEmail(), $this->getPassword(), $this->getUserId()));
    }

    public function delete() {
        $pdo = PDO_DB::factory();
        $sql_query = "DELETE FROM users WHERE users.id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }
}


class Project
{
    private $_project_id;
    private $_name;
    private $_icon;
    private $_color;

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->_project_id;
    }

    /**
     * @param mixed $_project_id
     */
    public function setProjectId($_project_id)
    {
        $this->_project_id = $_project_id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $_name
     */
    public function setName($_name)
    {
        $this->_name = $_name;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->_icon;
    }

    /**
     * @param mixed $_icon
     */
    public function setIcon($_icon)
    {
        $this->_icon = $_icon;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->_color;
    }

    /**
     * @param mixed $_color
     */
    public function setColor($_color)
    {
        $this->_color = $_color;
    }

    public function add() {
        $pdo = PDO_DB::factory();
        $sql_query = "INSERT INTO project(name, icon, color) VALUES (?, ?, ?)";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getIcon(), $this->getColor()));
    }

    public function remove() {
        $pdo = PDO_DB::factory();
        $sql_query = "DELETE FROM project WHERE project.id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId()));
    }

    public function subscribe($user_id) {
        $pdo = PDO_DB::factory();
        $sql_query = "SELECT * FROM userprojects WHERE userprojects.user_id = :user_id";
        $stmt = $pdo->prepare($sql_query);
        $stmt->execute(array(':user_id' => $user_id));

        if ($stmt->fetchAll() === array()) {
            $sql_query = "INSERT INTO userprojects (project_id, user_id, is_subscribed) VALUES(?, ?, true)";
        } else {
            $sql_query = "UPDATE userprojects SET is_subscribed = true WHERE project_id = ? AND user_id = ?";
        }

        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
    }

    public function unsubscribe($user_id) {
        $pdo = PDO_DB::factory();
        $sql_query = "UPDATE userprojects SET is_subscribed = false WHERE project_id = ? AND user_id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(),  $user_id));
    }
}


class Newsletter
{
    private $user_id;
    private $frequency;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param mixed $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    public function subscribe() {
        $pdo = PDO_DB::factory();
        $sql_query = "INSERT INTO newsletter (frequency, user_id) VALUES(?, ?)";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getFrequency(), $this->getUserId()));
    }

    public function unsubscribe() {
        $pdo = PDO_DB::factory();
        $sql_query = "DELETE FROM newsletter WHERE user_id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }

    public function update_frequency($daily=false, $weekly=false, $monthly=true) {
        $pdo = PDO_DB::factory();
        $frequency = 0;

        if ($monthly) {
            $frequency = 30;
        } elseif ($weekly) {
            $frequency = 14;
        } elseif ($daily) {
            $frequency = 7;
        } else {
            $frequency = 30;
        }

        $sql_query = "UPDATE newsletter SET frequency = ? WHERE user_id = ?";
        $sql_reg = $pdo->prepare($sql_query);
        $sql_reg->execute(array($frequency, $this->getUserId()));
    }
}