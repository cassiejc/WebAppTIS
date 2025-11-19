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
        // Cek Izin Akses (Sudah ada dari sebelumnya)
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        $user_group = isset($user['group_user']) ? $user['group_user'] : '';
        
        $allowed_groups = ['administrator', 'surveyor', 'koordinator'];
        if (!in_array($user_group, $allowed_groups)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk mengubah data farm.');
            redirect('Admin_Controller/list_data/farm');
            return;
        }

        $existing_data = $this->M_master_farm->get_farm_by_id($id);
        if (empty($existing_data)) {
            show_404();
        }

        $questions = $this->M_Questions->get_questions_by_page('master_farm_edit');

        // --- AWAL MODIFIKASI: Kontrol Akses Field ---
        $is_admin = ($user_group === 'administrator');

        // Jika BUKAN admin, buat field selain kapasitas & tanggal menjadi readonly
        if (!$is_admin) {
            // Tentukan field apa saja yang boleh diedit oleh non-admin
            $allowed_edit_fields = ['kapasitas_farm', 'start_date']; 
            
            $temp_questions = [];
            foreach ($questions as $q) {
                // Jika field TIDAK diizinkan untuk diedit oleh non-admin
                if (!in_array($q['field_name'], $allowed_edit_fields)) {
                    $q['type'] = 'text_readonly'; // Ubah tipenya jadi readonly
                }
                $temp_questions[] = $q;
            }
            $questions = $temp_questions; // Timpa $questions asli dengan yang sudah diubah
        }
        // --- AKHIR MODIFIKASI ---
        
        // Proses options (harus dipanggil setelah $questions dimodifikasi)
        $this->_process_options($questions, 'Farm', $user); 
        
        $data = [
            'title'             => 'CP APPS',
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
        $user_group = isset($user['group_user']) ? $user['group_user'] : '';
        
        $allowed_groups = ['administrator', 'surveyor', 'koordinator'];
        if (!in_array($user_group, $allowed_groups)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki hak akses untuk memperbarui data farm.');
            redirect('Admin_Controller/list_data/farm');
            return;
        }
        
        $is_admin = ($user_group === 'administrator');

        $questions = $this->M_Questions->get_questions_by_page('master_farm_edit');
        $update_data = [];
        foreach($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            
            if ($field == 'kapasitas_farm') {
                $jawaban = str_replace(',', '', $jawaban);
            }
            
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
                // --- PERBAIKAN: ambil nama farm dari $currentFarm jika admin tidak mengedit ---
                'nama_farm'      => $is_admin ? $update_data['nama_farm'] : $currentFarm['nama_farm'],
                'kapasitas'      => $new_capacity,
                'start_date'     => $start_date_str,
                'end_date'       => '9999-12-31'
            ];
            $this->M_master_farm->add_new_capacity_history($new_history_data);
        }
        
        
        // Semua user yang diizinkan (admin, surveyor, koor) BISA update kapasitas
        $data_farm = [
            'kapasitas_farm'     => $new_capacity,
        ];

        // HANYA ADMIN yang bisa update data master lainnya
        if ($is_admin) {
            
            // Ambil ID peternak jika yang di-pass adalah nama
            if (!empty($update_data['master_peternak_id']) && !is_numeric($update_data['master_peternak_id'])) {
                 $peternak = $this->db->select('master_peternak_id')
                                      ->from('master_peternak')
                                      ->where('nama_peternak', $update_data['master_peternak_id'])
                                      ->get()
                                      ->row();
                $update_data['master_peternak_id'] = $peternak ? $peternak->master_peternak_id : null;
            }

            // Tambahkan field lain ke $data_farm
            $data_farm['nama_farm']            = $update_data['nama_farm'];
            $data_farm['master_peternak_id']   = $update_data['master_peternak_id'];
            $data_farm['tipe_ternak']          = $update_data['tipe_ternak'];
            $data_farm['alamat_farm']          = $update_data['alamat_farm'];
            $data_farm['master_area_id']       = $update_data['master_area_id']; // Ini sudah ID dari dropdown/readonly
            $data_farm['master_sub_area_id']   = $update_data['master_sub_area_id']; // Ini sudah ID
            $data_farm['vip_farm']             = $update_data['vip_farm'];
        }
        
        $this->M_master_farm->update_farm_data($master_farm_id, $data_farm);
        
        // Logika update options HANYA boleh jalan jika admin yang mengubah nama
        if ($is_admin && isset($currentFarm['nama_farm']) && isset($update_data['nama_farm'])) {
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
            
            if ($user_group === 'surveyor' && $capacityHasChanged) {
                
                // Ambil nama surveyor
                $surveyor_name = isset($user['nama_user']) ? $user['nama_user'] : (isset($user['username']) ? $user['username'] : 'Surveyor');
                
                // Ambil nama farm dari data sebelum diubah
                $farm_name = $currentFarm['nama_farm'];
                
                // Format angka dan tanggal agar mudah dibaca
                $old_capacity_formatted = number_format($currentFarm['kapasitas_farm']);
                $new_capacity_formatted = number_format($new_capacity);
                $effective_date_formatted = date('d F Y', strtotime($start_date_str));

                //pesan notifikasi
                $message = "*Notifikasi Perubahan Kapasitas Farm*\n\n" .
                           "Surveyor: *" . $surveyor_name . "*\n" .
                           "Nama Farm: *" . $farm_name . "*\n\n" .
                           "Kapasitas telah diubah:\n" .
                           "Lama: " . $old_capacity_formatted . " ekor\n" .
                           "Baru: *" . $new_capacity_formatted . " ekor*\n\n" .
                           "Tanggal Efektif: " . $effective_date_formatted;

                $this->send_wa_notification($message);
            }
        }

        redirect('Admin_Controller/list_data/farm');
    }


    private function send_wa_notification($message)
    {
        $url = "https://cpipga.com/API_WA/inputPesan";
        
        // $target_whatsapp = "120363399977253659"; 
        $target_whatsapp = "6285259185063-1559451445"; 
       
        $api_token = "HEkUJoTGZuSxmafBF5Is3o96saNLFK";

        $data = [
            "to" => $target_whatsapp,
            "msg" => $message,
            "is_group" => 1,
            "attachment" => "",
            "priority" => 10
        ];

        $headers = [
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Accept: /",
            "Accept-Encoding: gzip, deflate, br",
            "Connection: keep-alive",
            "Token: " . $api_token
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $err = curl_error($ch); 
        
        if ($response === false || $err) {
            log_message('error', 'cURL Error (Custom WA API): ' . $err);
        } else {
            log_message('debug', 'Custom WA API Response: ' . $response);
        }
        
        curl_close($ch);
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
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->update_pakan($id);
        } else {
            $this->edit_pakan($id);
        }
        // $this->_handle_update_form('Pakan', 'master_pakan', $id);
    }

    public function edit_pakan($id)
    {
        $existing_data = $this->M_master_pakan->get_pakan_by_id($id);

        if (empty($existing_data)) {
            show_404();
        }

        $questions = $this->M_Questions->get_questions_by_page('master_pakan_edit');
        $this->_process_options($questions, 'Pakan', null);

        $data = [
            'title'             => 'CP APPS',
            'page_title'        => 'Edit Pakan',
            'kategori_selected' => 'Pakan',
            'edit_id'           => $id,
            'existing_data'     => $existing_data,
            'questions_kategori'=> $questions,
            'form_action'       => site_url('Admin_Controller/pakan/' . $id)
        ];

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function update_pakan($id) {
        if (!$id) {
            $this->session->set_flashdata('error', 'ID Pakan tidak valid.');
            redirect('Admin_Controller/list_data/pakan');
            return;
        }
        $existing_data = $this->M_master_pakan->get_pakan_by_id($id);
        $questions = $this->M_Questions->get_questions_by_page('master_pakan_edit');
        $update_data = [];
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);
            $update_data[$field] = $jawaban;
        }
        $this->db->trans_start();
        $this->M_master_pakan->update_pakan_data($id, $update_data);
        if (isset($existing_data['nama_pakan']) && isset($update_data['nama_pakan']) &&
            $existing_data['nama_pakan'] != $update_data['nama_pakan'])
        {
        $this->_update_pakan_options($existing_data, $update_data);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal memperbarui data pakan.');
        } else {
            $this->session->set_flashdata('success', 'Data pakan berhasil diperbarui!');
        }
        redirect('Admin_Controller/list_data/pakan');
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
            'title' => 'CP APPS',
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

        // 1. Ambil kedua nilai baru
        $new_target_value = $update_data['target'];
        $new_vip_target_value = $update_data['vip_target']; // <-- TAMBAHKAN INI
        $start_date_str = $update_data['start_date'];

        $currentTarget = $this->M_target->get_target_by_id($id_target);
        $current_user_id = $currentTarget['id_user'];

        // 2. Cek apakah SALAH SATU target berubah
        $targetHasChanged = ($currentTarget['target'] != $new_target_value);
        $vipTargetHasChanged = ($currentTarget['vip_target'] != $new_vip_target_value); // <-- TAMBAHKAN INI
        $anyTargetHasChanged = ($targetHasChanged || $vipTargetHasChanged); // <-- GUNAKAN INI

        $this->db->trans_start();

        // Gunakan $anyTargetHasChanged
        if ($anyTargetHasChanged) { 
            $end_date_str = date('Y-m-d', strtotime('-1 day', strtotime($start_date_str)));
            $this->M_target->close_current_target_history($current_user_id, $end_date_str);

            // 3. Tambahkan vip_target ke data riwayat baru
            $new_history_data = [
                'id_target'    => $id_target,
                'id_user'      => $current_user_id,
                'target'       => $new_target_value,
                'vip_target'   => $new_vip_target_value, // <-- TAMBAKAN INI
                'start_date'   => $start_date_str,
                'end_date'     => '9999-12-31'
            ];
            $this->M_target->add_new_target_history($new_history_data);
        }

        // 4. Tambahkan vip_target ke data master yang akan di-update
        $data_target = [
            'target' => $new_target_value,
            'vip_target' => $new_vip_target_value // <-- TAMBAHKAN INI
        ];
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
        $data['title'] = 'CP APPS';
        $data['page_title'] = 'Tambah Target Baru';

        $this->load->view('templates/dash_h', $data);
        $this->load->view('add_target_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }
    
    public function create_target_action()
    {
        $this->form_validation->set_rules('id_user', 'User', 'required|is_unique[master_target.id_user]');
        $this->form_validation->set_rules('target', 'Nilai Target', 'required|numeric');
        $this->form_validation->set_rules('vip_target', 'Nilai VIP Target', 'required|numeric'); // <-- TAMBAHKAN VALIDASI
        $this->form_validation->set_rules('start_date', 'Tanggal Mulai', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('Admin_Controller/add_target');
        } else {
            // 2. Tambahkan vip_target ke data master
            $data_target = [
                'id_user' => $this->input->post('id_user'),
                'target'  => $this->input->post('target'),
                'vip_target' => $this->input->post('vip_target') // <-- TAMBAHKAN INI
            ];

            $this->db->trans_start();
            
            // Pastikan M_target->create_target() mengembalikan insert_id()
            $new_target_id = $this->M_target->create_target($data_target); 

            if ($new_target_id && method_exists($this->M_target, 'add_new_target_history')) {
                // 3. Tambahkan vip_target ke data riwayat
                $history_data = [
                    'id_target'  => $new_target_id,
                    'id_user'    => $this->input->post('id_user'),
                    'target'     => $this->input->post('target'),
                    'vip_target' => $this->input->post('vip_target'), // <-- TAMBAHKAN INI
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
            'title'             => 'CP APPS',
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

    /* --- [TAMBAHKAN 2 FUNGSI INI DI Admin_Controller.php] --- */

    /**
     * Menampilkan halaman form untuk menambah user baru.
     */
    public function add_user()
    {
        $data = [
            'title'               => 'CP APPS',
            'page_title'          => 'Tambah User Baru',
            'kategori_selected'   => 'User', // Penting untuk view
            // Pastikan 'master_user_tambah' ada di tabel questions Anda
            'questions_kategori'  => $this->M_Questions->get_questions_by_page('master_user_tambah'), 
            'form_action'         => site_url('Admin_Controller/create_user_action')
        ];
        
        // Panggil _process_options untuk mengisi dropdown Area/Sub-Area
        $this->_process_options($data['questions_kategori'], 'User', null);

        $this->load->view('templates/dash_h', $data);
        // Kita gunakan view yang sama dengan edit, tapi tanpa 'existing_data'
        $this->load->view('form_admin_update_view', $data); 
        $this->load->view('templates/dash_f', $data);
    }

    public function create_user_action()
    {
        // Pastikan Anda sudah membuat 'master_user_tambah' di tabel questions
        $questions = $this->M_Questions->get_questions_by_page('master_user_tambah');
        
        $insert_data = []; // Data untuk tabel z_master_user
        $start_date = date('Y-m-d'); // Default start date hari ini
        
        foreach ($questions as $q) {
            $field = $q['field_name'];
            $input_name = 'q' . $q['questions_id'];
            $jawaban = $this->input->post($input_name);

            // --- [LOGIKA DIPERBAIKI] ---
            if ($field == 'start_date') {
                if (!empty($jawaban)) {
                    $start_date = $jawaban; // Ambil start_date jika diisi
                }
            } 
            elseif ($field == 'master_sub_area_id') {
                 // Pastikan NULL jika kosong, bukan string kosong
                 $insert_data[$field] = empty($jawaban) ? NULL : $jawaban;
            }
            else {
                // Semua field lain (username, pass, area_id, caption, active)
                // sekarang masuk ke $insert_data
                $insert_data[$field] = $jawaban;
            }
            // --- [AKHIR PERBAIKAN] ---
        }

        // Validasi dasar
        if (empty($insert_data['username']) || empty($insert_data['password'])) {
            $this->session->set_flashdata('error', 'Username dan Password wajib diisi.');
            redirect('Admin_Controller/add_user');
            return;
        }
        if (empty($insert_data['master_area_id'])) {
             $this->session->set_flashdata('error', 'Area wajib diisi.');
             redirect('Admin_Controller/add_user');
             return;
        }

        // Mulai transaksi database
        $this->db->trans_start();

        // 1. Buat user baru (Model akan hash password)
        // $insert_data sekarang sudah lengkap (termasuk area dan sub_area)
        $new_user_id = $this->M_edit_user->create_user($insert_data);

        if ($new_user_id) {
            // 2. Buat history area pertama untuk user baru
            $new_history_data = [
                'id_user'            => $new_user_id,
                'master_area_id'     => $insert_data['master_area_id'],     // <-- Ambil dari $insert_data
                'master_sub_area_id' => $insert_data['master_sub_area_id'], // <-- Ambil dari $insert_data
                'start_date'         => $start_date, // Gunakan variabel start_date
                'end_date'           => '9999-12-31'
            ];
            
            // Asumsi fungsi 'add_new_area_history' sudah ada di M_edit_user
            $this->M_edit_user->add_new_area_history($new_history_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal membuat user baru.');
        } else {
            $this->session->set_flashdata('success', 'User baru berhasil dibuat!');
        }

        redirect('Admin_Controller/list_data/user');
    }

    /**
     * Fungsi router, tidak ada perubahan.
     */
    public function kontributorharga($id = null) {
        // Fungsi ini hanya sebagai penghubung dari URL ke fungsi edit yang sebenarnya
        if (!$id) {
            redirect('Admin_Controller/list_data/kontributorharga');
            return;
        }
        $this->edit_kontributor_harga($id);
    }

    /**
     * FUNGSI YANG DIPERBARUI
     * Menampilkan form edit dengan daftar checkbox jenis harga.
     */
    public function edit_kontributor_harga($id_user)
    {
        $existing_user_data = $this->M_edit_user->get_user_by_id($id_user);
        if (empty($existing_user_data)) {
            show_404();
        }
        // 1. Definisikan semua jenis harga yang bisa dipilih.
        // Kunci array (cth: 'harga_jual_telur_layer') HARUS SAMA PERSIS
        // dengan 'jenis_harga_key' di database & M_master_harga.
        $semua_jenis_harga = [
            'harga_jual_telur_layer' => 'Harga Jual Telur Layer',
            'harga_jagung'           => 'Harga Jagung',
            'harga_katul'            => 'Harga Katul',
            'harga_afkir'            => 'Harga Afkir',
            'harga_telur_puyuh'      => 'Harga Telur Puyuh',
            'harga_telur_bebek'      => 'Harga Telur Bebek',
            'harga_bebek_pedaging'   => 'Harga Bebek Pedaging',
            'harga_live_bird'        => 'Harga Live Bird'
        ];
        
        // Urutkan berdasarkan nama (label) agar rapi di view
        asort($semua_jenis_harga);

        // 2. Ambil data kontribusi yang sedang aktif untuk user ini dari model BARU.
        $kontribusi_terpilih = $this->M_edit_user->get_user_active_contributions($id_user);

        // 3. "Suntikkan" data ini ke array $existing_user_data agar bisa dibaca view
        //    View akan mencari $existing_data['kontribusi_terpilih']
        $existing_user_data['kontribusi_terpilih'] = $kontribusi_terpilih;

        $data = [
            'title'               => 'CP APPS',
            'page_title'          => 'Edit Status Kontributor: ' . $existing_user_data['username'],
            'kategori_selected'   => 'Kontributor Harga', // Ini penting untuk view
            'edit_id'             => $id_user,
            'existing_data'       => $existing_user_data, // Sudah berisi 'kontribusi_terpilih'
            
            // Variabel BARU untuk view
            'semua_jenis_harga'   => $semua_jenis_harga,
            
            // Variabel LAMA (dikosongkan agar view tidak error)
            'questions_kategori'  => [], // View akan melewatinya karena $kategori_selected == 'Kontributor Harga'
            
            'form_action'         => site_url('Admin_Controller/update_kontributor_harga')
        ];

        $this->load->view('templates/dash_h', $data);
        $this->load->view('form_admin_update_view', $data); // View ini sekarang sudah punya logika if
        $this->load->view('templates/dash_f', $data);
    }

    /**
     * FUNGSI YANG DIPERBARUI
     * Memproses update checkbox kontributor.
     */
    public function update_kontributor_harga()
    {
        $id_user = $this->input->post('edit_id');
        
        // 1. Ambil array dari checkbox 'kontribusi_harga[]' yang dikirim view.
        //    Jika tidak ada satupun yang dicentang, $this->input->post akan NULL.
        //    Kita ubah jadi array kosong agar logic-nya aman.
        $new_selected_keys = $this->input->post('kontribusi_harga') ?? []; 

        // 2. Panggil fungsi model BARU.
        //    Fungsi ini sudah berisi logic (trans_start, array_diff, update, insert, trans_complete)
        //    dan akan mengembalikan true/false.
        $success = $this->M_edit_user->update_user_contributions($id_user, $new_selected_keys);

        // 3. Set flash message berdasarkan hasil dari model
        if ($success) {
            $this->session->set_flashdata('success', 'Status kontributor berhasil diperbarui!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui status kontributor.');
        }

        redirect('Admin_Controller/list_data/kontributorharga');
    }


    public function add_harga()
    {
        $data = [
            'title' => 'CP APPS',
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
    
    public function hitung_ulang_harga_telur_layer($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            // Tentukan periode waktu saat ini untuk pengujian
            $tahun_sekarang = date('Y'); // Misal: 2025
            $bulan_sekarang = date('m'); // Misal: 10 (untuk Oktober)

            // 1. Jalankan proses harian (ini akan mengupdate nilai di master_harga)
            $this->M_master_harga->proses_rata_rata_harian_telur_layer($id_harga);

            // 2. Langsung jalankan proses bulanan untuk bulan ini
            $this->M_master_harga->hitung_rata_rata_bulanan_telur_layer($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk tahun ini
            $this->M_master_harga->hitung_rata_rata_tahunan_telur_layer($id_harga, $tahun_sekarang);
            
            // Jika semua berjalan tanpa error, set pesan sukses
            $this->session->set_flashdata('success', 'Harga rata-rata Harian, Bulanan, dan Tahunan berhasil diproses untuk pengujian!');

        } catch (Exception $e) {
            // Menangkap jika ada error tak terduga
            $this->session->set_flashdata('error', 'Terjadi error saat memproses harga rata-rata: ' . $e->getMessage());
        }

        // Arahkan kembali ke halaman daftar harga
        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_harga_jagung($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk jagung
            $this->M_master_harga->proses_rata_rata_harian_jagung($id_harga);

            // 2. Langsung jalankan proses bulanan untuk jagung
            $this->M_master_harga->hitung_rata_rata_bulanan_jagung($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk jagung
            $this->M_master_harga->hitung_rata_rata_tahunan_jagung($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'Harga rata-rata Jagung (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_harga_katul($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk katul
            $this->M_master_harga->proses_rata_rata_harian_katul($id_harga);

            // 2. Langsung jalankan proses bulanan untuk katul
            $this->M_master_harga->hitung_rata_rata_bulanan_katul($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk katul
            $this->M_master_harga->hitung_rata_rata_tahunan_katul($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'Harga rata-rata Katul (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_harga_afkir($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk afkir
            $this->M_master_harga->proses_rata_rata_harian_afkir($id_harga);

            // 2. Langsung jalankan proses bulanan untuk afkir
            $this->M_master_harga->hitung_rata_rata_bulanan_afkir($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk afkir
            $this->M_master_harga->hitung_rata_rata_tahunan_afkir($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'Harga rata-rata Afkir (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    // Pemicu untuk Telur Puyuh
    public function hitung_ulang_harga_telur_puyuh($id_harga = null) {
        if ($id_harga) {
            $this->M_master_harga->proses_rata_rata_harian_telur_puyuh($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_telur_puyuh($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_telur_puyuh($id_harga, date('Y'));
            $this->session->set_flashdata('success', 'Harga rata-rata Telur Puyuh berhasil diproses!');
        }
        redirect('Admin_Controller/list_data/harga');
    }

    // Pemicu untuk Telur Bebek
    public function hitung_ulang_harga_telur_bebek($id_harga = null) {
        if ($id_harga) {
            $this->M_master_harga->proses_rata_rata_harian_telur_bebek($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_telur_bebek($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_telur_bebek($id_harga, date('Y'));
            $this->session->set_flashdata('success', 'Harga rata-rata Telur Bebek berhasil diproses!');
        }
        redirect('Admin_Controller/list_data/harga');
    }

    // Pemicu untuk Bebek Pedaging
    public function hitung_ulang_harga_bebek_pedaging($id_harga = null) {
        if ($id_harga) {
            $this->M_master_harga->proses_rata_rata_harian_bebek_pedaging($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_bebek_pedaging($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_bebek_pedaging($id_harga, date('Y'));
            $this->session->set_flashdata('success', 'Harga rata-rata Bebek Pedaging berhasil diproses!');
        }
        redirect('Admin_Controller/list_data/harga');
    }

    // Pemicu untuk Live Bird
    public function hitung_ulang_harga_live_bird($id_harga = null) {
        if ($id_harga) {
            $this->M_master_harga->proses_rata_rata_harian_live_bird($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_live_bird($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_live_bird($id_harga, date('Y'));
            $this->session->set_flashdata('success', 'Harga rata-rata Live Bird berhasil diproses!');
        }
        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_harga_konsentrat_layer($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk harga konsentrat jadi
            $this->M_master_harga->proses_harga_konsentrat_layer_harian($id_harga);

            // 2. Langsung jalankan proses bulanan untuk harga konsentrat jadi
            $this->M_master_harga->hitung_rata_rata_bulanan_konsentrat_layer($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk harga konsentrat jadi
            $this->M_master_harga->hitung_rata_rata_tahunan_konsentrat_layer($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'Harga Konsentrat Layer (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_hpp_konsentrat_layer($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk HPP
            $this->M_master_harga->proses_hpp_konsentrat_layer_harian($id_harga);

            // 2. Langsung jalankan proses bulanan untuk HPP
            $this->M_master_harga->hitung_rata_rata_bulanan_hpp_konsentrat_layer($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk HPP
            $this->M_master_harga->hitung_rata_rata_tahunan_hpp_konsentrat_layer($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'HPP Konsentrat Layer (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_hpp_komplit_layer($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $tahun_sekarang = date('Y');
            $bulan_sekarang = date('m');

            // 1. Jalankan proses harian untuk HPP
            $this->M_master_harga->proses_hpp_komplit_layer_harian($id_harga);

            // 2. Langsung jalankan proses bulanan untuk HPP
            $this->M_master_harga->hitung_rata_rata_bulanan_hpp_komplit_layer($id_harga, $tahun_sekarang, $bulan_sekarang);

            // 3. Langsung jalankan proses tahunan untuk HPP
            $this->M_master_harga->hitung_rata_rata_tahunan_hpp_komplit_layer($id_harga, $tahun_sekarang);
            
            $this->session->set_flashdata('success', 'HPP Komplit Layer (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_cost_komplit_broiler($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            $this->M_master_harga->proses_harga_komplit_broiler_harian($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_cost_komplit_broiler($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_cost_komplit_broiler($id_harga, date('Y'));

            $this->session->set_flashdata('success', 'Average Cost Komplit Broiler (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_hpp_broiler($id_harga = null)
    {
        if (!$id_harga) {
            $this->session->set_flashdata('error', 'ID Harga tidak valid.');
            redirect('Admin_Controller/list_data/harga');
            return;
        }

        try {
            // Jalankan proses harian, bulanan, dan tahunan
            $this->M_master_harga->proses_hpp_broiler_harian($id_harga);
            $this->M_master_harga->hitung_rata_rata_bulanan_hpp_broiler($id_harga, date('Y'), date('m'));
            $this->M_master_harga->hitung_rata_rata_tahunan_hpp_broiler($id_harga, date('Y'));

            $this->session->set_flashdata('success', 'Average HPP Broiler (Harian, Bulanan, Tahunan) berhasil diproses!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error: ' . $e->getMessage());
        }

        redirect('Admin_Controller/list_data/harga');
    }

    public function hitung_ulang_semua_harga()
    {
        try {
            // 1. Pastikan M_master_harga sudah di-load (biasanya di construct)
            // $this->load->model('M_master_harga'); 

            // 2. Panggil fungsi tunggal dari model
            $this->M_master_harga->recalculate_all_prices(); 

            // 3. Set flashdata seperti biasa
            $this->session->set_flashdata('success', 'SEMUA harga rata-rata (Harian, Bulanan, Tahunan) berhasil diproses ulang!');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi error saat memproses semua harga: ' . $e->getMessage());
        }
        
        // 4. Redirect seperti biasa
        redirect('Admin_Controller/list_data/harga');
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
            'kategori_selected' => $kategori,
            'questions_kategori' => [],
            'title' => 'CP APPS',
            'page_title' => 'Edit ' . $kategori,
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
                $user_group = isset($user['group_user']) ? $user['group_user'] : '';

                if ($is_admin) {
                    // Admin: no filter
                } elseif ($user_group === 'koordinator') {
                    // Koordinator: Cek by Area
                    $this->db->where('master_area_id', $user['master_area_id']);
                } else {
                    // Surveyor: Cek by Sub Area
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
                            
                            $user_group = isset($user['group_user']) ? $user['group_user'] : '';

                            if ($is_admin) {
                                // Admin pass
                            } elseif ($user_group === 'koordinator') {
                                // Koordinator melihat opsi berdasarkan Area
                                $this->db->where('o.master_area_id', $user['master_area_id']);
                            } else {
                                // Surveyor melihat opsi berdasarkan Sub Area
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
                        elseif ($q['field_name'] == 'master_area_id') {
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

            // case 'Harga':
            //     if (isset($update_data['nilai_harga'])) {
            //         $update_data['nilai_harga'] = preg_replace('/[^\d]/', '', $update_data['nilai_harga']);
            //         $update_data['nilai_harga'] = (int)$update_data['nilai_harga'];
            //     }
                
            //     $this->M_master_harga->update_harga($id, $update_data);
            //     break;

            // case 'Harga':
            // if (isset($update_data['nilai_harga'])) {
            //     $update_data['nilai_harga'] = preg_replace('/[^\d]/', '', $update_data['nilai_harga']);
            //     $update_data['nilai_harga'] = (int)$update_data['nilai_harga'];
            // }
            
            // // 1. Lakukan update utama ke tabel master_harga
            // $update_status = $this->M_master_harga->update_harga($id, $update_data);

            // // 2. LOGIKA BARU: Cek apakah nama harga MENGANDUNG kata "DOC" (lebih aman)
            // // stripos() tidak mempermasalahkan huruf besar/kecil
            // if (isset($existing_data['nama_harga']) && stripos($existing_data['nama_harga'], 'DOC') !== false) {
                
            //     // Tambahkan Log untuk Debugging. Pesan ini akan muncul di file log CodeIgniter
            //     log_message('debug', 'Nama harga DOC terdeteksi: "' . $existing_data['nama_harga'] . '". Memulai proses simpan ke tabel harian.');

            //     if ($update_status) {
            //         // 3. Jalankan proses kalkulasi otomatis
            //         $this->M_master_harga->proses_rata_rata_harian_doc($id);
            //         $this->M_master_harga->hitung_rata_rata_bulanan_doc($id, date('Y'), date('m'));
            //         $this->M_master_harga->hitung_rata_rata_tahunan_doc($id, date('Y'));

            //         // 4. Set pesan sukses yang lebih spesifik
            //         $this->session->set_flashdata('success', 'Harga DOC berhasil diupdate & data rata-rata harian/bulanan/tahunan telah diproses!');
                    
            //         redirect('Admin_Controller/list_data/harga');
            //     } else {
            //         log_message('error', 'Gagal mengupdate master_harga untuk DOC, proses harian dibatalkan.');
            //     }

            //     } elseif (isset($existing_data['nama_harga']) && $existing_data['nama_harga'] === 'Pakan Komplit Layer') {
                    
            //         log_message('debug', 'Nama harga "Pakan Komplit Layer" terdeteksi. Memulai proses simpan ke tabel harian.');

            //         if ($update_status) {
            //             // 4. Jalankan proses kalkulasi otomatis untuk Pakan Komplit Layer
            //             $this->M_master_harga->proses_rata_rata_harian_pakan_komplit_layer($id);
            //             $this->M_master_harga->hitung_rata_rata_bulanan_pakan_komplit_layer($id, date('Y'), date('m'));
            //             // $this->M_master_harga->hitung_rata_rata_tahunan_pakan_komplit_layer($id, date('Y'));

            //             // 5. Set pesan sukses
            //             $this->session->set_flashdata('success', 'Harga Pakan Komplit Layer berhasil diupdate & data rata-rata harian/bulanan/tahunan telah diproses!');
                        
            //             redirect('Admin_Controller/list_data/harga');
            //         } else {
            //             log_message('error', 'Gagal mengupdate master_harga untuk Pakan Komplit Layer, proses harian dibatalkan.');
            //         }

            // } else {
            //     // Log jika nama harga BUKAN DOC, untuk memastikan kondisi if berjalan benar
            //     if(isset($existing_data['nama_harga'])){
            //          log_message('debug', 'Nama harga yang diupdate bukan DOC: "' . $existing_data['nama_harga'] . '". Tidak ada proses otomatis dijalankan.');
            //     }
            // }

            case 'Harga':
                if (isset($update_data['nilai_harga'])) {
                    $update_data['nilai_harga'] = preg_replace('/[^\d]/', '', $update_data['nilai_harga']);
                    $update_data['nilai_harga'] = (int)$update_data['nilai_harga'];
                }
                
                // 1. Lakukan update utama ke tabel master_harga
                $update_status = $this->M_master_harga->update_harga($id, $update_data);
                
                $nama_harga = $existing_data['nama_harga'] ?? '';

                // --- [LOGIKA BARU YANG MENGGABUNGKAN KEDUANYA] ---
                
                // 2. Cek untuk DOC terlebih dahulu (menggunakan logika lama Anda)
                // Ini akan memanggil fungsi ..._doc() yang spesifik
                if ($update_status && isset($nama_harga) && stripos($nama_harga, 'DOC') !== false) {
                    
                    log_message('debug', "Nama harga DOC terdeteksi: '$nama_harga'. Menjalankan proses ...doc() spesifik.");
                    
                    // Memanggil fungsi DOC spesifik yang ada di M_master_harga.php
                    $this->M_master_harga->proses_rata_rata_harian_doc($id);
                    $this->M_master_harga->hitung_rata_rata_bulanan_doc($id, date('Y'), date('m'));
                    // $this->M_master_harga->hitung_rata_rata_tahunan_doc($id, date('Y'));

                    $this->session->set_flashdata('success', "Harga '$nama_harga' berhasil diupdate & data rata-rata harian/bulanan/tahunan telah diproses!");
                    redirect('Admin_Controller/list_data/harga');

                } else {
                    // 3. Jika BUKAN DOC, cek di map untuk harga manual lainnya
                    
                    // Definisikan map HANYA untuk item selain DOC
                    $manual_to_daily_map = [
                        // 'Average Harga DOC' DIHAPUS dari sini karena sudah ditangani di atas
                        'Pakan Komplit Layer'           => 'pakan_komplit_layer',
                        'Pakan Komplit Broiler'         => 'pakan_komplit_broiler',
                        'Harga Pakan Pre Stater'        => 'harga_pakan_pre_stater',
                        'Harga Pakan Stater'            => 'harga_pakan_stater',
                        'Harga Pakan Finisher'          => 'harga_pakan_finisher',
                        'Ongkos Kirim'                  => 'ongkos_kirim',
                        'Harga Pakan Konsentrat Layer'  => 'harga_pakan_konsentrat_layer'
                    ];

                    if ($update_status && array_key_exists($nama_harga, $manual_to_daily_map)) {
                        
                        $jenis_harga = $manual_to_daily_map[$nama_harga];
                        log_message('debug', "Harga manual '$nama_harga' terdeteksi. Menjalankan proses generic() dengan key '$jenis_harga'.");

                        // Panggil FUNGSI GENERIC BARU
                        $this->M_master_harga->proses_rata_rata_harian_manual($id, $jenis_harga);
                        $this->M_master_harga->hitung_rata_rata_bulanan_generic($id, $jenis_harga, date('Y'), date('m'));
                        // $this->M_master_harga->hitung_rata_rata_tahunan_generic($id, $jenis_harga, date('Y'));

                        $this->session->set_flashdata('success', "Harga '$nama_harga' berhasil diupdate & data rata-rata harian/bulanan/tahunan telah diproses!");
                        redirect('Admin_Controller/list_data/harga');
                    
                    } elseif ($update_status) {
                        // Harga yang diupdate tidak ada di map dan bukan DOC (misal: HPP, Rata2 Telur, dll)
                        log_message('debug', "Harga '$nama_harga' diupdate, tapi tidak ada di map manual-to-daily. Tidak ada proses otomatis dijalankan.");
                    } else {
                        log_message('error', "Gagal mengupdate master_harga untuk ID $id, proses harian dibatalkan.");
                    }
                }
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
            $this->db->where('questions_id', $question['questions_id']);
            $this->db->where('option_text', $old_text);

            // LOGIKA BARU: Hanya tambahkan filter area jika memang diperlukan untuk kategori tersebut
            if (in_array($page, ['master_subagen', 'master_kemitraan'])) {
                // Untuk Sub Agen & Kemitraan, filter berdasarkan master_area_id
                if ($master_area_id !== null) {
                    $this->db->where('master_area_id', $master_area_id);
                }
            } elseif (in_array($page, ['master_farm', 'master_lokasi_lainnya'])) {
                // Untuk Farm, Lokasi Baru, dll, gunakan master_sub_area_id
                if ($master_sub_area_id !== null) {
                    $this->db->where('master_sub_area_id', $master_sub_area_id);
                }
            }
            // Untuk 'master_agen' dan kategori global lainnya, tidak ada filter area yang ditambahkan.

            // Lakukan update
            $this->db->update('options', ['option_text' => $new_text]);
        }
        
        // foreach ($questions as $question) {
        //     $where_conditions = [
        //         'questions_id' => $question['questions_id'],
        //         'option_text' => $old_text,
        //         // 'master_sub_area_id' => $master_sub_area_id
        //     ];
            
        //     if ($master_area_id !== null) {
        //         $where_conditions['master_area_id'] = $master_area_id;
        //     }
            
        //     $this->db->where($where_conditions)->update('options', ['option_text' => $new_text]);
        // }
        
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
            'harga' => 'Harga',
            'kontributorharga' => 'Kontributor Harga'
        ];

        if (!isset($kategori_map[$kategori])) {
            $this->session->set_flashdata('error', 'Kategori tidak ditemukan');
            redirect('Dashboard_new/index');
            return;
        }

        $kategori_name = $kategori_map[$kategori];
        
        $data_list = $this->_get_data_list($kategori_name, $user);
        
        $data = [
            'title' => 'CP APPS',
            'page_title' => 'Daftar ' . $kategori_name,
            'kategori_selected' => $kategori_name,
            'data_list' => $data_list['data'],
            'table_headers' => $data_list['headers'],
            'table_fields' => $data_list['fields'],
            'primary_key' => $data_list['primary_key'],
            'display_field' => $data_list['display_field'],
            'user_group' => isset($user['group_user']) ? $user['group_user'] : ''
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
                // 1. Memperbarui SELECT untuk mengambil nama_area dan nama_sub_area (komentar dipindahkan)
                $this->db->select('
                    mf.*, 
                    mp.nama_peternak, 
                    ma.nama_area,
                    msa.nama_sub_area
                ');
                
                $this->db->from('master_farm mf');

                // 2. Menambahkan JOIN ke tabel-tabel yang relevan
                $this->db->join('master_peternak mp', 'mf.master_peternak_id = mp.master_peternak_id', 'left');
                $this->db->join('master_area ma', 'mf.master_area_id = ma.master_area_id', 'left');
                $this->db->join('master_sub_area msa', 'mf.master_sub_area_id = msa.master_sub_area_id', 'left');

                // Cek Group User
                $user_group = isset($user['group_user']) ? $user['group_user'] : '';

                if ($is_admin) {
                    // Admin: Tidak ada filter (lihat semua)
                } elseif ($user_group === 'koordinator') {
                    // Koordinator: Filter berdasarkan AREA (Area ID)
                    $this->db->where('mf.master_area_id', $user['master_area_id']);
                } else {
                    // Surveyor/Lainnya: Filter berdasarkan SUB AREA
                    $this->db->where('mf.master_sub_area_id', $user['master_sub_area_id']);
                }

                return [
                    'data' => $this->db->get()->result_array(),
                    'headers' => ['Nama Farm', 'Nama Peternak', 'Area', 'Sub Area', 'Tipe Ternak', 'Alamat', 'Kapasitas', 'VIP Farm'],
                    
                    // 3. Mengganti fields dari ID menjadi nama yang sesuai
                    'fields' => ['nama_farm', 'nama_peternak', 'nama_area', 'nama_sub_area', 'tipe_ternak', 'alamat_farm', 'kapasitas_farm', 'vip_farm'],
                    
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
                    'headers' => ['Nama Pakan', 'Tipe Ternak', 'Nama Pabrik'],
                    'fields' => ['nama_pakan', 'tipe_ternak', 'nama_pabrik'],
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
                    'headers' => ['Username', 'Target', 'Target VIP'],
                    'fields' => ['username', 'target', 'vip_target'],
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

            case 'Kontributor Harga':
                $all_users = $this->M_edit_user->get_all_users_with_selection_status();
                
                // Kita tambahkan kolom 'status' secara manual untuk ditampilkan di view
                foreach ($all_users as &$u) {
                    if ($u['histori_id'] !== NULL) {
                        $u['status'] = 'Aktif sejak ' . date('d-m-Y', strtotime($u['start_date']));
                    } else {
                        $u['status'] = 'Tidak Aktif';
                    }
                }

                return [
                    'data' => $all_users,
                    'headers' => ['Username', 'Status Kontributor'],
                    'fields' => ['username', 'status'],
                    'primary_key' => 'id_user',
                    'display_field' => 'username'
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
            'harga' => 'Harga',
            'user' => 'User'
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

        if (empty($questions)) {
            return;
        }
        $question_ids = array_column($questions, 'questions_id');
        $this->db->where_in('questions_id', $question_ids)
                 ->where('option_text', $option_text);

        // if (in_array($page, ['master_agen'])) {
        //     $token = $this->session->userdata('token');
        //     $user = $this->dash->getUserInfo($token)->row_array();

        // }

        if (in_array($page, ['master_subagen'])) {
            $token = $this->session->userdata('token');
            $user = $this->dash->getUserInfo($token)->row_array();
            $this->db->where('master_area_id', $user['master_area_id']);
        } 
        // 2. Cek kategori yang terikat pada SUB AREA
        elseif (in_array($page, ['master_farm', 'master_lokasi_lainnya'])) { 
            $this->db->where('master_sub_area_id', $master_sub_area_id);
        }

        $this->db->delete('options');
                     
        // foreach ($questions as $question) {
        //     $this->db->where([
        //         'questions_id' => $question['questions_id'],
        //         'option_text' => $option_text,
        //         'master_sub_area_id' => $master_sub_area_id
        //     ])
        //     ->delete('options');
        // }
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
        $pakan_name_to_delete = $existing_data['nama_pakan'];
        
        if (empty($pakan_name_to_delete)) {
            return;
        }

        $related_field_names = [
            'nama_pakan',                 
            'layer_pilihan_pakan_cp',     
            'layer_pilihan_pakan_lain'  
        ];

        $questions = $this->db->select('questions_id')
                              ->from('questions')
                              ->where_in('field_name', $related_field_names)
                              ->get()
                              ->result_array();

        if (empty($questions)) {
            return;
        }

        $question_ids_to_delete_from = array_column($questions, 'questions_id');

        $this->db->where('option_text', $pakan_name_to_delete);
        $this->db->where_in('questions_id', $question_ids_to_delete_from);
        $this->db->delete('options');
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

    // GANTI FUNGSI ajax_add_area DI Admin_Controller.php
public function ajax_add_area()
{
    // Set header JSON
    header('Content-Type: application/json');
    
    // Log untuk debugging
    log_message('debug', 'ajax_add_area called');
    
    // Validasi request AJAX (optional, bisa dikomentari untuk testing)
    // if (!$this->input->is_ajax_request()) {
    //     echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    //     return;
    // }

    $nama_area = trim($this->input->post('nama_area'));
    
    log_message('debug', 'Nama Area: ' . $nama_area);

    if (empty($nama_area)) {
        $response = [
            'status' => 'error',
            'message' => 'Nama area tidak boleh kosong.',
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        echo json_encode($response);
        return;
    }

    // Cek apakah area sudah ada (case insensitive)
    $existing = $this->db->select('master_area_id')
                         ->from('master_area')
                         ->where('LOWER(nama_area)', strtolower($nama_area))
                         ->get()
                         ->row();

    if ($existing) {
        $response = [
            'status' => 'error',
            'message' => 'Area "' . $nama_area . '" sudah ada di database.',
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        echo json_encode($response);
        return;
    }

    // Insert area baru ke tabel master_area
    $data_insert = [
        'nama_area' => $nama_area,
        'created_at' => date('Y-m-d H:i:s') // Jika ada kolom created_at
    ];
    
    // Hapus created_at jika kolom tidak ada
    $columns = $this->db->list_fields('master_area');
    if (!in_array('created_at', $columns)) {
        unset($data_insert['created_at']);
    }
    
    $this->db->insert('master_area', $data_insert);
    $new_area_id = $this->db->insert_id();
    
    log_message('debug', 'Insert ID: ' . $new_area_id);
    log_message('debug', 'DB Error: ' . $this->db->error()['message']);

    if ($new_area_id && $new_area_id > 0) {
        $response = [
            'status' => 'success',
            'message' => 'Area "' . $nama_area . '" berhasil ditambahkan!',
            'data' => [
                'id' => $new_area_id,
                'text' => $nama_area
            ],
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        log_message('debug', 'Success response: ' . json_encode($response));
    } else {
        $db_error = $this->db->error();
        $response = [
            'status' => 'error',
            'message' => 'Gagal menambahkan area baru. Error: ' . $db_error['message'],
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        log_message('error', 'DB Error: ' . json_encode($db_error));
    }

    echo json_encode($response);
    return;
}

// GANTI FUNGSI ajax_add_sub_area DI Admin_Controller.php
public function ajax_add_sub_area()
{
    // Set header JSON
    header('Content-Type: application/json');
    
    // Log untuk debugging
    log_message('debug', 'ajax_add_sub_area called');
    
    // Validasi request AJAX (optional, bisa dikomentari untuk testing)
    // if (!$this->input->is_ajax_request()) {
    //     echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    //     return;
    // }

    $nama_sub_area = trim($this->input->post('nama_sub_area'));
    $master_area_id = $this->input->post('master_area_id');
    
    log_message('debug', 'Nama Sub Area: ' . $nama_sub_area);
    log_message('debug', 'Master Area ID: ' . $master_area_id);

    if (empty($nama_sub_area)) {
        $response = [
            'status' => 'error',
            'message' => 'Nama sub-area tidak boleh kosong.',
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        echo json_encode($response);
        return;
    }

    if (empty($master_area_id) || !is_numeric($master_area_id)) {
        $response = [
            'status' => 'error',
            'message' => 'Pilih Area terlebih dahulu sebelum menambahkan Sub-Area.',
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        echo json_encode($response);
        return;
    }

    // Cek apakah sub-area sudah ada di area tersebut (case insensitive)
    $existing = $this->db->select('master_sub_area_id')
                         ->from('master_sub_area')
                         ->where('LOWER(nama_sub_area)', strtolower($nama_sub_area))
                         ->where('master_area_id', $master_area_id)
                         ->get()
                         ->row();

    if ($existing) {
        $response = [
            'status' => 'error',
            'message' => 'Sub-Area "' . $nama_sub_area . '" sudah ada di area ini.',
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        echo json_encode($response);
        return;
    }

    // Insert sub-area baru ke tabel master_sub_area
    $data_insert = [
        'nama_sub_area' => $nama_sub_area,
        'master_area_id' => $master_area_id,
        'created_at' => date('Y-m-d H:i:s') // Jika ada kolom created_at
    ];
    
    // Hapus created_at jika kolom tidak ada
    $columns = $this->db->list_fields('master_sub_area');
    if (!in_array('created_at', $columns)) {
        unset($data_insert['created_at']);
    }
    
    $this->db->insert('master_sub_area', $data_insert);
    $new_sub_area_id = $this->db->insert_id();
    
    log_message('debug', 'Insert Sub Area ID: ' . $new_sub_area_id);
    log_message('debug', 'DB Error: ' . $this->db->error()['message']);

    if ($new_sub_area_id && $new_sub_area_id > 0) {
        $response = [
            'status' => 'success',
            'message' => 'Sub-Area "' . $nama_sub_area . '" berhasil ditambahkan!',
            'data' => [
                'id' => $new_sub_area_id,
                'text' => $nama_sub_area
            ],
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        log_message('debug', 'Success response: ' . json_encode($response));
    } else {
        $db_error = $this->db->error();
        $response = [
            'status' => 'error',
            'message' => 'Gagal menambahkan sub-area baru. Error: ' . $db_error['message'],
            'csrf_hash' => $this->security->get_csrf_hash()
        ];
        log_message('error', 'DB Error: ' . json_encode($db_error));
    }

    echo json_encode($response);
    return;
}
    
    public function index() {
        redirect('Dashboard_new/index');
    }
    
}
