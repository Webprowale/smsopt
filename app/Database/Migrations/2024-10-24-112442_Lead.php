<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Lead extends Migration
{
    public function up()
{
    $this->forge->addField([
        'id'          => ['type' => 'INT', 'auto_increment' => true],
        'name'        => ['type' => 'VARCHAR', 'constraint' => '255'],
        'email'       => ['type' => 'VARCHAR', 'constraint' => '255'],
        'phone'       => ['type' => 'VARCHAR', 'constraint' => '11'],
        'category_id' => ['type' => 'INT','constraint' => 11, 'unsigned' => true ],
        'created_at'  => ['type' => 'DATETIME','null' => true],
        'updated_at'  => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('category_id', 'category', 'id',  'CASCADE', 'CASCADE');
    $this->forge->createTable('lead');
}

    public function down()
    {
        $this->forge->dropTable('lead');
    }
}
