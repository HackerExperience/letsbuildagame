<?php

require_once 'connection.php';

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
