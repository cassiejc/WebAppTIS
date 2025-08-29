<?php
class M_master_lokasi_lainnya extends CI_Model {
    public function insert_master_lokasi_lainnya($data) {
        return $this->db->insert('master_lokasi_lainnya', $data);
    }

    public function get_questions_by_page($page = 'master_lokasi_lainnya') {
        $this->db->where('page', $page);
        return $this->db->get('questions')->result_array();
    }
}
