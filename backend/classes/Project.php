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
        $sql_query = "INSERT INTO projects(name, icon, color) VALUES (?, ?, ?)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getName(), $this->getIcon(), $this->getColor()));
    }

    public function remove() {
        $sql_query = "DELETE FROM projects WHERE projects.projects_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId()));
    }

    private function _ensureProjectId(){
        if($this->getProjectId()){
            return TRUE;
        }
        
        $query = $this->read($this->getName(), 'name');
        
        if (!$query) {
            return FALSE;
        }
        
        $this->setProjectId($query->project_id);
        $this->setColor($query->color);
        $this->setIcon($query->icon);
        
        return TRUE;
        
    }
       
    private function read($search_value, $search_method = 'id', $limit = 1) {
                
        if ($search_method == 'id') {
            $column_name = 'project_id';
        } elseif($search_method == 'project_name' || $search_method == 'name' ) {
            $column_name = 'name';
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
        
        $sql_query = "SELECT * FROM projects WHERE projects.".$column_name." = :value $limit";
        $stmt = $this->_dbo->prepare($sql_query);
        $stmt->execute(array(':value' => $search_value));
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function is_subscribed($user_id){
        
        $sql_query = "SELECT * FROM user_projects WHERE user_projects.user_id = :user_id AND user_projects.project_id = :project_id LIMIT 1";
        $stmt = $this->_dbo->prepare($sql_query);
        return $stmt->execute(array(':user_id' => $user_id, 'project_id' => $this->getProjectId()));

    }
    
    public function subscribe($user_id) {
        
        if (!$this->_ensureProjectId()){
            return FALSE;
        }
        
        if($this->is_subscribed($user_id)){
            return TRUE;
        }
        
        $sql_query = "INSERT INTO user_projects (project_id, user_id, is_subscribed) VALUES(?, ?, TRUE)";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
                
        return TRUE;
    
    }

    public function unsubscribe($user_id) {
        $sql_query = "UPDATE user_projects SET is_subscribed = FALSE WHERE project_id = ? AND user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
    }
}


class Team {
    
    private $_team;
    
    public function __construct($name){
            
        $team_name = 'TEAM_' . strtoupper($name);
        
        $this->setTeam(new Project(NULL, $team_name, 'color_team', 'icon_team'));
       
    }
    
    public function getTeam(){
        return $this->_team;
    }
    
    public function setTeam($team){
        $this->_team = $team;
    }
    
    public function join($user_id) {
        $this->getTeam()->subscribe($user_id);
    }
    
}

function all_teams(){
    
    $teams = Array(
        'dev' => new Team('dev'),
        'art' => new Team('art'),
        'mgt' => new Team('mgt'),
        'gd' => new Team('gd'),
        'translate' => new Team('translate'),
        'promote' => new Team('promote'),
        'student' => new Team('student'),
        'gamer' => new Team('gamer')
    );
    
    return $teams;
    
}

class Task {
    
    private $_task;
    
    public function __construct($name){
        
        $task_name = 'TASK_' .strtoupper($name);
        
        $this->setTask(new Project(NULL, $task_name, 'color_task', 'icon_task'));
        
    }
 
    public function getTask(){
        return $this->_task;
    }
    
    public function setTask($task){
        $this->_task = $task;
    }
    
    public function join($user_id) {
        $this->getTask()->subscribe($user_id);
    }
    
}

function all_tasks(){
    
    $tasks = Array(
        'write_tests' => new Task('write_tests'),
    );
    
    return $tasks;
    
}