<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Visiting extends CI_Model {
    
    private $table_mapping = [
        'Kantor' => 'visiting_kantor',
        'Agen' => 'visiting_agen', 
        'Peternak' => 'visiting_peternak',
        'Kemitraan' => 'visiting_kemitraan',
        'Sub Agen' => 'visiting_subagen',
        'Koordinasi' => 'visiting_koordinasi',
        // Update pedaging mappings
        'Grower' => 'visiting_p_grower',
        'Bebek Pedaging' => 'visiting_p_bebek_pedaging'
    ];

    public function __construct() {
        parent::__construct();
    }

    public function insert_visiting($data, $type) {
        // For pedaging types, check tipe_ternak instead of livestock_type
        if (isset($data['tipe_ternak']) && array_key_exists($data['tipe_ternak'], $this->table_mapping)) {
            $table = $this->table_mapping[$data['tipe_ternak']];
        } 
        // For other visiting types
        else if (array_key_exists($type, $this->table_mapping)) {
            $table = $this->table_mapping[$type];
        }
        else {
            throw new Exception('Invalid visiting or livestock type');
        }
        
        return $this->db->insert($table, $data);
    }

    public function get_questions_by_page($page = 'visiting') {
        $this->db->where('page', $page);
        $questions = $this->db->get('questions')->result_array();

        foreach ($questions as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                    $combine_ids = explode(',', $q['combine_options']);
                    $this->db->where_in('questions_id', $combine_ids);
                    $q['options'] = $this->db->get('options')->result_array();
                } else {
                    $this->db->where('questions_id', $q['questions_id']);
                    $q['options'] = $this->db->get('options')->result_array();
                }
            }
        }

        return $questions;
    }

    public function get_all_pages() {
        $this->db->distinct();
        $this->db->select('page');
        return $this->db->get('questions')->result_array();
    }

    public function get_questions_by_field_name($field_name) {
        $this->db->where('field_name', $field_name);
        return $this->db->get('questions')->result_array();
    }

    // Helper method for database creation
    public function create_visiting_tables() {
        foreach ($this->table_mapping as $table) {
            if ($table !== 'visiting' && !$this->db->table_exists($table)) {
                // Create table using visiting as template
                $this->db->query("CREATE TABLE {$table} LIKE visiting");
            }
        }
    }
}
