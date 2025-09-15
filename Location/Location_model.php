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
}
