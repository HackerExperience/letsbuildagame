<?php

if(php_sapi_name() != 'cli') exit();

require_once 'classes/Project.php';

// -----------------------------------------------------------------------------
// Add all teams to the database
// -----------------------------------------------------------------------------

$all_teams = all_teams();

foreach ($all_teams as $name => $team) {
    $team->getTeam()->add();
}

// -----------------------------------------------------------------------------
// Add all tasks to the database
// -----------------------------------------------------------------------------

$all_tasks = all_tasks();

foreach ($all_tasks as $name => $task_group) {
    foreach ($task_group as $task) {
        $task->getTask()->add();
    }
}

