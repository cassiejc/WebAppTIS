<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location_model extends CI_Model {

    public function save_location($latitude, $longitude, $address)
    {
        $data = [
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'address'    => $address,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('user_location', $data);
    }

    public function save_user_location($data)
    {
        // Add timestamp if not provided
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert('user_location', $data);
    }

    public function get_user_locations($user_id, $limit = null)
    {
        $this->db->where('id_user', $user_id);
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get('user_location')->result_array();
    }

    public function get_latest_user_location($user_id)
    {
        $this->db->where('id_user', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get('user_location')->row_array();
    }
}
