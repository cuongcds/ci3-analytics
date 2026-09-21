<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics_event_model extends CI_Model
{
    protected $table;

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->table = $this->config->item('analytics')['tables']['events'];
    }

    public function record(array $data)
    {
        $data['created_at'] = time();
        $this->db->insert($this->table, $data);
        return (int) $this->db->insert_id();
    }

    public function countByType($eventType, $from, $to)
    {
        $this->db->where('event_type', $eventType);
        if ($from !== null) {
            $this->db->where('created_at >=', $from);
        }
        if ($to !== null) {
            $this->db->where('created_at <=', $to);
        }
        return (int) $this->db->count_all_results($this->table);
    }

    public function getDailySeries($eventType, $from, $to)
    {
        $this->db->select("FROM_UNIXTIME(created_at, '%Y-%m-%d') as day, COUNT(*) as total", false);
        $this->db->where('event_type', $eventType);
        $this->db->where('created_at >=', $from);
        $this->db->where('created_at <=', $to);
        $this->db->group_by('day');
        $this->db->order_by('day', 'asc');
        return $this->db->get($this->table)->result_array();
    }

    public function getDailyActiveVisitors($from, $to)
    {
        $this->db->select("FROM_UNIXTIME(created_at, '%Y-%m-%d') as day, COUNT(DISTINCT visitor_uid) as total", false);
        $this->db->where('event_type', 'page_view');
        $this->db->where('created_at >=', $from);
        $this->db->where('created_at <=', $to);
        $this->db->group_by('day');
        $this->db->order_by('day', 'asc');
        return $this->db->get($this->table)->result_array();
    }

    public function getEventTypeBreakdown($from, $to)
    {
        $this->db->select('event_type, COUNT(*) as total');
        $this->db->where('created_at >=', $from);
        $this->db->where('created_at <=', $to);
        $this->db->group_by('event_type');
        $this->db->order_by('total', 'desc');
        return $this->db->get($this->table)->result_array();
    }

    public function getTopSubjects($from, $to, $limit = 20)
    {
        $this->db->select('e.subject_id,
            SUM(CASE WHEN e.event_type = "page_view" THEN 1 ELSE 0 END) as page_views,
            SUM(CASE WHEN e.event_type = "click" THEN 1 ELSE 0 END) as clicks', false);
        $this->db->from($this->table . ' e');
        $this->db->where('e.subject_id is not null');
        $this->db->where('e.created_at >=', $from);
        $this->db->where('e.created_at <=', $to);
        $this->db->group_by('e.subject_id');
        $this->db->order_by('page_views', 'desc');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function getTopPaths($from, $to, $limit = 20)
    {
        $this->db->select('path, domain, COUNT(*) as page_views');
        $this->db->where('event_type', 'page_view');
        $this->db->where('path is not null');
        $this->db->where('created_at >=', $from);
        $this->db->where('created_at <=', $to);
        $this->db->group_by(['path', 'domain']);
        $this->db->order_by('page_views', 'desc');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }

    public function getTopDomains($from, $to, $limit = 20)
    {
        $this->db->select('domain,
            SUM(CASE WHEN event_type = "page_view" THEN 1 ELSE 0 END) as page_views,
            COUNT(DISTINCT visitor_uid) as unique_visitors', false);
        $this->db->where('domain is not null');
        $this->db->where('created_at >=', $from);
        $this->db->where('created_at <=', $to);
        $this->db->group_by('domain');
        $this->db->order_by('page_views', 'desc');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result_array();
    }
}
