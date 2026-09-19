<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics_visitor_model extends CI_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->table = $this->config->item('analytics')['tables']['visitors'];
    }

    public function touch($visitorUid, $fingerprint, $device, $browser)
    {
        $now = time();
        $existing = $this->db->where('visitor_uid', $visitorUid)->get($this->table)->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->update($this->table, [
                'last_seen_at' => $now,
                'fingerprint' => $fingerprint,
                'device' => $device,
                'browser' => $browser,
                'visit_count' => $existing['visit_count'] + 1,
            ]);
            return (int) $existing['id'];
        }

        $this->db->insert($this->table, [
            'visitor_uid' => $visitorUid,
            'fingerprint' => $fingerprint,
            'device' => $device,
            'browser' => $browser,
            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'visit_count' => 1,
        ]);
        return (int) $this->db->insert_id();
    }

    public function countUnique($from, $to)
    {
        if ($from !== null) {
            $this->db->where('last_seen_at >=', $from);
        }
        if ($to !== null) {
            $this->db->where('last_seen_at <=', $to);
        }
        return (int) $this->db->count_all_results($this->table);
    }
}
