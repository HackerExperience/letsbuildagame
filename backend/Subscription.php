<?php

require_once 'connection.php';
require_once 'LDAP.php';


/**
 * Created by PhpStorm.
 * User: taiga
 * Date: 2/29/16
 * Time: 3:37 AM
 */
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


class Project {
    private $_project_id;
    private $_name;
    private $_icon;
    private $_color;
    private $_dbo;

    public function __construct($project_id, $name, $color, $icon) {
        $this->setProjectId($project_id);
        $this->setName($name);
        $this->setColor($color);
        $this->setIcon($icon);
        $this->_dbo = PDO_DB::factory();
    }

    /**
     * @return mixed
     */
    public function getProjectId() {
        return $this->_project_id;
    }

    /**
     * @param mixed $_project_id
     */
    public function setProjectId($_project_id) {
        $this->_project_id = $_project_id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param mixed $_name
     */
    public function setName($_name) {
        $this->_name = $_name;
    }

    /**
     * @return mixed
     */
    public function getIcon() {
        return $this->_icon;
    }

    /**
     * @param mixed $_icon
     */
    public function setIcon($_icon) {
        $this->_icon = $_icon;
    }

    /**
     * @return mixed
     */
    public function getColor() {
        return $this->_color;
    }

    /**
     * @param mixed $_color
     */
    public function setColor($_color) {
        $this->_color = $_color;
    }

    public function add() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "INSERT INTO projects(name, icon, color) VALUES (?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getIcon(), $this->getColor()));
    }

    public function remove() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "DELETE FROM projects WHERE projects.projects_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId()));
    }

    public function subscribe($user_id) {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "SELECT * FROM user_projects WHERE user_projects.user_id = :user_id";
        $stmt = $this->_dbo->prepare($sql_query);
        $stmt->execute(array(':user_id' => $user_id));

        if ($stmt->fetchAll() === array()) {
            $sql_query = "INSERT INTO user_projects (project_id, user_id, is_subscribed) VALUES(?, ?, TRUE)";
        } else {
            $sql_query = "UPDATE user_projects SET is_subscribed = TRUE WHERE project_id = ? AND user_id = ?";
        }

        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
    }

    public function unsubscribe($user_id) {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "UPDATE user_projects SET is_subscribed = FALSE WHERE project_id = ? AND user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
    }
}


class Newsletter {
    private $_user_id;
    private $_frequency;
    private $_dbo;

    public function __construct($user_id, $frequency) {
        $this->setUserId($user_id);
        $this->setFrequency($frequency);
        $this->_dbo = PDO_DB::factory();
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->_user_id;
    }

    /**
     * @param mixed $_user_id
     */
    public function setUserId($_user_id) {
        $this->_user_id = $_user_id;
    }

    /**
     * @return mixed
     */
    public function getFrequency() {
        return $this->_frequency;
    }

    /**
     * @param mixed $_frequency
     */
    public function setFrequency($_frequency) {
        $this->_frequency = $_frequency;
    }

    public function subscribe() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "INSERT INTO newsletter (frequency, user_id) VALUES(?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getFrequency(), $this->getUserId()));
    }

    public function unsubscribe() {
        $this->_dbo = PDO_DB::factory();
        $sql_query = "DELETE FROM newsletter WHERE user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getUserId()));
    }

    public function update_frequency($daily = false, $weekly = false, $monthly = true) {
        $this->_dbo = PDO_DB::factory();

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
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($frequency, $this->getUserId()));
    }
}
