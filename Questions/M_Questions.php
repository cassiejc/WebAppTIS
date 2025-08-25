<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Questions extends CI_Model
{
    public function get_questions_by_page($page)
    {
        $this->db->where('page', $page);
        $questions = $this->db->get('questions')->result_array();

        foreach ($questions as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select' || $q['type'] === 'checkbox') {
                // Jika field ini harus menggabungkan options dari multiple questions_id
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
    
    // Method untuk mendapatkan opsi subagen berdasarkan sub area
    public function get_subagen_options_by_sub_area($sub_area_ids)
    {
        $this->db->select('nama_subagen');
        $this->db->from('master_subagen');
        $this->db->where_in('master_sub_area_id', $sub_area_ids);
        $this->db->group_by('nama_subagen'); // Avoid duplicates
        $subagen_list = $this->db->get()->result_array();
        
        // Convert to same format as options
        $filtered_options = [];
        foreach ($subagen_list as $subagen) {
            $filtered_options[] = [
                'option_text' => $subagen['nama_subagen']
            ];
        }
        
        return $filtered_options;
    }

    // Method untuk mendapatkan opsi peternak berdasarkan sub area
    public function get_peternak_options_by_sub_area($sub_area_ids)
    {
        $this->db->select('nama_peternak');
        $this->db->from('master_peternak');
        $this->db->where_in('master_sub_area_id', $sub_area_ids);
        $this->db->group_by('nama_peternak'); // Avoid duplicates
        $peternak_list = $this->db->get()->result_array();
        
        // Convert to same format as options
        $filtered_options = [];
        foreach ($peternak_list as $peternak) {
            $filtered_options[] = [
                'option_text' => $peternak['nama_peternak']
            ];
        }
        
        return $filtered_options;
    }
    
}
