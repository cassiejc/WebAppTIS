<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('form_validation');
        $this->load->model('M_Dash', 'dash');
        $this->load->model('M_tambah_data_baru_master', 'tambah_data_baru_master');
        $this->load->model('M_Questions');
        $this->load->model('M_master_peternak');
        $this->load->model('M_master_subagen');
        $this->load->model('M_master_agen');
        $this->load->model('M_master_kemitraan');
        $this->load->model('M_master_farm');
        $this->load->model('M_master_lokasi_lainnya');
        $this->load->model('M_master_pakan');
        $this->load->model('M_master_strain');
        $this->load->model('M_target'); 
        $this->load->model('M_edit_user');
        $this->load->model('M_master_harga');
    }

    public function subagen($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/subagen');
            return;
        }
        $this->_handle_update_form('Sub Agen', 'master_subagen', $id);
    }
    
    public function agen($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/agen');
            return;
        }
        $this->_handle_update_form('Agen', 'master_agen', $id);
    }
    
    public function peternak($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/peternak');
            return;
        }
        $this->_handle_update_form('Peternak', 'master_peternak', $id);
    }
    
    public function kemitraan($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/kemitraan');
            return;
        }
        $this->_handle_update_form('Kemitraan', 'master_kemitraan', $id);
    }
    
    public function farm($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/farm');
            return;
        }
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->update_farm($id);
        } else {
            $this->edit_farm($id);
        }
    }
    
    public function edit_farm($id)
    {
        $existing_data = $this->M_master_farm->get_farm_by_id($id);

        if (empty($existing_data)) {
            show_44();
        }

        $questions = $this->M_Questions->get_questions_by_page('master_farm_edit');
        
        $this->_process_options($questions, 'Farm', null);
        
        $data = [
            'title'             => 'CP-APPS | Edit Farm',
            'page_title'        => 'Edit Farm',
            'kategori_selected' => 'Farm',
            'edit_id'           => $id,
            'existing_data'     => $existing_data,
            'questions_kategori'=> $questions,
            'form_action'       => site_url('Admin_Controller/farm/' . $id)
        ];

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    public function update_farm($id)
    {
        $master_farm_id = $id;

        if (!$master_farm_id) {
            $this->session->set_flashdata('error', 'ID Farm tidak valid.');
            redirect('Admin_Controller/list_data/farm');
            return;
        }
        
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');

        $questions = $this->M_Questions->get_questions_by_page('master_farm_edit');
        $update_data = [];
        foreach($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            $update_data[$field] = $jawaban;
        }

        if (!isset($update_data['start_date']) || empty($update_data['start_date'])) {
            $this->session->set_flashdata('error', 'Tanggal Efektif Perubahan Kapasitas wajib diisi.');
            redirect('Admin_Controller/farm/' . $master_farm_id);
            return;
        }
        
        $new_capacity = $update_data['kapasitas_farm'];
        $start_date_str = $update_data['start_date'];

        $currentFarm = $this->M_master_farm->get_farm_by_id($master_farm_id);
        $capacityHasChanged = ($currentFarm['kapasitas_farm'] != $new_capacity);
        
        $this->db->trans_start();

        if ($capacityHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_master_farm->close_current_capacity_history($master_farm_id, $end_date_str);

            $new_history_data = [
                'master_farm_id' => $master_farm_id,
                'kapasitas'      => $new_capacity,
                'start_date'     => $start_date_str,
                'end_date'       => '9999-12-31'
            ];
            $this->M_master_farm->add_new_capacity_history($new_history_data);
        }
        
        $data_farm = [
            'nama_farm'          => $update_data['nama_farm'],
            'kapasitas_farm'     => $new_capacity,
            'master_peternak_id' => $update_data['master_peternak_id'],
            'tipe_ternak'        => $update_data['tipe_ternak'],
            'alamat_farm'        => $update_data['alamat_farm']
        ];
        $this->M_master_farm->update_farm_data($master_farm_id, $data_farm);
        
        if (isset($currentFarm['nama_farm']) && isset($update_data['nama_farm'])) {
            $old_name = trim($currentFarm['nama_farm']);
            $new_name = trim($update_data['nama_farm']);
            
            if ($old_name !== $new_name) {
                $this->_update_farm_options(
                    $currentFarm, 
                    $update_data, 
                    $is_admin ? $currentFarm['master_sub_area_id'] : $user['master_sub_area_id'], 
                    $user['id_user']
                );
            }
        }
        
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data farm.');
        } else {
            $this->session->set_flashdata('success', 'Data farm berhasil diperbarui dan riwayat kapasitas telah disimpan!');
        }

        redirect('Admin_Controller/list_data/farm');
    }

    public function lokasibaru($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/lokasibaru');
            return;
        }
        $this->_handle_update_form('Lokasi Baru', 'master_lokasi_lainnya', $id);
    }
    
    public function pakan($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/pakan');
            return;
        }
        $this->_handle_update_form('Pakan', 'master_pakan', $id);
    }

    // Method untuk Strain
    public function strain($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/strain');
            return;
        }
        $this->_handle_update_form('Strain', 'master_strain', $id);
    }

    public function target($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/target');
            return;
        }
        $this->edit_target($id); 
    }
    
    public function edit_target($id_target)
    {
        $existing_data = $this->M_target->get_target_by_id($id_target);

        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data target tidak ditemukan');
            redirect('Admin_Controller/list_data/target');
            return;
        }
        
        $data = [
            'title' => 'CP-APPS | Edit Target',
            'page_title' => 'Edit Target',
            'kategori_selected' => 'Target', 
            'edit_id' => $id_target,
            'existing_data' => $existing_data,
            'questions_kategori' => $this->M_Questions->get_questions_by_page('master_target_edit'),
            'form_action' => site_url('Admin_Controller/update_target')
        ];

        if (!isset($data['existing_data']['start_date'])) {
            $data['existing_data']['start_date'] = date('Y-m-d');
        }
        
        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function update_target()
    {
        $id_target = $this->input->post('edit_id');

        $questions = $this->M_Questions->get_questions_by_page('master_target_edit');
        $update_data = [];
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            $update_data[$field] = $jawaban;
        }

        $new_target_value = $update_data['target'];
        $start_date_str = $update_data['start_date'];

        $currentTarget = $this->M_target->get_target_by_id($id_target);
        $current_user_id = $currentTarget['id_user'];

        $targetHasChanged = ($currentTarget['target'] != $new_target_value);

        $this->db->trans_start();

        if ($targetHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_target->close_current_target_history($current_user_id, $end_date_str);

            $new_history_data = [
                'id_target'  => $id_target,
                'id_user'    => $current_user_id,
                'target'     => $new_target_value,
                'start_date' => $start_date_str,
                'end_date'   => '9999-12-31'
            ];
            $this->M_target->add_new_target_history($new_history_data);
        }

        $data_target = ['target' => $new_target_value];
        $this->M_target->update_target($id_target, $data_target);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui target.');
        } else {
            $this->session->set_flashdata('success', 'Target berhasil diperbarui dan riwayat telah disimpan!');
        }

        redirect('Admin_Controller/list_data/target');
    }
    
    public function add_target()
    {
        $data['users_without_target'] = $this->M_target->get_users_without_target();
        $data['title'] = 'CP-APPS | Tambah Target';
        $data['page_title'] = 'Tambah Target Baru';

        $this->load->view('templates/dash_h', $data);
        $this->load->view('add_target_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    public function create_target_action()
    {
        $this->form_validation->set_rules('id_user', 'User', 'required|is_unique[master_target.id_user]');
        $this->form_validation->set_rules('target', 'Nilai Target', 'required|numeric');
        $this->form_validation->set_rules('start_date', 'Tanggal Mulai', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Admin_Controller/add_target');
        } else {
            $data_target = [
                'id_user' => $this->input->post('id_user'),
                'target'  => $this->input->post('target')
            ];

            $this->db->trans_start();
            
            $new_target_id = $this->M_target->create_target($data_target);

            if (method_exists($this->M_target, 'add_new_target_history')) {
                $history_data = [
                    'id_target'  => $new_target_id,
                    'id_user'    => $this->input->post('id_user'),
                    'target'     => $this->input->post('target'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date'   => '9999-12-31' 
                ];
                $this->M_target->add_new_target_history($history_data);
            }
            
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan data target ke database.');
            } else {
                $this->session->set_flashdata('success', 'Target baru berhasil ditambahkan!');
            }
            
            redirect('Admin_Controller/list_data/target');
        }
    }

    public function user($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/user');
            return;
        }else {
            $this->edit_user($id);
        }
    }

    public function edit_user($id_user)
    {
        $existing_data = $this->M_edit_user->get_user_by_id($id_user);

        if (empty($existing_data)) {
            show_404();
        }

        $questions = $this->M_Questions->get_questions_by_page('master_user_edit');

        $this->_process_options($questions, 'User', null);

        $data = [
            'title'             => 'CP-APPS | Edit User',
            'page_title'        => 'Edit User',
            'kategori_selected' => 'User',
            'edit_id'           => $id_user,
            'existing_data'     => $existing_data,
            'questions_kategori'=> $questions,
            'form_action'       => site_url('Admin_Controller/update_user')
        ];

        if (!isset($data['existing_data']['start_date'])) {
            $data['existing_data']['start_date'] = date('Y-m-d');
        }

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function update_user()
    {
        $id_user = $this->input->post('edit_id');

        $questions = $this->M_Questions->get_questions_by_page('master_user_edit');
        $update_data_from_form = [];
        foreach ($questions as $q) {
            if ($q['type'] !== 'text_readonly') {
                $field = $q['field_name'];
                $input_name = 'q' . $q['questions_id'];
                $jawaban = $this->input->post($input_name);
                $update_data_from_form[$field] = $jawaban;
            }
        }

        $new_area_id = $update_data_from_form['master_area_id'];
        $new_sub_area_id = $update_data_from_form['master_sub_area_id'];
        $start_date_str = $update_data_from_form['start_date'];

        $currentUser = $this->M_edit_user->get_user_by_id($id_user);
        $areaHasChanged = ($currentUser['master_area_id'] != $new_area_id || $currentUser['master_sub_area_id'] != $new_sub_area_id);

        $this->db->trans_start();

        if ($areaHasChanged) {
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_edit_user->close_current_area_history($id_user, $end_date_str);

            $new_history_data = [
                'id_user' => $id_user,
                'master_area_id' => $new_area_id,
                'master_sub_area_id' => $new_sub_area_id,
                'start_date' => $start_date_str,
                'end_date' => '9999-12-31'
            ];
            $this->M_edit_user->add_new_area_history($new_history_data);
        }

        if (empty($new_sub_area_id)) {
            $new_sub_area_id = NULL;
        }

        $data_to_update = [
            'username'           => $update_data_from_form['username'],
            'master_area_id' => $new_area_id,
            'master_sub_area_id' => $new_sub_area_id,
            'caption' => $update_data_from_form['caption'],
            'is_active' => $update_data_from_form['is_active']
        ];
        $this->M_edit_user->update_user($id_user, $data_to_update);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data user.');
        } else {
            $this->session->set_flashdata('success', 'Data user berhasil diperbarui dan riwayat area telah disimpan!');
        }

        redirect('Admin_Controller/list_data/user');
    }
    public function add_harga()
    {
        $data = [
            'title' => 'CP-APPS | Tambah Harga',
            'page_title' => 'Tambah Harga Baru',
            'kategori_selected' => 'Harga',
            'questions_kategori' => $this->M_Questions->get_questions_by_page('master_harga_tambah'),
            'form_action' => site_url('Admin_Controller/create_harga_action')
            ] ;

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function create_harga_action()
    {
        $questions = $this->M_Questions->get_questions_by_page('master_harga_tambah');
        $insert_data = [];
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            $insert_data[$field] = $jawaban;
        }
        $this->M_master_harga->create_harga($insert_data);
        $this->session->set_flashdata('success', 'Harga baru berhasil ditambahkan!');
        redirect('Admin_Controller/list_data/harga');
    }

    public function harga($id = null) {
        if (!$id) {
            redirect('Admin_Controller/list_data/harga');
            return;
        }
        $this->_handle_update_form('Harga', 'master_harga_edit', $id);
    }
    
    private function _handle_update_form($kategori, $page, $id) {
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $submit = $this->input->post('submit_form');

        if (!$id) {
            $this->session->set_flashdata('error', 'ID data tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $existing_data = $this->_get_existing_data($kategori, $id, $user);
        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_url = strtolower(str_replace(' ', '', $kategori));

        $data = [
            'title' => 'CP-APPS | Edit ' . $kategori,
            'page_title' => 'Edit ' . $kategori,
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'existing_data' => $existing_data,
            'edit_id' => $id,
            'form_action' => site_url('Admin_Controller/' . $kategori_url . '/' . $id)
        ];

        $data['questions_kategori'] = $this->M_Questions->get_questions_by_page($page);

        $this->_process_options($data['questions_kategori'], $kategori, $user);

        if ($submit && !empty($data['questions_kategori'])) {
            $this->_process_update_submission($data['questions_kategori'], $kategori, $user, $page, $id, $existing_data);
        }

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    private function _get_existing_data($kategori, $id, $user) {
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');

        switch($kategori) {
            case 'Agen':
                $this->db->select('*')->from('master_agen')->where('master_agen_id', $id);
                if (!$is_admin) { 
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return $this->db->get()->row_array();
                
            case 'Kemitraan':
                $this->db->select('*')->from('master_kemitraan')->where('master_kemitraan_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return $this->db->get()->row_array();
                
            case 'Sub Agen':
                $this->db->select('*')->from('master_subagen')->where('subagen_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return $this->db->get()->row_array();
                
            case 'Peternak':
                return $this->db->select('*')->from('master_peternak')->where('master_peternak_id', $id)->get()->row_array();
                
            case 'Farm':
                $this->db->select('*')->from('master_farm')->where('master_farm_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return $this->db->get()->row_array();
                
            case 'Lokasi Baru':
                $this->db->select('*')->from('master_lokasi_lainnya')->where('master_lokasi_lainnya_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return $this->db->get()->row_array();
                
            case 'Pakan':
                return $this->db->select('*')->from('master_pakan')->where('master_pakan_id', $id)->get()->row_array();
                
            case 'Strain':
                return $this->db->select('*')->from('master_strain')->where('master_strain_id', $id)->get()->row_array();

            case 'Harga':
                return $this->M_master_harga->get_harga_by_id($id);

            case 'User':
                return $this->M_edit_user->get_user_by_id($id);

            case 'Target':
                return $this->M_target->get_target_by_id($id);
                
            default:
                return null;
        }
    }

    private function _process_options(&$questions_kategori, $kategori, $user) {
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');
        
        switch($kategori) {
            case 'Sub Agen':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                            $combine_ids = explode(',', $q['combine_options']);
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where_in('o.questions_id', $combine_ids);
                            if (!$is_admin) {
                                $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            }
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            if (!$is_admin) {
                                $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            }
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Peternak':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'jenis_peternak') {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                        elseif (in_array($q['field_name'], ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                            if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                                $combine_ids = explode(',', $q['combine_options']);
                                $this->db->select('o.option_text')
                                        ->from('options o')
                                        ->where_in('o.questions_id', $combine_ids);
                                if (!$is_admin) {
                                    $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                }
                                $q['options'] = $this->db->get()->result_array();
                            } else {
                                $this->db->select('o.option_text')
                                        ->from('options o')
                                        ->where('o.questions_id', $q['questions_id']);
                                if (!$is_admin) {
                                    $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
                                }
                                $q['options'] = $this->db->get()->result_array();
                            }
                        }
                        else {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Farm':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'select') {
                        if ($q['field_name'] == 'nama_farm') {
                            if (isset($q['combine_options']) && !empty($q['combine_options'])) {
                                $combine_ids = explode(',', $q['combine_options']);
                                $this->db->select('o.option_text')
                                        ->from('options o')
                                        ->where_in('o.questions_id', $combine_ids);
                            } else {
                                $this->db->select('o.option_text')
                                        ->from('options o')
                                        ->where('o.questions_id', $q['questions_id']);
                            }
                            
                            if (!$is_admin) {
                                $this->db->where('o.master_sub_area_id', $user['master_sub_area_id']);
                            }
                            $q['options'] = $this->db->get()->result_array();
                        }
                        elseif ($q['field_name'] == 'master_peternak_id') {
                            $peternak_list = $this->M_master_farm->get_all_peternak();
                            $q['options'] = array_map(function($p) {
                                return ['option_value' => $p['master_peternak_id'], 'option_text' => $p['nama_peternak']];
                            }, $peternak_list);
                        }
                        elseif ($q['field_name'] == 'tipe_ternak') {
                            $tipe_ternak_list = $this->M_master_farm->get_options_by_field_name('tipe_ternak', 'master_farm');
                            $q['options'] = array_map(function($t) {
                                return ['option_value' => $t['option_text'], 'option_text' => $t['option_text']];
                            }, $tipe_ternak_list);
                        }
                    }
                }
                break;
                
            case 'Pakan':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'tipe_ternak') {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;
                
            case 'Strain':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'radio' || $q['type'] === 'select') {
                        if ($q['field_name'] == 'tipe_ternak') {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        } else {
                            $this->db->select('o.option_text')
                                    ->from('options o')
                                    ->where('o.questions_id', $q['questions_id']);
                            $q['options'] = $this->db->get()->result_array();
                        }
                    }
                }
                break;

            case 'User':
                foreach($questions_kategori as &$q) {
                    if ($q['type'] === 'select') {
                        if ($q['field_name'] == 'master_area_id') {
                            $areas = $this->M_edit_user->get_all_areas();
                            $q['options'] = array_map(function($area) {
                                return ['option_value' => $area['master_area_id'], 'option_text' => $area['nama_area']];
                            }, $areas);
                        }
                        elseif ($q['field_name'] == 'master_sub_area_id') {
                            $sub_areas = $this->M_edit_user->get_all_sub_areas();
                            $q['options'] = array_map(function($sub_area) {
                                return ['option_value' => $sub_area['master_sub_area_id'], 'option_text' => $sub_area['nama_sub_area']];
                            }, $sub_areas);
                        }
                        elseif ($q['field_name'] == 'is_active') {
                            $q['options'] = [
                                ['option_value' => '1', 'option_text' => 'Aktif'],
                                ['option_value' => '0', 'option_text' => 'Tidak Aktif']
                            ];
                        }
                    }
                }
                break;
        }
    }

    private function _process_update_submission($questions_kategori, $kategori, $user, $page, $id, $existing_data) {
        $update_data = [];

        $jenis_peternak = null;
        foreach ($questions_kategori as $q) {
            if ($q['field_name'] == 'jenis_peternak') {
                $input_name = 'q' . $q['questions_id'];
                $jenis_peternak = $this->input->post($input_name);
                break;
            }
        }
        foreach ($questions_kategori as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);

            $should_be_required = false;
            if (!empty($q['required'])) {
                if (in_array($field, ['agen_dari', 'sub_agen_dari', 'kemitraan_dari'])) {
                    if (($field == 'agen_dari' && $jenis_peternak == 'Agen') ||
                        ($field == 'sub_agen_dari' && $jenis_peternak == 'Sub Agen') ||
                        ($field == 'kemitraan_dari' && $jenis_peternak == 'Kemitraan')) {
                        $should_be_required = true;
                    }
                } else {
                    $should_be_required = true;
                }
            }

            if ($should_be_required && (is_null($jawaban) || $jawaban === '')) {
                $this->session->set_flashdata('error', 'Mohon isi semua field yang wajib diisi: ' . $q['question_text']);
                redirect(current_url());
                return;
            }

            $update_data[$field] = $jawaban;
        }

        $this->_update_data($kategori, $update_data, $user, $page, $id, $existing_data);
        
        $kategori_url = strtolower(str_replace(' ', '', $kategori));

        $this->session->set_flashdata('success', 'Data berhasil diupdate!');
        redirect('Admin_Controller/list_data/' . $kategori_url);
    }
    
    private function _update_data($kategori, $update_data, $user, $page, $id, $existing_data) {
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');
        
        switch($kategori) {
            case 'Agen':
                $this->db->where('master_agen_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->update('master_agen', $update_data);
                        
                if (isset($existing_data['nama_agen']) && isset($update_data['nama_agen']) && 
                    $existing_data['nama_agen'] != $update_data['nama_agen']) {
                    $this->_update_options(
                        $page, 
                        $existing_data['nama_agen'], 
                        $update_data['nama_agen'], 
                        $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'],
                        $is_admin ? $existing_data['master_area_id'] : $user['master_area_id']
                    );
                }
                break;
                
            case 'Kemitraan':
                $this->db->where('master_kemitraan_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->update('master_kemitraan', $update_data);
                        
                if (isset($existing_data['nama_kantor_kemitraan']) && isset($update_data['nama_kantor_kemitraan']) && 
                    $existing_data['nama_kantor_kemitraan'] != $update_data['nama_kantor_kemitraan']) {
                    $this->_update_options(
                        $page, 
                        $existing_data['nama_kantor_kemitraan'], 
                        $update_data['nama_kantor_kemitraan'], 
                        $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'],
                        $is_admin ? $existing_data['master_area_id'] : $user['master_area_id']
                    );
                }
                break;
                
            case 'Sub Agen':
                $this->db->where('subagen_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->update('master_subagen', $update_data);
                        
                if (isset($existing_data['nama_subagen']) && isset($update_data['nama_subagen']) && 
                    $existing_data['nama_subagen'] != $update_data['nama_subagen']) {
                    $this->_update_options(
                        $page, 
                        $existing_data['nama_subagen'], 
                        $update_data['nama_subagen'], 
                        $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'],
                        $is_admin ? $existing_data['master_area_id'] : $user['master_area_id']
                    );
                }
                break;
                
            case 'Peternak':
                if (!empty($update_data['jenis_peternak'])) {
                    $jenis = $update_data['jenis_peternak'];
                    $nama_dari = '';
                    
                    if ($jenis === 'Agen' && !empty($update_data['agen_dari'])) {
                        $nama_dari = $update_data['agen_dari'];
                    } elseif ($jenis === 'Sub Agen' && !empty($update_data['sub_agen_dari'])) {
                        $nama_dari = $update_data['sub_agen_dari'];
                    } elseif ($jenis === 'Kemitraan' && !empty($update_data['kemitraan_dari'])) {
                        $nama_dari = $update_data['kemitraan_dari'];
                    }
                    
                    $update_data['jenis_peternak'] = !empty($nama_dari) ? "$jenis: $nama_dari" : $jenis;
                }
                
                unset($update_data['agen_dari']);
                unset($update_data['sub_agen_dari']); 
                unset($update_data['kemitraan_dari']);
                
                $this->db->where('master_peternak_id', $id)->update('master_peternak', $update_data);
                        
                if (isset($existing_data['nama_peternak']) && isset($update_data['nama_peternak']) && 
                    $existing_data['nama_peternak'] != $update_data['nama_peternak']) {
                    $this->_update_options(
                        $page, 
                        $existing_data['nama_peternak'], 
                        $update_data['nama_peternak'], 
                        $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'],
                        $is_admin ? $existing_data['master_area_id'] : $user['master_area_id']
                    );
                }
                break;
                
            case 'Farm':
                if (!empty($update_data['master_peternak_id'])) {
                    if (!is_numeric($update_data['master_peternak_id'])) {
                        $peternak = $this->db->select('master_peternak_id')
                                            ->from('master_peternak')
                                            ->where('nama_peternak', $update_data['master_peternak_id'])
                                            ->get()
                                            ->row();
                        
                        if ($peternak) {
                            $update_data['master_peternak_id'] = $peternak->master_peternak_id;
                        }
                    }
                }
                
                $this->db->where('master_farm_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->update('master_farm', $update_data);
                        
                if (isset($existing_data['nama_farm']) && isset($update_data['nama_farm'])) {
                    $old_name = trim($existing_data['nama_farm']);
                    $new_name = trim($update_data['nama_farm']);
                    
                    if ($old_name !== $new_name) {
                        $this->_update_farm_options(
                            $existing_data, 
                            $update_data, 
                            $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'], 
                            $user['id_user']
                        );
                    }
                }
                break;
                
            case 'Lokasi Baru':
                $this->db->where('master_lokasi_lainnya_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->update('master_lokasi_lainnya', $update_data);
                        
                if (isset($existing_data['nama_lokasi']) && isset($update_data['nama_lokasi']) && 
                    $existing_data['nama_lokasi'] != $update_data['nama_lokasi']) {
                    $this->_update_options(
                        $page, 
                        $existing_data['nama_lokasi'], 
                        $update_data['nama_lokasi'], 
                        $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'],
                        $is_admin ? $existing_data['master_area_id'] : $user['master_area_id']
                    );
                }
                break;
                
            case 'Pakan':
                $this->db->where('master_pakan_id', $id)->update('master_pakan', $update_data);
                        
                if (isset($existing_data['nama_pakan']) && isset($update_data['nama_pakan']) && 
                    $existing_data['nama_pakan'] != $update_data['nama_pakan']) {
                    $this->_update_pakan_options($existing_data, $update_data);
                }
                break;
                
            case 'Strain':
                $this->db->where('master_strain_id', $id)->update('master_strain', $update_data);
                        
                if (isset($existing_data['nama_strain']) && isset($update_data['nama_strain']) && 
                    $existing_data['nama_strain'] != $update_data['nama_strain']) {
                    $this->_update_strain_options($existing_data, $update_data);
                }
                break;

            case 'Harga':
                if (isset($update_data['nilai_harga'])) {
                    $update_data['nilai_harga'] = preg_replace('/[^\d]/', '', $update_data['nilai_harga']);
                    $update_data['nilai_harga'] = (int)$update_data['nilai_harga'];
                }
                
                $this->M_master_harga->update_harga($id, $update_data);
                break;

            default:
                return false;
        }
        
        return true;
    }
    private function _update_options($page, $old_text, $new_text, $master_sub_area_id, $master_area_id = null) {
        $questions = $this->db->select('questions_id')
                            ->from('questions')
                            ->where('page', $page)
                            ->where('add_to_options', 1)
                            ->get()
                            ->result_array();
        
        if (empty($questions)) {
            return false;
        }
        
        foreach ($questions as $question) {
            $where_conditions = [
                'questions_id' => $question['questions_id'],
                'option_text' => $old_text,
                'master_sub_area_id' => $master_sub_area_id
            ];
            
            if ($master_area_id !== null) {
                $where_conditions['master_area_id'] = $master_area_id;
            }
            
            $this->db->where($where_conditions)->update('options', ['option_text' => $new_text]);
        }
        
        return true;
    }

    private function _update_farm_options($existing_data, $update_data, $master_sub_area_id, $id_user) {
        $questions = $this->db->select('questions_id')
                            ->from('questions')
                            ->where('field_name', 'nama_farm')
                            ->where('add_to_options', 1)
                            ->get()
                            ->result_array();
        
        if (empty($questions)) {
            return false;
        }
        
        foreach ($questions as $question) {
            $where_conditions = [
                'questions_id' => $question['questions_id'],
                'option_text' => $existing_data['nama_farm'],
                'master_sub_area_id' => $master_sub_area_id
            ];
            
            if (isset($existing_data['master_area_id'])) {
                $where_conditions['master_area_id'] = $existing_data['master_area_id'];
            }
            
            $update_options_data = [
                'option_text' => $update_data['nama_farm']
            ];
            
            if (isset($update_data['tipe_ternak'])) {
                $update_options_data['tipe_ternak'] = $update_data['tipe_ternak'];
            }
            
            $this->db->where($where_conditions)->update('options', $update_options_data);
        }
        
        return true;
    }

    private function _update_pakan_options($existing_data, $update_data) {
        $questions = $this->db->select('questions_id')
                            ->from('questions')
                            ->where('page', 'master_pakan')
                            ->where('add_to_options', 1)
                            ->get()
                            ->result_array();
        
        if (empty($questions)) {
            return false;
        }
                        
        foreach ($questions as $question) {
            // Pakan bersifat global, update semua tanpa filter area
            $this->db->where('questions_id', $question['questions_id'])
                    ->where('option_text', $existing_data['nama_pakan'])
                    ->update('options', [
                        'option_text' => $update_data['nama_pakan'],
                        'tipe_ternak' => $update_data['tipe_ternak']
                    ]);
        }
        return true;
    }

    private function _update_strain_options($existing_data, $update_data) {
        $questions = $this->db->select('questions_id')
                            ->from('questions')
                            ->where('page', 'master_strain')
                            ->where('add_to_options', 1)
                            ->get()
                            ->result_array();
        
        if (empty($questions)) {
            return false;
        }
                    
        foreach ($questions as $question) {
            $this->db->where('questions_id', $question['questions_id'])
                    ->where('option_text', $existing_data['nama_strain'])
                    ->update('options', [
                        'option_text' => $update_data['nama_strain'],
                        'tipe_ternak' => $update_data['tipe_ternak']
                    ]);
        }
        return true;
    }
    public function list_data($kategori = null) {
        if (!$kategori) {
            redirect('Dashboard_new/index');
            return;
        }

        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $kategori_map = [
            'agen' => 'Agen',
            'kemitraan' => 'Kemitraan', 
            'subagen' => 'Sub Agen',
            'peternak' => 'Peternak',
            'farm' => 'Farm',
            'lokasibaru' => 'Lokasi Baru',
            'pakan' => 'Pakan',
            'strain' => 'Strain',
            'target' => 'Target',
            'user' => 'User',
            'harga' => 'Harga'
        ];

        if (!isset($kategori_map[$kategori])) {
            $this->session->set_flashdata('error', 'Kategori tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_name = $kategori_map[$kategori];
        
        $data_list = $this->_get_data_list($kategori_name, $user);
        
        $data = [
            'title' => 'CP-APPS | Daftar ' . $kategori_name,
            'page_title' => 'Daftar ' . $kategori_name,
            'kategori_selected' => $kategori_name,
            'data_list' => $data_list['data'],
            'table_headers' => $data_list['headers'],
            'table_fields' => $data_list['fields'],
            'primary_key' => $data_list['primary_key'],
            'display_field' => $data_list['display_field']
        ];

        $this->load->view('templates/dash_h', $data);
        $this->load->view('data_list_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    private function _get_data_list($kategori, $user) {
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');

        switch($kategori) {
            case 'Agen':
                $this->db->select('*')->from('master_agen');
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Agen', 'Alamat', 'Nomor Telepon'],
                    'fields' => ['nama_agen', 'alamat_agen', 'nomor_telepon_agen'],
                    'primary_key' => 'master_agen_id',
                    'display_field' => 'nama_agen'
                ];
                
            case 'Kemitraan':
                $this->db->select('*')->from('master_kemitraan');
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Kantor Kemitraan', 'Alamat', 'Nomor Telepon'],
                    'fields' => ['nama_kantor_kemitraan', 'alamat_kantor_kemitraan', 'nomor_telepon_kemitraan'],
                    'primary_key' => 'master_kemitraan_id',
                    'display_field' => 'nama_kantor_kemitraan'
                ];
                
            case 'Sub Agen':
                $this->db->select('*')->from('master_subagen');
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Sub Agen', 'Alamat'],
                    'fields' => ['nama_subagen', 'alamat_subagen'],
                    'primary_key' => 'subagen_id',
                    'display_field' => 'nama_subagen'
                ];
                
            case 'Peternak':
                $query = $this->db->select('*')->from('master_peternak')->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Peternak', 'Jenis Peternak', 'Alamat'],
                    'fields' => ['nama_peternak', 'jenis_peternak', 'alamat_peternak'],
                    'primary_key' => 'master_peternak_id',
                    'display_field' => 'nama_peternak'
                ];
                
            case 'Farm':
                $this->db->select('mf.*, mp.nama_peternak')->from('master_farm mf')->join('master_peternak mp', 'mf.master_peternak_id = mp.master_peternak_id', 'left');
                if (!$is_admin) {
                    $this->db->where('mf.master_sub_area_id', $user['master_sub_area_id']);
                }
                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Farm', 'Nama Peternak', 'Tipe Ternak', 'Alamat', 'Kapasitas'],
                    'fields' => ['nama_farm', 'nama_peternak', 'tipe_ternak', 'alamat_farm', 'kapasitas_farm'],
                    'primary_key' => 'master_farm_id',
                    'display_field' => 'nama_farm'
                ];
                
            case 'Lokasi Baru':
                $this->db->select('*')->from('master_lokasi_lainnya');
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Lokasi', 'Alamat'],
                    'fields' => ['nama_lokasi', 'alamat_lokasi'],
                    'primary_key' => 'master_lokasi_lainnya_id',
                    'display_field' => 'nama_lokasi'
                ];
                
            case 'Pakan':
                $query = $this->db->select('*')->from('master_pakan')->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Pakan', 'Tipe Ternak'],
                    'fields' => ['nama_pakan', 'tipe_ternak'],
                    'primary_key' => 'master_pakan_id',
                    'display_field' => 'nama_pakan'
                ];
                
            case 'Strain':
                $query = $this->db->select('*')->from('master_strain')->get();
                return [
                    'data' => $query->result_array(),
                    'headers' => ['Nama Strain', 'Tipe Ternak'],
                    'fields' => ['nama_strain', 'tipe_ternak'],
                    'primary_key' => 'master_strain_id',
                    'display_field' => 'nama_strain'
                ];

            case 'Target':
                return [
                    'data' => $this->M_target->get_all_target(),
                    'headers' => ['Username', 'Target'],
                    'fields' => ['username', 'target'],
                    'primary_key' => 'id_target',
                    'display_field' => 'username'
                ];

            case 'User':
                return [
                    'data' => $this->M_edit_user->get_all_users(),
                    'headers' => ['Username', 'Caption', 'Area', 'Sub Area', 'Status'],
                    'fields' => ['username', 'caption', 'nama_area', 'nama_sub_area', 'is_active'],
                    'primary_key' => 'id_user',
                    'display_field' => 'username'
                ];

            case 'Harga':
                $all_harga = $this->M_master_harga->get_all_harga();
                foreach ($all_harga as &$item) {
                    $item['nilai_harga'] = number_format($item['nilai_harga'], 0, ',', '.');
                }
                return [
                    'data' => $all_harga,
                    'headers' => ['Nama Item', 'Harga', 'Terakhir Diupdate'],
                    'fields' => ['nama_harga', 'nilai_harga', 'updated_at'],
                    'primary_key' => 'id_harga',
                    'display_field' => 'nama_harga'
                ];
                
            default:
                return ['data' => [], 'headers' => [], 'fields' => [], 'primary_key' => 'id', 'display_field' => 'name'];
        }
    }

    public function delete_data($kategori = null, $id = null) {
        if (!$kategori || !$id) {
            $this->session->set_flashdata('error', 'Parameter tidak lengkap');
            redirect('Dashboard_new/index');
            return;
        }

        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $kategori_map = [
            'agen' => 'Agen',
            'kemitraan' => 'Kemitraan', 
            'subagen' => 'Sub Agen',
            'peternak' => 'Peternak',
            'farm' => 'Farm',
            'lokasibaru' => 'Lokasi Baru',
            'pakan' => 'Pakan',
            'strain' => 'Strain',
            'target' => 'Target',
            'harga' => 'Harga'
        ];

        if (!isset($kategori_map[$kategori])) {
            $this->session->set_flashdata('error', 'Kategori tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_name = $kategori_map[$kategori];
        
        $existing_data = $this->_get_existing_data($kategori_name, $id, $user);
        if (!$existing_data) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('Admin_Controller/list_data/' . $kategori);
            return;
        }

        $result = $this->_delete_data($kategori_name, $id, $user, $existing_data);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        
        redirect('Admin_Controller/list_data/' . $kategori);
    }

    private function _delete_data($kategori, $id, $user, $existing_data) {
        $is_admin = (isset($user['group_user']) && $user['group_user'] === 'administrator');
        
        switch($kategori) {
            case 'Agen':
                $sub_area_id = $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'];
                $this->_delete_options('master_agen', $existing_data['nama_agen'], $sub_area_id);
                
                $this->db->where('master_agen_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_agen');
                return $this->db->affected_rows() > 0;
                
            case 'Kemitraan':
                $sub_area_id = $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'];
                $this->_delete_options('master_kemitraan', $existing_data['nama_kantor_kemitraan'], $sub_area_id);
                
                $this->db->where('master_kemitraan_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_kemitraan');
                return $this->db->affected_rows() > 0;
                
            case 'Sub Agen':
                $sub_area_id = $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'];
                $this->_delete_options('master_subagen', $existing_data['nama_subagen'], $sub_area_id);
                
                $this->db->where('subagen_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_subagen');
                return $this->db->affected_rows() > 0;
                
            case 'Peternak':
                $sub_area_id = $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'];
                $this->_delete_options('master_peternak', $existing_data['nama_peternak'], $sub_area_id);
                
                $this->db->where('master_peternak_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_peternak');
                return $this->db->affected_rows() > 0;
                
            case 'Farm':
                $sub_area_id = $is_admin ? $existing_data['master_sub_area_id'] : $user['master_sub_area_id'];
                $this->_delete_farm_options($existing_data, $sub_area_id);
                
                $this->db->where('master_farm_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_farm');
                return $this->db->affected_rows() > 0;
                
            case 'Lokasi Baru':
                $sub_area_id = $is_admin ? $existing_data['master_lokasi_lainnya_id'] : $user['master_sub_area_id'];
                $this->_delete_options('master_lokasi_lainnya', $existing_data['nama_lokasi'], $sub_area_id);
                
                $this->db->where('master_lokasi_lainnya_id', $id);
                if (!$is_admin) {
                    $this->db->where('master_sub_area_id', $user['master_sub_area_id']);
                }
                $this->db->delete('master_lokasi_lainnya');
                return $this->db->affected_rows() > 0;
                
            case 'Pakan':
                $this->_delete_pakan_options($existing_data);
                $this->db->where('master_pakan_id', $id)->delete('master_pakan');
                return $this->db->affected_rows() > 0;
                
            case 'Strain':
                $this->_delete_strain_options($existing_data);
                $this->db->where('master_strain_id', $id)->delete('master_strain');
                return $this->db->affected_rows() > 0;

        case 'Target':
            $nama_tabel_histori = 'history_target'; 
            $tanggal_sekarang = date('Y-m-d'); 
            $this->db->trans_start();
            $this->db->where('id_target', $id);
            $this->db->where('end_date', '9999-12-31'); 
            $this->db->update($nama_tabel_histori, ['end_date' => $tanggal_sekarang]);
            $this->db->where('id_target', $id);
            $this->db->update('master_target', ['is_active' => 0]);
            $this->db->trans_complete();
            return $this->db->trans_status();

            case 'Harga':
                $result = $this->M_master_harga->delete_harga($id);
                if (!$result) {
                    log_message('error', 'Failed to delete harga. ID: ' . $id);
                }
                return $result;
                
            default:
                log_message('error', 'Unknown category in _delete_data: ' . $kategori);
                return false;
        }
    }

    private function _delete_options($page, $option_text, $master_sub_area_id) {
        $questions = $this->db->select('questions_id')
                             ->from('questions')
                             ->where('page', $page)
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            $this->db->where([
                'questions_id' => $question['questions_id'],
                'option_text' => $option_text,
                'master_sub_area_id' => $master_sub_area_id
            ])
            ->delete('options');
        }
    }

    private function _delete_farm_options($existing_data, $master_sub_area_id) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_farm')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                     
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_farm') {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_farm'],
                    'master_sub_area_id' => $master_sub_area_id
                ])
                ->delete('options');
            }
        }
    }

    private function _delete_pakan_options($existing_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_pakan')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                            
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_pakan' && !empty($existing_data['nama_pakan'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_pakan']
                ])
                ->delete('options');
            }
        }
    }

    private function _delete_strain_options($existing_data) {
        $questions = $this->db->select('questions_id, field_name')
                             ->from('questions')
                             ->where('page', 'master_strain')
                             ->where('add_to_options', 1)
                             ->get()
                             ->result_array();
                        
        foreach ($questions as $question) {
            if ($question['field_name'] === 'nama_strain' && !empty($existing_data['nama_strain'])) {
                $this->db->where([
                    'questions_id' => $question['questions_id'],
                    'option_text' => $existing_data['nama_strain']
                ])
                ->delete('options');
            }
        }
    }

    

    public function index() {
        redirect('Dashboard_new/index');
    }
    
}
