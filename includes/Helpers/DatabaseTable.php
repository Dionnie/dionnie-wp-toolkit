<?php
declare(strict_types=1);

namespace DionnieWPToolkit\Helpers;

class DatabaseTable {

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var string
     */
    protected string $tableName;

    /**
     * @var string
     */
    protected string $primaryKey;

    /**
     * Initialize the DatabaseTable utility.
     *
     * @param string $tableName  The table name (without the wp_ prefix).
     * @param string $primaryKey The primary key column name (defaults to 'id').
     */
    public function __construct(string $tableName, string $primaryKey = 'id') {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . $tableName;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Create the database table.
     * 
     * @param string $schemaDefinition The SQL column definitions (e.g., "id INT(11) NOT NULL AUTO_INCREMENT, name VARCHAR(255), PRIMARY KEY (id)").
     * @return void
     */
    public function createTable(string $schemaDefinition): void {
        $charsetCollate = $this->wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->tableName} (
            {$schemaDefinition}
        ) {$charsetCollate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Create a new record (Insert).
     *
     * @param array $data   Data to insert (in column => value pairs).
     * @param array|null $format An array of formats to be mapped to each of the value in $data.
     * @return int|false The ID of the inserted record, or false on failure.
     */
    public function insert(array $data, ?array $format = null): int|false {
        $result = $this->wpdb->insert($this->tableName, $data, $format);
        
        if ($result === false) {
            return false;
        }
        
        return $this->wpdb->insert_id;
    }

    /**
     * Read a single record by its primary key.
     *
     * @param int $id The primary key value.
     * @param string $outputType Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K.
     * @return array|object|null|void Database query result in format specified.
     */
    public function get(int $id, string $outputType = OBJECT) {
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = %d",
            $id
        );
        
        return $this->wpdb->get_row($query, $outputType);
    }

    /**
     * Update a record by its primary key.
     *
     * @param int $id       The primary key value.
     * @param array $data   Data to update (in column => value pairs).
     * @param array|null $format An array of formats to be mapped to each of the values in $data.
     * @return int|false The number of rows updated, or false on error.
     */
    public function update(int $id, array $data, ?array $format = null): int|false {
        $where = [$this->primaryKey => $id];
        $whereFormat = ['%d'];
        
        return $this->wpdb->update($this->tableName, $data, $where, $format, $whereFormat);
    }

    /**
     * Delete a record by its primary key.
     *
     * @param int $id The primary key value.
     * @return int|false The number of rows deleted, or false on error.
     */
    public function delete(int $id): int|false {
        $where = [$this->primaryKey => $id];
        $whereFormat = ['%d'];
        
        return $this->wpdb->delete($this->tableName, $where, $whereFormat);
    }

    /**
     * Drop the database table.
     *
     * @return int|bool Database query result.
     */
    public function dropTable(): int|bool {
        return $this->wpdb->query("DROP TABLE IF EXISTS {$this->tableName}");
    }
}