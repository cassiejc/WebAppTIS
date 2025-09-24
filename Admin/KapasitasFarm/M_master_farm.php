<?php
class M_master_farm extends CI_Model {
    public function insert_master_farm($data) {
        return $this->db->insert('master_farm', $data);
    }

    public function get_questions_by_page($page = 'master_farm') {
        $this->db->where('page', $page);
        return $this->db->get('questions')->result_array();
    }
}
