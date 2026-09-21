<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tracks which domain each event's page was actually served on — see
 * Open\Analytics\Support\Domain — so reports can break down page views /
 * most-viewed pages by domain.
 */
class Migration_Add_analytics_events_domain extends CI_Migration
{
    public function up()
    {
        $table = $this->config->item('analytics')['tables']['events'];

        $this->dbforge->add_column($table, [
            'domain' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'path'],
        ]);

        $this->db->query('ALTER TABLE ' . $table . ' ADD INDEX analytics_events_domain_index (domain, event_type, created_at)');
    }

    public function down()
    {
        $table = $this->config->item('analytics')['tables']['events'];

        $this->db->query('ALTER TABLE ' . $table . ' DROP INDEX analytics_events_domain_index');
        $this->dbforge->drop_column($table, 'domain');
    }
}
