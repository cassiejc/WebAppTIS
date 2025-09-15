<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location extends CI_Controller {

    public function index()
    {
        // Tampilkan view utama
        $this->load->view('location_view');
    }

    // Proxy untuk reverse geocoding (hindari CORS)
    public function reverse_geocode()
    {
        $lat = $this->input->get('lat');
        $lon = $this->input->get('lon');

        $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "MyApp/1.0 (your_email@example.com)");
        $output = curl_exec($ch);
        curl_close($ch);

        header('Content-Type: application/json');
        echo $output;
    }

    // Simpan data lokasi ke database
    public function save_location()
    {
        $latitude  = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        $address   = $this->input->post('address');

        $this->load->model('Location_model');
        $this->Location_model->save_location($latitude, $longitude, $address);

        echo json_encode(['status' => 'success']);
    }
}
