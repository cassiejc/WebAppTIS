<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Questions extends CI_Model {
    
    private $field_types = [
        'number_fields' => [
            'efektif_terisi_cp_petelur', 'efektif_terisi_non_cp_petelur', 'doa_woa_petelur', 'deplesi_petelur', 'intake_petelur',
            'produksi_telur_petelur', 'berat_telur_petelur', 'fcr_petelur', 'layer_pakai_pakan_cp', 'layer_selain_pakan_cp', 'layer_jumlah_kandang', 'layer_hen_day',
            'layer_lama_puncak_produksi', 'layer_deplesi', 'layer_intake', 'layer_produksi_telur',
            'layer_berat_telur', 'layer_fcr', 'layer_umur_tertua', 'layer_umur_termuda',
            'suhu_kandang_layer', 'kelembapan_kandang_layer', 'petelur_umur'
        ],
        'currency_fields' => [
            'harga_jual_telur_terakhir', 'layer_harga_jual_telur', 'layer_harga_beli_jagung',
            'layer_harga_beli_katul', 'layer_harga_afkir', 'harga_live_bird'
        ],
        'decimal_fields' => [
            'deplesi_petelur', 'intake_petelur', 'produksi_telur_petelur', 'berat_telur_petelur',
            'fcr_petelur', 'layer_hen_day', 'layer_deplesi', 'layer_intake', 'layer_produksi_telur',
            'layer_berat_telur', 'layer_fcr', 'suhu_kandang_layer', 'kelembapan_kandang_layer'
        ],
        'integer_fields' => [
            'efektif_terisi_cp_petelur', 'efektif_terisi_non_cp_petelur', 'doa_woa_petelur', 'layer_pakai_pakan_cp',
            'layer_selain_pakan_cp', 'layer_jumlah_kandang', 'layer_lama_puncak_produksi', 'layer_populasi',
            'layer_woa','layer_umur_tertua', 'layer_umur_termuda', 'petelur_umur'
        ],
        'letters_only_fields' => [],
        'varchar_fields' => ['layer_kode_label_pakan', 'layer_nama_kandang', 'petelur_kode_label_pakan']
    ];
    
    public function get_questions_by_page($page) {
        $this->db->where('page', $page);
        $questions = $this->db->get('questions')->result_array();
        
        foreach ($questions as &$q) {
            $q['data_field'] = $q['field_name'];
            
            if ($q['type'] === 'radio' || $q['type'] === 'select' || $q['type'] === 'checkbox') {
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

    /**
     * Get form questions with proper ordering and input types
     */
    public function get_form_questions($page, $user) {
        $all_questions = $this->get_questions_by_page($page);
        $questions = [];
        $other_questions = [];
        
        // Separate tipe_ternak question from others
        foreach($all_questions as $q) {
            if ($q['field_name'] === 'tipe_ternak') {
                array_unshift($questions, $q);
            } else {
                $other_questions[] = $q;
            }
        }
        
        // Add other questions after tipe_ternak
        $questions = array_merge($questions, $other_questions);
        
        // Set options and input types
        foreach($questions as &$q) {
            if ($q['type'] === 'select') {
                // MODIFIKASI: Meneruskan seluruh objek $user, bukan hanya master_sub_area_id
                $q['options'] = $this->get_question_options($q, $user);
            }
            
            $q = $this->_set_input_type($q);
        }
        
        return $questions;
    }

    /**
     * Get questions based on livestock type for AJAX requests
     */
    public function get_questions_by_livestock_type($tipe_ternak, $user) {
        $page = ($tipe_ternak === 'Layer') ? 'layer' : 'visiting_petelur';
        $questions = $this->get_questions_by_page($page);
        
        // Add tipe_ternak question for layer page
        if ($page === 'layer') {
            $tipe_ternak_question = $this->get_questions_by_page('visiting_petelur');
            foreach($tipe_ternak_question as $q) {
                if ($q['field_name'] === 'tipe_ternak') {
                    // MODIFIKASI: Meneruskan seluruh objek $user
                    $q['options'] = $this->get_question_options($q, $user);
                    array_unshift($questions, $q);
                    break;
                }
            }
        }
        
        // Set options with livestock type filtering
        foreach($questions as &$q) {
            if ($q['type'] === 'select' && $q['field_name'] !== 'tipe_ternak') {
                $q['options'] = $this->_get_filtered_options($q, $user, $tipe_ternak);
            }
            
            $q = $this->_set_input_type($q);
        }
        
        return $questions;
    }

    /**
     * Process form data with proper field type handling
     */
    public function process_form_data($page, $post_data, $user) {
        $questions = $this->get_questions_by_page($page);
        $insert_data = [
            'id_user' => $user['id_user'],
            'master_sub_area_id' => $user['master_sub_area_id']
        ];
        
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $value = isset($post_data[$input_name]) ? $post_data[$input_name] : '';
            
            $insert_data[$field] = $this->process_field_value($field, $value);
        }
        
        return $insert_data;
    }
   
    public function process_field_value($field, $value) {
        if (empty($value)) return $value;
        
        if (in_array($field, $this->field_types['currency_fields'])) {
            return (int)str_replace(',', '', $value);
        } elseif (in_array($field, $this->field_types['integer_fields'])) {
            return (int)str_replace(',', '', $value);
        } elseif (in_array($field, $this->field_types['decimal_fields'])) {
            return (float)str_replace(',', '.', $value);
        } elseif (in_array($field, $this->field_types['letters_only_fields'])) {
            return preg_replace('/[^a-zA-Z\s]/', '', trim($value));
        } elseif (in_array($field, $this->field_types['varchar_fields'])) {
            return trim($value);
        } elseif (in_array($field, $this->field_types['number_fields'])) {
            if (in_array($field, $this->field_types['integer_fields'])) {
                return (int)str_replace(',', '', $value);
            } else {
                return (float)str_replace(',', '.', $value);
            }
        }
        
        return $value;
    }

    /**
     * Get options for a question with combine_options support and filtering
     */
    // MODIFIKASI: Mengubah parameter kedua dari $master_sub_area_id menjadi $user
    public function get_question_options($question, $user, $tipe_ternak = null) {
        $options = [];
        
        if (!empty($question['combine_options'])) {
            $combine_question_ids = explode(',', $question['combine_options']);
            
            foreach ($combine_question_ids as $combine_id) {
                $combine_id = trim($combine_id);
                // MODIFIKASI: Meneruskan $user ke fungsi _get_options_with_filters
                $combined_options = $this->_get_options_with_filters($combine_id, $question['field_name'], $user, $tipe_ternak);
                $options = array_merge($options, $combined_options);
            }
        } else {
            // MODIFIKASI: Meneruskan $user ke fungsi _get_options_with_filters
            $options = $this->_get_options_with_filters($question['questions_id'], $question['field_name'], $user, $tipe_ternak);
        }
        
        return $this->_remove_duplicate_options($options);
    }

    // Private helper methods
    private function _set_input_type($question) {
        $field_name = $question['field_name'];
        
        if (in_array($field_name, $this->field_types['integer_fields'])) {
            $question['input_type'] = 'integer';
        } elseif (in_array($field_name, $this->field_types['number_fields'])) {
            $question['input_type'] = 'number';
            $question['step'] = in_array($field_name, $this->field_types['decimal_fields']) ? '0.01' : '1';
        } elseif (in_array($field_name, $this->field_types['currency_fields'])) {
            $question['input_type'] = 'currency';
        } elseif (in_array($field_name, $this->field_types['letters_only_fields'])) {
            $question['input_type'] = 'letters_only';
        } elseif (in_array($field_name, $this->field_types['varchar_fields'])) {
            $question['input_type'] = 'varchar';
        }
        
        return $question;
    }

    private function _get_filtered_options($question, $user, $tipe_ternak) {
        if ($question['field_name'] === 'nama_farm' || 
            ($tipe_ternak === 'Layer' && $question['field_name'] === 'layer_nama_farm') ||
            strpos($question['field_name'], 'strain') !== false ||
            $question['field_name'] === 'pakan_petelur' || 
            $question['field_name'] === 'layer_pakan') {
            
            // Special handling for Layer nama_farm
            if ($tipe_ternak === 'Layer' && $question['field_name'] === 'layer_nama_farm') {
                $nama_farm_questions = $this->get_questions_by_page('visiting_petelur');
                
                foreach($nama_farm_questions as $nf_q) {
                    if ($nf_q['field_name'] === 'nama_farm') {
                        // MODIFIKASI: Meneruskan seluruh objek $user
                        return $this->get_question_options($nf_q, $user, $tipe_ternak);
                    }
                }
            } else {
                // MODIFIKASI: Meneruskan seluruh objek $user
                return $this->get_question_options($question, $user, $tipe_ternak);
            }
        }
        
        // MODIFIKASI: Meneruskan seluruh objek $user
        return $this->get_question_options($question, $user);
    }

    // MODIFIKASI KESELURUHAN FUNGSI INI
    private function _get_options_with_filters($questions_id, $field_name, $user, $tipe_ternak = null) {
        // MODIFIKASI: Pindahkan SELECT ke dalam blok IF/ELSEIF
        // $this->db->select('o.option_text, o.tipe_ternak'); 
        $this->db->from('options o');
        $this->db->where('o.questions_id', $questions_id);

        // Filter ini hanya berlaku untuk dropdown nama farm
        if ($field_name === 'nama_farm' || $field_name === 'layer_nama_farm') {

            // MODIFIKASI: Tambahkan select untuk master_farm_id dan kapasitas_farm
            $this->db->select('o.option_text, o.tipe_ternak, o.master_farm_id, mf.kapasitas_farm');
            
            // MODIFIKASI: Tambahkan JOIN ke master_farm
            $this->db->join('master_farm mf', 'o.master_farm_id = mf.master_farm_id', 'left');

            // Langkah 1: Filter berdasarkan jangkauan user
            // TAMBAHKAN CEK UNTUK ADMINISTRATOR
            if (isset($user['group_user']) && $user['group_user'] === 'administrator') {
                // Administrator tidak ada filter area/sub-area, tampilkan semua
                // Tidak perlu menambahkan WHERE clause untuk area
            } 
            elseif (isset($user['group_user']) && $user['group_user'] === 'koordinator') {
                $this->db->join('master_sub_area msa', 'o.master_sub_area_id = msa.master_sub_area_id', 'left');
                $this->db->where('msa.master_area_id', $user['master_area_id']);
            } 
            else {
                // TIS atau user lain
                $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
            }

            // Langkah 2: Filter berdasarkan Tipe Ternak (tetap sama)
            if ($tipe_ternak) {
                $this->db->where('o.tipe_ternak', $tipe_ternak);
            } else {
                if ($field_name === 'nama_farm') {
                    // Tidak ada filter tipe_ternak untuk nama_farm saat load pertama
                } elseif ($field_name === 'layer_nama_farm') {
                    $petelur_types = ['Layer', 'Puyuh', 'Bebek Petelur', 'Arap'];
                    $this->db->where_in('o.tipe_ternak', $petelur_types);
                }
            }
        }
        elseif ($tipe_ternak && (strpos($field_name, 'strain') !== false ||
                                    $field_name === 'pakan_petelur' ||
                                    $field_name === 'layer_pakan')) {
            // MODIFIKASI: Select standar untuk field non-farm
            $this->db->select('o.option_text, o.tipe_ternak');
            $this->db->where('o.tipe_ternak', $tipe_ternak);
        }
        else {
            // MODIFIKASI: Fallback select standar
            $this->db->select('o.option_text, o.tipe_ternak');
        }

        return $this->db->get()->result_array();
    }

    private function _remove_duplicate_options($options) {
        $unique_options = [];
        $seen = [];
        
        foreach ($options as $option) {
            if (!in_array($option['option_text'], $seen)) {
                $unique_options[] = $option;
                $seen[] = $option['option_text'];
            }
        }
        
        return $unique_options;
    }

    private function _get_user_from_session() {
        $CI = &get_instance();
        $token = $CI->session->userdata('token');
        return $CI->dash->getUserInfo($token)->row_array();
    }

    /**
     * Get questions with filtered options based on user's sub area
     * Menghapus parameter $id_user
     */
    public function get_questions_with_filtered_options($page, $master_sub_area_id, $livestock_type = null) {
        $this->db->where('page', $page);
        $questions = $this->db->get('questions')->result_array();
        
        foreach ($questions as &$q) {
            $q['data_field'] = $q['field_name'];
            
            if ($q['type'] === 'radio' || $q['type'] === 'select' || $q['type'] === 'checkbox') {
                // Menghapus argumen id_user dari pemanggilan _get_user_options
                $user_options = $this->_get_user_options($q, $master_sub_area_id, $livestock_type);
                $global_options = $this->_get_global_options($q, $livestock_type);
                $q['options'] = array_merge($user_options, $global_options);
            }
        }
        
        return $questions;
    }

    /**
     * Get options for AJAX request based on livestock type
     * Menghapus parameter $id_user
     */
    public function get_options_by_livestock_type($questions_id, $master_sub_area_id, $livestock_type = null) {
        // Get user-specific options (sekarang menjadi sub_area_specific_options)
        $this->db->select('o.*')
                 ->from('options o')
                 ->where('o.questions_id', $questions_id)
                 ->where('o.master_sub_area_id', $master_sub_area_id);
                 // Menghapus filter berdasarkan id_user
                 // ->where('o.id_user', $id_user);
                 
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        $user_options = $this->db->get()->result_array();
        
        // Get global options
        $this->db->select('o.*')
                 ->from('options o')
                 ->where('o.questions_id', $questions_id)
                 ->where('o.master_sub_area_id', 0);
                 
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        $global_options = $this->db->get()->result_array();
        
        return array_merge($user_options, $global_options);
    }

    // Menghapus parameter $id_user dan query WHERE yang terkait
    private function _get_user_options($question, $master_sub_area_id, $livestock_type = null) {
        if (isset($question['combine_options']) && !empty($question['combine_options'])) {
            $combine_ids = explode(',', $question['combine_options']);
            $this->db->select('o.*, o.tipe_ternak')
                     ->from('options o')
                     ->where_in('o.questions_id', $combine_ids)
                     ->where('o.master_sub_area_id', $master_sub_area_id);
                     // Menghapus filter berdasarkan id_user
                     // ->where('o.id_user', $id_user);
        } else {
            $this->db->select('o.*, o.tipe_ternak')
                     ->from('options o')
                     ->where('o.questions_id', $question['questions_id'])
                     ->where('o.master_sub_area_id', $master_sub_area_id);
                     // Menghapus filter berdasarkan id_user
                     // ->where('o.id_user', $id_user);
        }
        
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        return $this->db->get()->result_array();
    }

    private function _get_global_options($question, $livestock_type = null) {
        if (isset($question['combine_options']) && !empty($question['combine_options'])) {
            $combine_ids = explode(',', $question['combine_options']);
            $this->db->select('o.*, o.tipe_ternak')
                     ->from('options o')
                     ->where_in('o.questions_id', $combine_ids)
                     ->where('o.master_sub_area_id', 0);
        } else {
            $this->db->select('o.*, o.tipe_ternak')
                     ->from('options o')
                     ->where('o.questions_id', $question['questions_id'])
                     ->where('o.master_sub_area_id', 0);
        }
        
        if (!empty($livestock_type)) {
            $this->db->where('o.tipe_ternak', $livestock_type);
        }
        
        return $this->db->get()->result_array();
    }

    /**
     * Get visiting questions with options filtering for specific visiting types
     */
    public function get_visiting_questions($page, $user) {
        $questions = $this->get_questions_by_page($page);
        
        foreach ($questions as &$q) {
            if ($q['type'] === 'radio' || $q['type'] === 'select') {
                // Get user-specific options
                $user_options = $this->_get_visiting_user_options($q, $user['master_sub_area_id']);
                
                // Get global options
                $global_options = $this->_get_visiting_global_options($q);
                
                // Merge options
                $q['options'] = array_merge($user_options, $global_options);
            }
        }
        
        return $questions;
    }

    /**
     * Get combined visiting questions from multiple pages
     */
    public function get_visiting_questions_combined($pages, $user) {
        $all_questions = [];
        
        foreach ($pages as $page) {
            $questions = $this->get_visiting_questions($page, $user);
            $all_questions = array_merge($all_questions, $questions);
        }
        
        return $all_questions;
    }

    /**
     * Process visiting form data from multiple pages
     */
    public function process_visiting_form_data($pages, $post_data, $user) {
        $form_data = [];
        
        foreach ($pages as $page) {
            $questions = $this->get_questions_by_page($page);
            
            foreach ($questions as $q) {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                
                if (isset($post_data[$input_name])) {
                    $form_data[$field] = $post_data[$input_name];
                }
            }
        }
        
        return $form_data;
    }

    private function _get_visiting_user_options($question, $master_sub_area_id) {
        if (isset($question['combine_options']) && !empty($question['combine_options'])) {
            $combine_ids = explode(',', $question['combine_options']);
            $this->db->select('o.*')
                     ->from('options o')
                     ->where_in('o.questions_id', $combine_ids)
                     ->where('o.master_sub_area_id', $master_sub_area_id);
        } else {
            $this->db->select('o.*')
                     ->from('options o')
                     ->where('o.questions_id', $question['questions_id'])
                     ->where('o.master_sub_area_id', $master_sub_area_id);
        }
        
        return $this->db->get()->result_array();
    }

    private function _get_visiting_global_options($question) {
        if (isset($question['combine_options']) && !empty($question['combine_options'])) {
            $combine_ids = explode(',', $question['combine_options']);
            $this->db->select('o.*')
                     ->from('options o')
                     ->where_in('o.questions_id', $combine_ids)
                     ->where('o.master_sub_area_id', 0);
        } else {
            $this->db->select('o.*')
                     ->from('options o')
                     ->where('o.questions_id', $question['questions_id'])
                     ->where('o.master_sub_area_id', 0);
        }
        
        return $this->db->get()->result_array();
    }
}
?>
