<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_target');
        $this->load->model('M_Dash', 'dash'); 
        $this->load->helper('url');
        $this->load->library('session');
    }

    // Tampilkan data target
    public function target()
    {
        // Get user info for template consistency
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $data['target'] = $this->M_target->get_all_target();
        $data['user'] = $user; // Pass user data to template
        
        // Load template with target view
        $this->load->view('templates/dash_h', $data);
        $this->load->view('admin_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    // Form edit target
    public function edit_target($id_target)
    {
        // Get user info for template consistency
        $token = $this->session->userdata('token');
        $user = $this->dash->getUserInfo($token)->row_array();
        
        $data['target'] = $this->M_target->get_target_by_id($id_target);
        $data['user'] = $user; // Pass user data to template
        
        // Load template with edit form
        $this->load->view('templates/dash_h', $data);
        $this->load->view('edit_target_view', $data);
        $this->load->view('templates/dash_f', $data);
    }

    // Proses update target
    public function update_target()
    {
        $id_target = $this->input->post('id_target');
        $target = $this->input->post('target');

        $update_result = $this->M_target->update_target($id_target, ['target' => $target]);
        
        if ($update_result) {
            $this->session->set_flashdata('success', 'Target berhasil diperbarui!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui target!');
        }
        
        redirect('admin/target');
    }
}
