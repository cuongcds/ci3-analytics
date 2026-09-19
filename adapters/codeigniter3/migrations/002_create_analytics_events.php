<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_analytics_events extends CI_Migration
{
    public function up()
    {
        $table = $this->config->item('analytics')['tables']['events'];

        $this->dbforge->add_field([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'auto_increment' => true],
            'visitor_uid' => ['type' => 'VARCHAR', 'constraint' => 64],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            // Nullable, host-app-defined subject id (a tool/product/article
            // row) — see Open\Analytics\Contracts\SubjectResolverInterface.
            'subject_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'path' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'referrer' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'device' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'browser' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'created_at' => ['type' => 'INT', 'constraint' => 11],
        ]);

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table($table, true);

        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_events_type_created_index (event_type, created_at)');
        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_events_subject_index (subject_id, event_type, created_at)');
        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_events_visitor_index (visitor_uid)');
    }

    public function down()
    {
        $table = $this->config->item('analytics')['tables']['events'];

        $this->dbforge->drop_table($table, true);
    }
}
