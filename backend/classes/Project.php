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
        $stmt->execute(array(':user_id' => $user_id, 'project_id' => $this->getProjectId()));
        
        return $stmt->fetch(PDO::FETCH_OBJ);

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
        
        if (!$this->_ensureProjectId()){
            return FALSE;
        }
        
        $sql_query = "DELETE FROM user_projects WHERE project_id = ? AND user_id = ?";
        $sql_reg = $this->_dbo->prepare($sql_query);
        $sql_reg->execute(array($this->getProjectId(), $user_id));
        
        return TRUE;
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
        return $this->getTeam()->subscribe($user_id);
    }
    
}

function all_teams(){
    
    $teams = Array(
        'dev' => new Team('dev'),
        'art' => new Team('art'),
        'mgt' => new Team('mgt'),
        'gd' => new Team('gd'),
        'translation' => new Team('translation'),
        'patron' => new Team('patron'),
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
    
    public function subscribe($user_id) {
        return $this->getTask()->subscribe($user_id);
    }
    
    public function unsubscribe($user_id) {
        return $this->getTask()->unsubscribe($user_id);
    }
        
}

function all_tasks(){
    
    $tasks = Array(
        
        'dev' => Array(
            'submit-patches' => new Task('submit-patches'),
            'review-code' => new Task('review-code'),
            'write-tests' => new Task('write-tests'),
            'write-doc' => new Task('write-doc'),
            
            'sub-todo' => new Task('sub-todo'),
            'sub-waitingreview' => new Task('sub-waitingreview'),
            'sub-bug' => new Task('sub-bug'),
            'sub-discussion' => new Task('sub-discussion'),
            'sub-elixir' => new Task('sub-elixir'),
            'sub-python' => new Task('sub-python'),
            'sub-elm' => new Task('sub-elm'),
            'sub-fs' => new Task('sub-fs'),
            'sub-javascript' => new Task('sub-javascript'),
            'sub-php' => new Task('sub-php'),
            'sub-frontend' => new Task('sub-frontend'),
            'sub-backend' => new Task('sub-backend'),
            'sub-infrastructure' => new Task('sub-infrastructure'),
            'sub-security' => new Task('sub-security'),
            'sub-optimization' => new Task('sub-optimization'),
            'sub-ai' => new Task('sub-ai'),
            'sub-network' => new Task('sub-network'),
            'sub-databases' => new Task('sub-databases'),
            'sub-pm' => new Task('sub-pm'),
            'sub-linux' => new Task('sub-linux'),
            'sub-ios' => new Task('sub-ios'),
            'sub-android' => new Task('sub-android'),
            'sub-core' => new Task('sub-core'),
            'sub-mobile' => new Task('sub-mobile'),
            'sub-web' => new Task('sub-web'),
            'sub-terminal' => new Task('sub-terminal'),
            'sub-aerospike' => new Task('sub-aerospike'),
            'sub-consul' => new Task('sub-consul'),
            'sub-elastic' => new Task('sub-elastic'),
            'sub-docker' => new Task('sub-docker'),
            'sub-kafka' => new Task('sub-kafka'),
            'sub-samza' => new Task('sub-samza'),
            'sub-nginx' => new Task('sub-nginx'),
            'sub-haproxy' => new Task('sub-haproxy'),
            'sub-mnesia' => new Task('sub-mnesia'),
            'sub-phabricator' => new Task('sub-phabricator'),
            'sub-postgresql' => new Task('sub-postgresql'),
            'sub-ansible' => new Task('sub-ansible'),
        ),
        
        'art' => Array(
            'discuss-ui' => new Task('discuss-ui'),
            'design-ui' => new Task('design-ui'),
            'create-assets' => new Task('create-assets'),
            'review-ux' => new Task('review-ux'),
        ),
        
    );
    
    return $tasks;
    
}