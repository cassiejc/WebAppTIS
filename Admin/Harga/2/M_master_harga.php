<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_master_harga extends CI_Model {

    public function get_all_harga()
    {
        return $this->db->get('master_harga')->result_array();
    }

    public function get_harga_by_id($id)
    {
        return $this->db->get_where('master_harga', ['id_harga' => $id])->row_array();
    }

    public function create_harga($data)
    {
        // Tambahkan created_at dan updated_at saat membuat data baru
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('master_harga', $data);
    }

    // FUNGSI PENTING UNTUK UPDATE
    public function update_harga($id_harga, $data)
    {
        // Set 'updated_at' ke waktu saat ini setiap kali ada update
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id_harga', $id_harga);
        return $this->db->update('master_harga', $data);
    }

    public function delete_harga($id_harga)
    {
        return $this->db->delete('master_harga', ['id_harga' => $id_harga]);
    }
}
