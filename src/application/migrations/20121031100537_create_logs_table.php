<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_logs_table extends CI_Migration {

    public function up() {
        // Define the fields for the logs table
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'level' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
            ),
            'message' => array(
                'type' => 'TEXT',
            ),
            'timestamp' => array(
                'type' => 'TIMESTAMP',
                'default' => 'NOW()',
            ),
        );

        // Add the primary key and create the table
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('logs');
    }

    public function down() {
        // Drop the logs table if it exists
        $this->dbforge->drop_table('logs');
    }
}
