<?php
/**
 * Summary cards (unique visitors / page views / clicks). Meant to be
 * embedded into the host app's own dashboard grid — see README
 * "Embedding into your own dashboard". Expects: $report (the array
 * returned by Analytics_lib::buildReport()).
 */
$this->load->view('admin/components/stat_card', ['icon' => 'ri-group-line', 'value' => $report['unique_visitors'], 'label' => 'Unique visitors']);
$this->load->view('admin/components/stat_card', ['icon' => 'ri-eye-line', 'value' => $report['page_views'], 'label' => 'Page views']);
$this->load->view('admin/components/stat_card', ['icon' => 'ri-cursor-line', 'value' => $report['clicks'], 'label' => 'Clicks']);
