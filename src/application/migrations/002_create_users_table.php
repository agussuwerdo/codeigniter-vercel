<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Users_Table extends CI_Migration {

    public function up() {
        // Define the fields for the users table
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'username' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => TRUE,
            ),
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => TRUE,
            ),
            'password' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'NOW()',
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'NOW()',
                'on_update' => 'NOW()',
            ),
        );

        // Add the primary key and create the table
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
    }

    public function down() {
        // Drop the users table if it exists
        $this->dbforge->drop_table('users');
    }
}
