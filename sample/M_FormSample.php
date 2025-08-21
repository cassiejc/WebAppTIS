<?php
class M_FormSample extends CI_Model {
    public function insert_sample($data) {
        
        return $this->db->insert('sample_form', $data);
    }

    public function get_questions_by_page($page = 'sample') {
        $this->db->where('page', $page);
        return $this->db->get('questions')->result_array();
    }
}
