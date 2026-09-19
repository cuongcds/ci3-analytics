<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_analytics_visitors extends CI_Migration
{
    public function up()
    {
        $table = $this->config->item('analytics')['tables']['visitors'];

        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'visitor_uid' => ['type' => 'VARCHAR', 'constraint' => 64],
            'fingerprint' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'first_seen_at' => ['type' => 'INT', 'constraint' => 11],
            'last_seen_at' => ['type' => 'INT', 'constraint' => 11],
            'visit_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'device' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'browser' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table($table, true);

        $this->db->query('ALTER TABLE ' . $table . ' ADD UNIQUE KEY analytics_visitors_uid_unique (visitor_uid)');
        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_visitors_fingerprint_index (fingerprint)');
        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_visitors_last_seen_index (last_seen_at)');
    }

    public function down()
    {
        $table = $this->config->item('analytics')['tables']['visitors'];

        $this->dbforge->drop_table($table, true);
    }
}
