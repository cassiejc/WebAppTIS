<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_master_strain extends CI_Model {
    public function insert_master_strain($data) {
        return $this->db->insert('master_strain', $data);
    }
}
