<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_edit_user extends CI_Model
{
    // Mengambil semua data user dengan nama area & sub-area-nya
    public function get_all_users()
    {
        $this->db->select('
        u.*, 
        a.nama_area, 
        sa.nama_sub_area'
    );
        $this->db->from('z_master_user u');
        $this->db->join('master_area a', 'a.master_area_id = u.master_area_id', 'left');
        $this->db->join('master_sub_area sa', 'sa.master_sub_area_id = u.master_sub_area_id', 'left');
        return $this->db->get()->result_array();
    }

    // Mengambil satu data user berdasarkan ID
    public function get_user_by_id($id_user)
    {
        return $this->db->get_where('z_master_user', ['id_user' => $id_user])->row_array();
    }

    // Mengambil semua data area untuk dropdown
    public function get_all_areas()
    {
        return $this->db->get('master_area')->result_array();
    }

    // Mengambil semua data sub-area untuk dropdown
    public function get_all_sub_areas()
    {
        return $this->db->get('master_sub_area')->result_array();
    }

    // Memperbarui data user di database
    public function update_user($id_user, $data)
    {
        $this->db->where('id_user', $id_user);
        return $this->db->update('z_master_user', $data);
    }

    public function close_current_area_history($id_user, $end_date)
    {
        $this->db->where('id_user', $id_user);
        $this->db->where('end_date', '9999-12-31'); // Cari record yang masih aktif
        return $this->db->update('history_user_area', ['end_date' => $end_date]);
    }
    
    public function add_new_area_history($data)
    {
        return $this->db->insert('history_user_area', $data);
    }

    public function get_area_history_by_user($id_user)
    {
        $this->db->where('id_user', $id_user);
        $this->db->order_by('start_date', 'DESC');
        return $this->db->get('history_user_area')->result_array();
    }
}
