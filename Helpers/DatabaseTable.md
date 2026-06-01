Example Usage:

1. Creating the Table (e.g., on plugin activation hook):

use Dionnie\Utils\DatabaseTable;

$tasksTable = new DatabaseTable('dionnie_tasks');
$schema = "
id mediumint(9) NOT NULL AUTO_INCREMENT,
title varchar(255) NOT NULL,
description text NOT NULL,
status varchar(50) DEFAULT 'pending' NOT NULL,
created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
PRIMARY KEY (id)
";
$tasksTable->createTable($schema);

2. Performing CRUD Operations:

$tasksTable = new DatabaseTable('dionnie_tasks');

// Create
$newTaskId = $tasksTable->insert([
'title' => 'Fix Lighthouse Issues',
'description' => 'Improve color contrast and add main landmark.',
'status' => 'in-progress'
]);

// Read
$task = $tasksTable->get($newTaskId);
echo $task->title;

// Update
$tasksTable->update($newTaskId, [
'status' => 'completed'
]);

// Delete
$tasksTable->delete($newTaskId);
