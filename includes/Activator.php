<?php 

namespace DionnieWPToolkit\Core;
use DionnieWPToolkit\Helpers\DatabaseTable;

class Activator {

public static function activate(): void {

    $tasksTable = new DatabaseTable('dionnie_tasks');
    $schema = "
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        status varchar(50) DEFAULT 'pending' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ";
    $tasksTable->createTable($schema);
}

}   

?>