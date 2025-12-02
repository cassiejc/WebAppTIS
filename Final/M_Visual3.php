<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Visual extends CI_Model {
    
    // ... (Fungsi __construct dan $table_mapping Anda tidak berubah) ...
    private $table_mapping = [
        'Kantor' => 'visiting_kantor',
        'Agen' => 'visiting_agen',
        'Kemitraan' => 'visiting_kemitraan',
        'Sub Agen' => 'visiting_subagen',
        'Koordinasi' => 'visiting_koordinasi',
        'Grower' => 'visiting_p_grower',
        'Bebek Pedaging' => 'visiting_p_bebek_pedaging',
        'Layer' => 'visiting_p_layer',
        'Bebek Petelur' => 'visiting_p_bebek_petelur',
        'Puyuh' => 'visiting_p_puyuh',
        'Arap' => 'visiting_p_arap',
        'Lainnya' => 'visiting_p_lainnya'
    ];

    public function __construct() {
        parent::__construct();
    }
    
    // ... (Fungsi get_surveyor_performance, get_area_performance, get_visit_breakdown, get_all_visit_details, dll... TIDAK BERUBAH) ...
    // ... (Biarkan semua fungsi sampai get_harga_telur_harian_chart) ...
    
    public function get_surveyor_performance($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $final_query = '';
        
        // Tentukan rentang tanggal SQL
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day = date('Y-m-t', strtotime($end_date_str)); // 't' = hari terakhir di bulan

        // [MODIFIKASI] Ganti nama variabel agar lebih jelas
        // Ini adalah query untuk AKTUAL TOTAL (termasuk VIP)
        $union_query_aktual_sql = $this->_build_union_query_for_aktual($start_date_str, $end_date_str, $user_id, $area_id);
        
        // [BARU] Panggil fungsi baru untuk query KHUSUS VIP
        $union_query_vip_sql = $this->_build_union_query_for_vip_aktual($start_date_str, $end_date_str, $user_id, $area_id);
        
        $base_query_filter = "WHERE u.group_user = 'surveyor'"; 
        $permission_filter = ""; 
        if ($user_id !== null) {
            $permission_filter = "AND u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $permission_filter = "AND u.master_area_id = {$area_id}";
        }
        $main_user_filter = $base_query_filter . " " . $permission_filter;

        // [MODIFIKASI] Ambil SUM(target) dan SUM(vip_target)
        $target_sum_sql = "
            SELECT 
                id_user, 
                SUM(target) as total_target_in_range,
                SUM(vip_target) as total_vip_target_in_range  -- [BARU]
            FROM history_target 
            WHERE start_date >= '{$first_day}' AND start_date <= '{$last_day}' 
            GROUP BY id_user
        ";

        // [BARU] Subquery untuk menghitung aktual KHUSUS VIP
        $vip_visit_counts_sql = "
            SELECT 
                id_user, 
                COUNT(*) as aktual_vip 
            FROM ({$union_query_vip_sql}) as all_vip_visits 
            GROUP BY id_user
        ";

        // [MODIFIKASI] Query akhir untuk menggabungkan semuanya
        $final_query = "
            SELECT 
                u.id_user,
                u.username AS surveyor_name,
                
                -- Target & Aktual Standar
                COALESCE(target_sums.total_target_in_range, 0) AS target,
                COALESCE(visit_counts.aktual, 0) AS aktual,
                
                -- [BARU] Target & Aktual VIP
                COALESCE(target_sums.total_vip_target_in_range, 0) AS target_vip,
                COALESCE(vip_visit_counts.aktual_vip, 0) AS aktual_vip,

                -- Persentase Standar
                CASE 
                    WHEN COALESCE(target_sums.total_target_in_range, 0) > 0 
                    THEN (COALESCE(visit_counts.aktual, 0) / target_sums.total_target_in_range * 100) 
                    ELSE 0 
                END AS achievement_percent,
                
                -- [BARU] Persentase VIP
                CASE 
                    WHEN COALESCE(target_sums.total_vip_target_in_range, 0) > 0 
                    THEN (COALESCE(vip_visit_counts.aktual_vip, 0) / target_sums.total_vip_target_in_range * 100) 
                    ELSE 0 
                END AS achievement_percent_vip

            FROM z_master_user u
            
            -- Join Aktual Standar (Tetap sama, menggunakan query union yang lama)
            LEFT JOIN (
                SELECT id_user, COUNT(*) as aktual 
                FROM ({$union_query_aktual_sql}) as all_visits 
                GROUP BY id_user
            ) as visit_counts ON u.id_user = visit_counts.id_user
            
            -- Join Target (Sekarang berisi Standar + VIP)
            LEFT JOIN ({$target_sum_sql}) as target_sums ON u.id_user = target_sums.id_user
            
            -- [BARU] Join Aktual VIP
            LEFT JOIN ({$vip_visit_counts_sql}) as vip_visit_counts ON u.id_user = vip_visit_counts.id_user
            
            {$main_user_filter}
            ORDER BY achievement_percent DESC, aktual DESC;
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    // GANTI FUNGSI INI
    public function get_area_performance($start_date_str, $end_date_str, $user = null) {
        $final_query = '';
        
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day = date('Y-m-t', strtotime($end_date_str));

        $area_filter_sql = '';
        if ($user && isset($user['group_user']) && ($user['group_user'] === 'surveyor' || $user['group_user'] === 'koordinator')) {
            if (isset($user['master_area_id'])) {
                $area_filter_sql = "WHERE ma.master_area_id = " . $this->db->escape($user['master_area_id']);
            }
        }
        
        $user_id_filter = null;
        $area_id_filter = null;
        if ($user && isset($user['group_user'])) {
            if ($user['group_user'] === 'surveyor') {
                $user_id_filter = $user['id_user'];
            } elseif ($user['group_user'] === 'koordinator') {
                $area_id_filter = $user['master_area_id'];
            }
        }
        
        $union_query = $this->_build_union_query_for_aktual($start_date_str, $end_date_str, $user_id_filter, $area_id_filter);

        $target_sum_sql = "
            SELECT id_user, SUM(target) as total_target_in_range 
            FROM history_target 
            WHERE start_date >= '{$first_day}' AND start_date <= '{$last_day}' 
            GROUP BY id_user
        ";

        $final_query = "
            SELECT ma.master_area_id, ma.nama_area, SUM(user_performance.target) AS total_target, SUM(user_performance.aktual) AS total_aktual,
                CASE WHEN SUM(user_performance.target) > 0 THEN (SUM(user_performance.aktual) / SUM(user_performance.target) * 100) ELSE 0 END AS achievement_percent
            FROM master_area ma
            LEFT JOIN (
                SELECT u.master_area_id, 
                    COALESCE(target_sums.total_target_in_range, 0) AS target, 
                    COALESCE(visit_counts.aktual, 0) AS aktual
                FROM z_master_user u
                LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                LEFT JOIN ({$target_sum_sql}) as target_sums ON u.id_user = target_sums.id_user
            ) AS user_performance ON ma.master_area_id = user_performance.master_area_id
            {$area_filter_sql}
            GROUP BY ma.master_area_id, ma.nama_area ORDER BY ma.nama_area ASC;
        ";

        return $this->db->query($final_query)->result_array();
    }

    public function get_visit_breakdown($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $sub_queries = [];
        
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));
        
        // --- Setup Filter Biasa ---
        $where_clause = "WHERE t.waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        
        $user_filter_sql = '';
        if ($user_id !== null) $user_filter_sql = " AND t.id_user = {$user_id}";
        
        $area_filter_sql = '';
        if ($area_id !== null) $area_filter_sql = " AND u.master_area_id = {$area_id}";
        
        // 1. Loop Table Mapping
        foreach ($this->table_mapping as $kategori => $nama_tabel) {
            $sub_queries[] = "
                SELECT '{$kategori}' as kategori 
                FROM {$nama_tabel} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user
                {$where_clause} {$user_filter_sql} {$area_filter_sql}
            ";
        }

        // =================================================================
        // 2. INTEGRASI CRM
        // =================================================================
        
        $crm_filter_user_area = "";
        if ($user_id !== null) {
            $crm_filter_user_area = " AND u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $crm_filter_user_area = " AND u.master_area_id = {$area_id}";
        }

        // --- A. CRM BROILER (check_in) ---
        $where_broiler = "WHERE c.check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        
        $sub_queries[] = "
            SELECT 'CRM Broiler' as kategori 
            FROM crm_broiler c
            JOIN z_master_user u ON c.surveyor_id = u.surveyor_id
            {$where_broiler}
            {$crm_filter_user_area}
            AND c.livestock_type IS NOT NULL 
        ";

        // --- B. CRM DOC (survey_date) ---
        // [UBAH] Filter tanggal diganti ke survey_date
        $where_doc = "WHERE c.survey_date BETWEEN '{$first_day}' AND '{$last_day_with_time}'";

        $sub_queries[] = "
            SELECT 'CRM DOC' as kategori 
            FROM crm_doc c
            JOIN z_master_user u ON c.surveyor_id = u.surveyor_id
            {$where_doc}
            {$crm_filter_user_area}
        ";
        
        $union_query = implode(' UNION ALL ', $sub_queries);
        
        $final_query = "
            SELECT 
                kategori,
                COUNT(*) AS jumlah_visit
            FROM ({$union_query}) as semua_visit
            WHERE kategori IS NOT NULL AND kategori != ''
            GROUP BY kategori
            ORDER BY jumlah_visit DESC
        ";

        return $this->db->query($final_query)->result_array();
    }


public function get_all_visit_details($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
    $first_day = date('Y-m-01', strtotime($start_date_str));
    $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));
    
    $where_clause_visit = "WHERE t.waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
    $where_clause_new_cust = "WHERE t.created_at BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
    
    $user_filter_sql = '';
    if ($user_id !== null) {
        $user_filter_sql = " AND t.id_user = " . $this->db->escape($user_id);
    }

    $area_filter_sql = '';
    if ($area_id !== null && $user_id === null) {
        $area_filter_sql = " AND u.master_area_id = " . $this->db->escape($area_id);
    }
    
    $sub_queries = [];
    $db_name = $this->db->database; 

    $desired_columns = [
        'tujuan_kunjungan', 
        'jenis_kasus', 
        'latitude', 
        'longitude', 
        'location_address'
    ];
    
    // ===================================================================
    // 1. Loop untuk $table_mapping
    // ===================================================================
    foreach ($this->table_mapping as $kategori => $nama_tabel) {
        
        $cols_query = $this->db->query("
            SELECT COLUMN_NAME 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = '{$db_name}' AND TABLE_NAME = '{$nama_tabel}'
        ");
        $existing_columns = array_column($cols_query->result_array(), 'COLUMN_NAME');

        $select_parts = []; 
        
        $select_parts[] = "u.username";
        $select_parts[] = "u.master_area_id";
        $select_parts[] = "'{$kategori}' as kategori_visit";
        $select_parts[] = "t.waktu_kunjungan"; 

        foreach ($desired_columns as $col) {
            if (in_array($col, $existing_columns)) {
                $select_parts[] = "t.{$col} as {$col}";
            } else {
                $select_parts[] = "NULL as {$col}"; 
            }
        }
        
        // Logika Pakan
        $pakan_select_sql = "NULL as pakan"; 
        switch ($nama_tabel) {
            case 'visiting_p_layer':
                $layer_pakan_cols = [];
                if (in_array('layer_pilihan_pakan_cp', $existing_columns)) {
                    $layer_pakan_cols[] = "NULLIF(t.layer_pilihan_pakan_cp, '')";
                }
                if (in_array('layer_pilihan_pakan_lain', $existing_columns)) {
                    $layer_pakan_cols[] = "NULLIF(t.layer_pilihan_pakan_lain, '')";
                }
                if (!empty($layer_pakan_cols)) {
                    $pakan_select_sql = "CONCAT_WS(', ', " . implode(', ', $layer_pakan_cols) . ") as pakan";
                }
                break;
            case 'visiting_p_arap':
            case 'visiting_p_bebek_petelur':
            case 'visiting_p_puyuh':
                if (in_array('pakan_petelur', $existing_columns)) {
                    $pakan_select_sql = "t.pakan_petelur as pakan";
                }
                break;
            case 'visiting_p_grower':
            case 'visiting_p_bebek_pedaging':
                if (in_array('pakan_pedaging', $existing_columns)) {
                    $pakan_select_sql = "t.pakan_pedaging as pakan";
                }
                break;
            case 'visiting_p_lainnya':
                if (in_array('pakan_lainnya', $existing_columns)) {
                    $pakan_select_sql = "t.pakan_lainnya as pakan";
                }
                break;
        }
        $select_parts[] = $pakan_select_sql; 
        
        // Logika Customer
        $customer_select_sql = "NULL as nama_customer"; 
        switch ($nama_tabel) {
            case 'visiting_agen':
                if (in_array('nama_agen', $existing_columns)) {
                    $customer_select_sql = "t.nama_agen as nama_customer";
                }
                break;
            case 'visiting_subagen':
                if (in_array('nama_subagen', $existing_columns)) {
                    $customer_select_sql = "t.nama_subagen as nama_customer";
                }
                break;
            case 'visiting_kantor':
                if (in_array('nama_kantor', $existing_columns)) {
                    $customer_select_sql = "t.nama_kantor as nama_customer";
                }
                break;
            case 'visiting_kemitraan':
                if (in_array('nama_kantor_kemitraan', $existing_columns)) {
                    $customer_select_sql = "t.nama_kantor_kemitraan as nama_customer";
                }
                break;
            default:
                if (in_array('nama_farm', $existing_columns)) {
                    $customer_select_sql = "t.nama_farm as nama_customer";
                } elseif (in_array('layer_nama_farm', $existing_columns)) {
                    $customer_select_sql = "t.layer_nama_farm as nama_customer";
                }
                break;
        }
        $select_parts[] = $customer_select_sql; 
        
        // Logika Kapasitas
        $kapasitas_select_sql = "NULL as kapasitas"; 
        if (in_array('nama_farm', $existing_columns)) {
            $kapasitas_select_sql = "(
                SELECT hfc.kapasitas 
                FROM history_farm_capacity hfc
                WHERE hfc.nama_farm = t.nama_farm 
                AND t.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                LIMIT 1
            ) as kapasitas";
        } elseif (in_array('layer_nama_farm', $existing_columns)) {
            $kapasitas_select_sql = "(
                SELECT hfc.kapasitas 
                FROM history_farm_capacity hfc
                WHERE hfc.nama_farm = t.layer_nama_farm 
                AND t.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                LIMIT 1
            ) as kapasitas";
        }
        $select_parts[] = $kapasitas_select_sql;
        
        // Logika VIP Farm
        if (in_array('vip_farm', $existing_columns)) {
            $select_parts[] = "t.vip_farm as vip_farm";
        } else {
            $select_parts[] = "NULL as vip_farm";
        }
        
        // [BARU] Logika Catatan
        $catatan_select_sql = "NULL as catatan";
        switch ($nama_tabel) {
            case 'visiting_p_layer':
                if (in_array('catatan_layer', $existing_columns)) {
                    $catatan_select_sql = "t.catatan_layer as catatan";
                }
                break;
            case 'visiting_p_arap':
            case 'visiting_p_bebek_petelur':
            case 'visiting_p_puyuh':
                if (in_array('catatan_petelur', $existing_columns)) {
                    $catatan_select_sql = "t.catatan_petelur as catatan";
                }
                break;
            case 'visiting_p_grower':
            case 'visiting_p_bebek_pedaging':
                if (in_array('catatan_pedaging', $existing_columns)) {
                    $catatan_select_sql = "t.catatan_pedaging as catatan";
                }
                break;
            case 'visiting_p_lainnya':
                if (in_array('catatan_lainnya', $existing_columns)) {
                    $catatan_select_sql = "t.catatan_lainnya as catatan";
                }
                break;
        }
        $select_parts[] = $catatan_select_sql;
        
        $select_string = implode(', ', $select_parts); 
        
        $sub_queries[] = "
            SELECT {$select_string}
            FROM {$nama_tabel} t
            LEFT JOIN z_master_user u ON t.id_user = u.id_user
            {$where_clause_visit} {$user_filter_sql} {$area_filter_sql}
        ";
    }
    
    // ===================================================================
    // 2. Query manual untuk tabel non-mapping (SUDAH DIPERBAIKI)
    // ===================================================================

    $get_cols = function($table) use ($db_name) {
        $cols_query = $this->db->query("
            SELECT COLUMN_NAME 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = '{$db_name}' AND TABLE_NAME = '{$table}'
        ");
        return array_column($cols_query->result_array(), 'COLUMN_NAME');
    };

    // SEMINAR (sudah ada kolom catatan)
    $seminar_cols = $get_cols('seminar');
    $select_parts = ["u.username", "u.master_area_id", "'Seminar' as kategori_visit", "t.waktu_kunjungan"];
    $select_parts[] = in_array('tujuan_kunjungan', $seminar_cols) ? "t.tujuan_kunjungan as tujuan_kunjungan" : "NULL as tujuan_kunjungan";
    $select_parts[] = in_array('jenis_kasus', $seminar_cols) ? "t.jenis_kasus as jenis_kasus" : "'Seminar' as jenis_kasus"; 
    $select_parts[] = in_array('latitude', $seminar_cols) ? "t.latitude as latitude" : "NULL as latitude";
    $select_parts[] = in_array('longitude', $seminar_cols) ? "t.longitude as longitude" : "NULL as longitude";
    $select_parts[] = in_array('location_address', $seminar_cols) ? "t.location_address as location_address" : "NULL as location_address";
    $select_parts[] = "NULL as pakan"; 
    $select_parts[] = in_array('nama_farm_peternak', $seminar_cols) ? "t.nama_farm_peternak as nama_customer" : (in_array('nama_customer', $seminar_cols) ? "t.nama_customer as nama_customer" : "NULL as nama_customer");
    $select_parts[] = "NULL as kapasitas";
    $select_parts[] = "NULL as vip_farm";
    $select_parts[] = in_array('catatan', $seminar_cols) ? "t.catatan as catatan" : "NULL as catatan";
    $select_string = implode(', ', $select_parts);
    $sub_queries[] = "SELECT {$select_string} FROM seminar t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$where_clause_visit} {$user_filter_sql} {$area_filter_sql}";

    // SAMPLE (sudah ada kolom catatan)
    $sample_cols = $get_cols('sample_form');
    $select_parts = ["u.username", "u.master_area_id", "'Kirim Sample' as kategori_visit", "t.waktu_kunjungan"];
    $select_parts[] = in_array('tujuan_pengambilan_sample', $sample_cols) ? "t.tujuan_pengambilan_sample as tujuan_kunjungan" : (in_array('tujuan_kunjungan', $sample_cols) ? "t.tujuan_kunjungan as tujuan_kunjungan" : "NULL as tujuan_kunjungan");
    $select_parts[] = "NULL as jenis_kasus"; 
    $select_parts[] = in_array('latitude', $sample_cols) ? "t.latitude as latitude" : "NULL as latitude";
    $select_parts[] = in_array('longitude', $sample_cols) ? "t.longitude as longitude" : "NULL as longitude";
    $select_parts[] = in_array('location_address', $sample_cols) ? "t.location_address as location_address" : "NULL as location_address";
    $select_parts[] = "NULL as pakan"; 
    $select_parts[] = in_array('nama_farm', $sample_cols) ? "t.nama_farm as nama_customer" : (in_array('nama_customer', $sample_cols) ? "t.nama_customer as nama_customer" : "NULL as nama_customer");
    $select_parts[] = "NULL as kapasitas";
    $select_parts[] = "NULL as vip_farm";
    $select_parts[] = in_array('catatan', $sample_cols) ? "t.catatan as catatan" : "NULL as catatan";
    $select_string = implode(', ', $select_parts);
    $sub_queries[] = "SELECT {$select_string} FROM sample_form t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$where_clause_visit} {$user_filter_sql} {$area_filter_sql}";

    // MASTER_FARM (sudah ada kolom catatan)
    $mf_cols = $get_cols('master_farm');
    $select_parts = ["u.username", "u.master_area_id", "'New Customers' as kategori_visit", "t.created_at as waktu_kunjungan"];
    $select_parts[] = "'Registrasi Customer Baru' as tujuan_kunjungan";
    $select_parts[] = "NULL as jenis_kasus"; 
    $select_parts[] = in_array('latitude', $mf_cols) ? "t.latitude as latitude" : "NULL as latitude";
    $select_parts[] = in_array('longitude', $mf_cols) ? "t.longitude as longitude" : "NULL as longitude";
    $select_parts[] = in_array('location_address', $mf_cols) ? "t.location_address as location_address" : "NULL as location_address";        
    $select_parts[] = "NULL as pakan";
    $select_parts[] = in_array('nama_farm', $mf_cols) ? "t.nama_farm as nama_customer" : "NULL as nama_customer";
    $select_parts[] = in_array('kapasitas_farm', $mf_cols) ? "t.kapasitas_farm as kapasitas_farm" : "NULL as kapasitas_farm";
    $select_parts[] = in_array('vip_farm', $mf_cols) ? "t.vip_farm as vip_farm" : "NULL as vip_farm";
    $select_parts[] = "NULL as catatan";
    $select_string = implode(', ', $select_parts);
    $sub_queries[] = "SELECT {$select_string} FROM master_farm t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$where_clause_new_cust} {$user_filter_sql} {$area_filter_sql}";

    // MASTER_SUBAGEN (sudah ada kolom catatan)
    $ms_cols = $get_cols('master_subagen');
    $select_parts = ["u.username", "u.master_area_id", "'New Customers' as kategori_visit", "t.created_at as waktu_kunjungan"];
    $select_parts[] = "'Registrasi Customer Baru' as tujuan_kunjungan";
    $select_parts[] = "NULL as jenis_kasus"; 
    $select_parts[] = in_array('latitude', $ms_cols) ? "t.latitude as latitude" : "NULL as latitude";
    $select_parts[] = in_array('longitude', $ms_cols) ? "t.longitude as longitude" : "NULL as longitude";
    $select_parts[] = in_array('location_address', $ms_cols) ? "t.location_address as location_address" : "NULL as location_address";
    $select_parts[] = "NULL as pakan";
    $select_parts[] = in_array('nama_subagen', $ms_cols) ? "t.nama_subagen as nama_customer" : "NULL as nama_customer";
    $select_parts[] = "NULL as kapasitas";
    $select_parts[] = "NULL as vip_farm";
    $select_parts[] = "NULL as catatan";
    $select_string = implode(', ', $select_parts);
    $sub_queries[] = "SELECT {$select_string} FROM master_subagen t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$where_clause_new_cust} {$user_filter_sql} {$area_filter_sql}";

    // MASTER_KEMITRAAN (sudah ada kolom catatan)
    $mk_cols = $get_cols('master_kemitraan');
    $select_parts = ["u.username", "u.master_area_id", "'New Customers' as kategori_visit", "t.created_at as waktu_kunjungan"];
    $select_parts[] = "'Registrasi Customer Baru' as tujuan_kunjungan";
    $select_parts[] = "NULL as jenis_kasus"; 
    $select_parts[] = in_array('latitude', $mk_cols) ? "t.latitude as latitude" : "NULL as latitude";
    $select_parts[] = in_array('longitude', $mk_cols) ? "t.longitude as longitude" : "NULL as longitude";
    $select_parts[] = in_array('location_address', $mk_cols) ? "t.location_address as location_address" : "NULL as location_address";
    $select_parts[] = "NULL as pakan";
    $select_parts[] = in_array('nama_kemitraan', $mk_cols) ? "t.nama_kemitraan as nama_customer" : "NULL as nama_customer";
    $select_parts[] = "NULL as kapasitas";
    $select_parts[] = "NULL as vip_farm";
    $select_parts[] = "NULL as catatan";
    $select_string = implode(', ', $select_parts);
    $sub_queries[] = "SELECT {$select_string} FROM master_kemitraan t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$where_clause_new_cust} {$user_filter_sql} {$area_filter_sql}";

        $crm_user_area_filter = "";
        if ($user_id !== null) {
            $crm_user_area_filter = " AND u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $crm_user_area_filter = " AND u.master_area_id = {$area_id}";
        }

        // --- A. CRM BROILER (check_in) ---
        $where_broiler = "WHERE c.check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'";

        $crm_broiler_selects = [
            "u.username", "u.master_area_id",
            "'CRM Broiler' as kategori_visit", 
            "c.check_in as waktu_kunjungan", "'-' as tujuan_kunjungan", "NULL as jenis_kasus",
            "c.latitude", "c.longitude", "NULL as location_address",
            "c.feed_name as pakan", "c.farm_name as nama_customer", "c.capacity as kapasitas", "NULL as vip_farm" , "NULL as catatan"
        ];
        $sub_queries[] = "
            SELECT " . implode(', ', $crm_broiler_selects) . "
            FROM crm_broiler c
            JOIN z_master_user u ON c.surveyor_id = u.surveyor_id
            {$where_broiler} {$crm_user_area_filter}
        ";

        // --- B. CRM DOC (survey_date) ---        
        $where_doc = "WHERE c.survey_date BETWEEN '{$first_day}' AND '{$last_day_with_time}'";

        $crm_doc_selects = [
            "u.username", "u.master_area_id",
            "'CRM DOC' as kategori_visit", 
            "c.survey_date as waktu_kunjungan", "'-' as tujuan_kunjungan", "NULL as jenis_kasus",
            "c.latitude", "c.longitude", "NULL as location_address",
            "c.feed_name as pakan", "c.farm_name as nama_customer", "NULL as kapasitas", "NULL as vip_farm", "NULL as catatan"
        ];
        $sub_queries[] = "
            SELECT " . implode(', ', $crm_doc_selects) . "
            FROM crm_doc c
            JOIN z_master_user u ON c.surveyor_id = u.surveyor_id
            {$where_doc} {$crm_user_area_filter}
        ";

        // --- Selesai Penambahan ---

        $union_query = implode(' UNION ALL ', $sub_queries);

        // --- [MODIFIKASI 7] Query akhir diubah dari SELECT * ---
        // Kita gunakan COALESCE() untuk mengganti semua nilai NULL dengan '-'
        $final_query = "
            SELECT 
                username,
                master_area_id,
                kategori_visit,
                waktu_kunjungan,
                COALESCE(tujuan_kunjungan, '-') as tujuan_kunjungan,
                COALESCE(jenis_kasus, '-') as jenis_kasus,
                COALESCE(latitude, '-') as latitude,
                COALESCE(longitude, '-') as longitude,
                COALESCE(location_address, '-') as location_address,
                COALESCE(pakan, '-') as pakan,
                COALESCE(nama_customer, '-') as nama_customer,
                COALESCE(kapasitas, '-') as kapasitas,
                COALESCE(vip_farm, '-') as vip_farm,
                COALESCE(catatan, '-') as catatan
            FROM ({$union_query}) as semua_visit
            ORDER BY waktu_kunjungan DESC
        ";
        // --- Akhir Modifikasi 7 ---
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_seminar_count_by_range($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $this->db->reset_query();
        $this->db->from('seminar t'); 

        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));
        $this->db->where("t.waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'");

        if ($user_id !== null) {
            $this->db->where('t.id_user', $user_id);
        }
        
        if ($area_id !== null && $user_id === null) {
            $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
            $this->db->where('u.master_area_id', $area_id);
        }

        return $this->db->count_all_results(); 
    }

    public function get_sample_count_by_range($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $this->db->reset_query();
        $this->db->from('sample_form t');

        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));
        $this->db->where("t.waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'");

        if ($user_id !== null) {
            $this->db->where('t.id_user', $user_id);
        }
        
        if ($area_id !== null && $user_id === null) {
            $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
            $this->db->where('u.master_area_id', $area_id);
        }

        return $this->db->count_all_results(); 
    }
        
    public function get_new_customer_count_by_range($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $total_new = 0;
        $tables = ['master_farm', 'master_subagen', 'master_kemitraan'];
        
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

        foreach ($tables as $table) {
            $this->db->reset_query();
            $this->db->from("{$table} t"); 

            $this->db->where("t.created_at BETWEEN '{$first_day}' AND '{$last_day_with_time}'");

            if ($user_id !== null) {
                $this->db->where('t.id_user', $user_id);
            }
            
            if ($area_id !== null && $user_id === null) {
                $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
                $this->db->where('u.master_area_id', $area_id);
            }
            
            $total_new += $this->db->count_all_results();
        }
        return $total_new;
    }

    private function _build_union_query_for_aktual($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $sub_queries = [];
        
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

        // --- FILTER UMUM (Untuk tabel existing) ---
        $date_filter_sql = "AND waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        $date_filter_created_at = "WHERE created_at BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        
        // Filter User/Area untuk tabel yang sudah punya kolom id_user langsung
        $user_area_filter_waktu = "";
        $user_area_filter_created_at = "";

        if ($user_id !== null) {
            $user_area_filter_waktu = " AND t.id_user = {$user_id}";
            $user_area_filter_created_at = " AND t.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $user_area_filter_waktu = " AND u.master_area_id = {$area_id}";
            $user_area_filter_created_at = " AND u.master_area_id = {$area_id}";
        }

        // 1. Loop tabel mapping (Visiting biasa)
        foreach ($this->table_mapping as $table) {
            $sub_queries[] = "SELECT t.id_user FROM {$table} t LEFT JOIN z_master_user u ON t.id_user = u.id_user WHERE t.id_user IS NOT NULL AND t.id_user != 0 {$date_filter_sql} {$user_area_filter_waktu}";
        }
        
        // 2. Tabel Seminar & Sample
        $sub_queries[] = "SELECT t.id_user FROM seminar t LEFT JOIN z_master_user u ON t.id_user = u.id_user WHERE t.id_user IS NOT NULL AND t.id_user != 0 {$date_filter_sql} {$user_area_filter_waktu}";
        $sub_queries[] = "SELECT t.id_user FROM sample_form t LEFT JOIN z_master_user u ON t.id_user = u.id_user WHERE t.id_user IS NOT NULL AND t.id_user != 0 {$date_filter_sql} {$user_area_filter_waktu}";
        
        // 3. Tabel New Customer (created_at)
        $created_at_tables = ['master_farm', 'master_subagen', 'master_kemitraan'];
        foreach ($created_at_tables as $table) {
            $sub_queries[] = "SELECT t.id_user FROM {$table} t LEFT JOIN z_master_user u ON t.id_user = u.id_user {$date_filter_created_at} {$user_area_filter_created_at}";
        }

        // =================================================================
        // 4. [BARU] INTEGRASI CRM BROILER (Masuk ke Aktual Reguler)
        // =================================================================
        // Logika: JOIN crm_broiler.surveyor_id dengan z_master_user.surveyor_id
        
        $crm_where_date = "WHERE c.check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        
        // Filter User/Area khusus untuk alias 'u' pada join CRM
        $crm_user_area_filter = "";
        if ($user_id !== null) {
            $crm_user_area_filter = " AND u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $crm_user_area_filter = " AND u.master_area_id = {$area_id}";
        }

        $sub_queries[] = "
            SELECT u.id_user 
            FROM crm_broiler c
            JOIN z_master_user u ON c.surveyor_id = u.surveyor_id 
            {$crm_where_date} 
            {$crm_user_area_filter}
            AND c.surveyor_id IS NOT NULL
        ";
        
        return implode(' UNION ALL ', $sub_queries);
    }

    private function _build_union_query_for_vip_aktual($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
        $sub_queries = [];
        
        $first_day = date('Y-m-01', strtotime($start_date_str));
        $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

        $date_filter_sql = "AND waktu_kunjungan BETWEEN '{$first_day}' AND '{$last_day_with_time}'";
        
        // Filter user & area untuk waktu_kunjungan
        $user_area_filter_waktu = "";
        if ($user_id !== null) {
            $user_area_filter_waktu = " AND t.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $user_area_filter_waktu = " AND u.master_area_id = {$area_id}";
        }

        $vip_farm_tables = [
            'visiting_p_grower',
            'visiting_p_bebek_pedaging',
            'visiting_p_layer',
            'visiting_p_bebek_petelur',
            'visiting_p_puyuh',
            'visiting_p_arap'
            // 'visiting_p_lainnya'
        ];

        // Loop HANYA pada tabel-tabel ini
        foreach ($vip_farm_tables as $table) {
            // [PENTING] Tambahkan filter AND t.vip_farm = 'Ya'
            $sub_queries[] = "
                SELECT t.id_user 
                FROM {$table} t 
                LEFT JOIN z_master_user u ON t.id_user = u.id_user 
                WHERE t.id_user IS NOT NULL 
                AND t.id_user != 0 
                AND t.vip_farm = 'Ya'  -- <<< FILTER VIP UTAMA
                {$date_filter_sql} 
                {$user_area_filter_waktu}
            ";
        }
        
        // Sesuai permintaan, kita tidak menyertakan seminar, sample, atau master_farm di hitungan VIP.
        
        if (empty($sub_queries)) {
            // Failsafe agar query SQL tidak error jika array $vip_farm_tables kosong
            return "SELECT NULL as id_user LIMIT 0"; 
        }

        return implode(' UNION ALL ', $sub_queries);
    }
   
    public function get_kasus_breakdown_count($year, $user_id = null, $area_id = null) { 
        $year = (int)$year;
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(t.waktu_kunjungan) = {$year}" : "";
        $user_filter_sql = ($user_id !== null) ? "AND t.id_user = {$user_id}" : "";
        
        $area_filter_sql = '';
        if ($area_id !== null && $user_id === null) {
            $area_filter_sql = " AND u.master_area_id = {$area_id}";
        }

        $allowed_categories = "'Bacterial', 'Parasit', 'Jamur', 'Viral'";
        
        // [PERBAIKAN] Filter lebih ketat untuk mengecualikan 'Lainnya' dan 'lambat puncak'
        $exclude_cases_sql = "AND t.jenis_kasus NOT LIKE '%Lain-lain%' AND t.jenis_kasus NOT IN ('lambat puncak')";

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT 
                    DATE_FORMAT(t.waktu_kunjungan, '%b %Y') as bulan_tahun,
                    CASE WHEN INSTR(t.jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(t.jenis_kasus, ':', 1) ELSE t.jenis_kasus END as kategori_kasus
                FROM {$table} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user 
                WHERE t.jenis_kasus IS NOT NULL AND t.jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql} {$area_filter_sql} {$exclude_cases_sql}
            ";
        }
        
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT 
                bulan_tahun, 
                kategori_kasus, 
                COUNT(*) as jumlah_kasus
            FROM ({$union_query}) as semua_kasus_raw
            -- [BARU] Tambahkan pengecualian untuk kategori hasil 'Lainnya' jika ada 
            WHERE kategori_kasus IS NOT NULL 
            AND kategori_kasus != '' 
            AND bulan_tahun IS NOT NULL 
            AND kategori_kasus IN ({$allowed_categories})
            AND kategori_kasus NOT IN ('Lain-lain') 
            GROUP BY bulan_tahun, kategori_kasus
            ORDER BY STR_TO_DATE(CONCAT('01 ', bulan_tahun), '%d %b %Y'), kategori_kasus
        ";
        
        return $this->db->query($final_query)->result_array();
    }
   
    public function get_kasus_pivot_by_area($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(t.waktu_kunjungan) = {$year}" : "";
        $user_filter_sql = ($user_id !== null) ? "AND t.id_user = {$user_id}" : "";
        
        // [PERBAIKAN] Filter untuk mengecualikan 'Lainnya' dan 'lambat puncak'
        $exclude_cases_sql = "AND t.jenis_kasus NOT LIKE '%Lain-lain%' AND t.jenis_kasus NOT IN ('lambat puncak')";

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT t.id_user, CASE WHEN INSTR(t.jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(t.jenis_kasus, ':', 1) ELSE t.jenis_kasus END as kategori_kasus
                FROM {$table} t
                WHERE t.jenis_kasus IS NOT NULL AND t.jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql} {$exclude_cases_sql}
            ";
        }
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT ma.nama_area, u.master_area_id, semua_kasus.kategori_kasus, COUNT(*) as jumlah
            FROM ({$union_query}) as semua_kasus
            JOIN z_master_user u ON semua_kasus.id_user = u.id_user
            JOIN master_area ma ON u.master_area_id = ma.master_area_id
            -- [BARU] Tambahkan pengecualian untuk kategori hasil 'Lainnya' jika ada
            WHERE semua_kasus.kategori_kasus IS NOT NULL 
            AND semua_kasus.kategori_kasus != ''
            AND semua_kasus.kategori_kasus NOT IN ('Lain-lain')
            GROUP BY ma.nama_area, u.master_area_id, semua_kasus.kategori_kasus
            ORDER BY ma.nama_area, semua_kasus.kategori_kasus;
        ";
        
        return $this->db->query($final_query)->result_array();
    }
    
    // GANTI FUNGSI INI DI M_Visual.php
public function get_kasus_detail_list($year, $user_id = null, $area_id = null) { 
    $year = (int)$year;
    $peternakan_tables = [
        'visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer',
        'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'
    ];
    $sub_queries = [];
    
    $year_filter_sql = ($year != 0) ? "AND YEAR(t.waktu_kunjungan) = {$year}" : "";
    $user_filter_sql = ($user_id !== null) ? "AND t.id_user = {$user_id}" : "";
    
    $area_filter_sql = '';
    if ($area_id !== null && $user_id === null) {
        $area_filter_sql = " AND u.master_area_id = {$area_id}";
    }

    $allowed_categories = "'Bacterial', 'Parasit', 'Jamur', 'Viral'";
    $exclude_cases_sql = "AND t.jenis_kasus NOT LIKE '%Lain-lain%' AND t.jenis_kasus NOT IN ('lambat puncak')";

    foreach ($peternakan_tables as $table) {
        $farm_column_selection = 'nama_farm';
        if ($table === 'visiting_p_layer') {
            $farm_column_selection = 'layer_nama_farm AS nama_farm';
        }

        // [TAMBAHAN] Pilih kolom location_address
        $sub_queries[] = "
            SELECT 
                t.waktu_kunjungan,
                t.id_user,
                {$farm_column_selection},
                t.jenis_kasus,
                t.location_address
            FROM {$table} t
            LEFT JOIN z_master_user u ON t.id_user = u.id_user
            WHERE 
                t.jenis_kasus IS NOT NULL 
                AND t.jenis_kasus != '-'
                {$year_filter_sql}
                {$user_filter_sql}
                {$area_filter_sql}
                {$exclude_cases_sql} 
        ";
    }
    
    $union_query = implode(' UNION ALL ', $sub_queries);

    $final_query = "
        SELECT 
            semua_kasus.*,
            ma.nama_area
        FROM ({$union_query}) as semua_kasus
        LEFT JOIN z_master_user u ON semua_kasus.id_user = u.id_user
        LEFT JOIN master_area ma ON u.master_area_id = ma.master_area_id
        ORDER BY waktu_kunjungan DESC
    ";
    
    return $this->db->query($final_query)->result_array();
}

    public function get_harga_telur_harian_chart($year, $month) {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->where('YEAR(tanggal)', $year);
        $this->db->where('MONTH(tanggal)', $month);
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil data harga jual telur layer bulanan.
     * @param int|null $year Jika null, ambil semua tahun.
     * @return array
     */
    public function get_harga_telur_bulanan_chart($year = null)
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        
        if ($year !== null) {
            $this->db->where('tahun', $year);
            $this->db->order_by('bulan', 'ASC');
        } else {
            // Ambil semua tahun, urutkan
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_harga_telur_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_telur_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->where('tanggal', date('Y-m-d')); 
        return $this->db->get()->row_array();
    }

    public function get_harga_jagung_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_jagung_harian_chart($year, $month) {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->where('YEAR(tanggal)', $year);
        $this->db->where('MONTH(tanggal)', $month);
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    // ==================================================================
    // === MULAI PERBAIKAN (Semua fungsi ..._bulanan_chart() di bawah) ===
    // ==================================================================

    public function get_harga_jagung_bulanan_chart($tahun = null)
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }

    public function get_harga_jagung_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_katul_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_katul_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_katul_bulanan_chart($tahun = null)
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_katul'); 
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }

    public function get_harga_katul_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_afkir_hari_ini()
    {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }

    public function get_harga_afkir_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_afkir_bulanan_chart($tahun = null)
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_afkir');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }

    public function get_harga_afkir_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_telur_puyuh_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_telur_puyuh', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_telur_puyuh_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_telur_puyuh');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_puyuh_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_puyuh');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_puyuh_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_telur_puyuh')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_telur_bebek_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_telur_bebek', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_telur_bebek_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_telur_bebek');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_bebek_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_bebek');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_telur_bebek_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_telur_bebek')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_bebek_pedaging_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_bebek_pedaging', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_bebek_pedaging_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_bebek_pedaging_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_bebek_pedaging_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_live_bird_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_live_bird', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_live_bird_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_live_bird');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_live_bird_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_live_bird');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_live_bird_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_live_bird')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_pakan_broiler_hari_ini() {
        $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data')->from('master_harga');
        return $this->db->where('nama_harga', 'Pakan Komplit Broiler')->get()->row_array();
    }
    public function get_harga_pakan_broiler_harian_chart() { return []; }
    public function get_harga_pakan_broiler_bulanan_chart($tahun = null) { return []; } // Tambahkan ($tahun = null)
    public function get_harga_pakan_broiler_tahunan_chart() { return []; }
    
    public function get_harga_doc_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->where('tanggal', date('Y-m-d'));
        return $this->db->get()->row_array();
    }
    
    public function get_harga_doc_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_doc_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_doc');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
   
    public function get_harga_doc_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_doc')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_konsentrat_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'harga_konsentrat_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_konsentrat_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_konsentrat_layer_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_konsentrat_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_hpp_konsentrat_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_konsentrat_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_hpp_konsentrat_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_hpp_konsentrat_layer_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_hpp_konsentrat_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_hpp_komplit_layer_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_komplit_layer', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_hpp_komplit_layer_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_komplit_layer');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_hpp_komplit_layer_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_komplit_layer');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_hpp_komplit_layer_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_komplit_layer')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_cost_komplit_broiler_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'cost_komplit_broiler', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_cost_komplit_broiler_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'cost_komplit_broiler');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_cost_komplit_broiler_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'cost_komplit_broiler');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_cost_komplit_broiler_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'cost_komplit_broiler')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
   
    public function get_harga_hpp_broiler_hari_ini() {
        $this->db->select('nilai_rata_rata, jumlah_sumber_data')->from('harga_rata_rata_harian');
        return $this->db->where(['jenis_harga' => 'hpp_broiler', 'tanggal' => date('Y-m-d')])->get()->row_array();
    }
    public function get_harga_hpp_broiler_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata')->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'hpp_broiler');
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')))->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }
    public function get_harga_hpp_broiler_bulanan_chart($tahun = null) {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_broiler');
        
        if ($tahun !== null) {
            $this->db->where('tahun', $tahun);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }
        
        return $this->db->get()->result_array();
    }
    public function get_harga_hpp_broiler_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_broiler')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }
    
    public function get_harga_pakan_layer_hari_ini() {
        $this->db->select('nilai_harga as nilai_rata_rata, 1 as jumlah_sumber_data');
        $this->db->from('master_harga');
        // Pastikan string 'Pakan Komplit Layer' ini SAMA PERSIS dengan di database Anda
        return $this->db->where('nama_harga', 'Pakan Komplit Layer')->get()->row_array();
    }

    public function get_all_tipe_ternak()
    {
        $this->db->select('tipe_ternak');
        $this->db->from('master_farm');
        $this->db->distinct();
        $this->db->order_by('tipe_ternak', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_monthly_vacancy_percentage($filters = [], $selected_years = [])
    {
        $cte_sql = "
            WITH 
            semua_kunjungan AS (
                (SELECT layer_nama_farm AS nama_farm, waktu_kunjungan, (layer_pakai_pakan_cp + layer_selain_pakan_cp) AS jumlah_terisi FROM visiting_p_layer)
                UNION ALL
                (SELECT nama_farm, waktu_kunjungan, (efektif_terisi_cp_petelur + efektif_terisi_non_cp_petelur) AS jumlah_terisi FROM visiting_p_arap)
                UNION ALL
                (SELECT nama_farm, waktu_kunjungan, (efektif_terisi_cp_petelur + efektif_terisi_non_cp_petelur) AS jumlah_terisi FROM visiting_p_bebek_petelur)
                UNION ALL
                (SELECT nama_farm, waktu_kunjungan, (efektif_terisi_cp_petelur + efektif_terisi_non_cp_petelur) AS jumlah_terisi FROM visiting_p_puyuh)
                UNION ALL
                (SELECT nama_farm, waktu_kunjungan, (efektif_terisi_cp_pedaging + efektif_terisi_non_cp_pedaging) AS jumlah_terisi FROM visiting_p_grower)
                UNION ALL
                -- PERBAIKAN FINAL untuk Bebek Pedaging: Mencoba BEBEKPEDAGING digabung
                (SELECT nama_farm, waktu_kunjungan, (efektif_terisi_cp_pedaging + efektif_terisi_non_cp_pedaging) AS jumlah_terisi FROM visiting_p_bebek_pedaging)
            ),
            kunjungan_terakhir_bulanan AS (
                SELECT
                    nama_farm,
                    waktu_kunjungan,
                    jumlah_terisi,
                    ROW_NUMBER() OVER(
                        PARTITION BY nama_farm, YEAR(waktu_kunjungan), MONTH(waktu_kunjungan) 
                        ORDER BY waktu_kunjungan DESC
                    ) as rn
                FROM semua_kunjungan
                WHERE nama_farm IS NOT NULL AND nama_farm != ''
            )
        ";

        // --- [AWAL PERUBAHAN LOGIKA FILTER] ---
        $base_where_clauses = []; 

        // 1. Filter Tipe Ternak (Jika ada)
        if (!empty($filters['tipe_ternak'])) {
             $base_where_clauses[] = "mf.tipe_ternak = " . $this->db->escape($filters['tipe_ternak']);
        }

        // 2. Filter Tahun (WAJIB ADA, minimal default)
        if (!empty($selected_years)) {
            $year_in_clause = implode(',', array_map('intval', $selected_years));
            if (!empty($year_in_clause)) {
                $base_where_clauses[] = "YEAR(ktb.waktu_kunjungan) IN ({$year_in_clause})";
            }
        } else {
            // Jika tidak ada tahun terpilih (misal: user uncheck semua), jangan tampilkan data
            $base_where_clauses[] = "1 = 0"; // Kondisi false
        }
        
        $where_sql = "";
        if (!empty($base_where_clauses)) {
            $where_sql = "AND " . implode(" AND ", $base_where_clauses);
        }
        // --- [AKHIR PERUBAHAN LOGIKA FILTER] ---

        
        // [PERUBAHAN] Query utama sekarang di-grup berdasarkan tipe ternak
        $final_query_body = "
            SELECT 
                mf.tipe_ternak,
                YEAR(ktb.waktu_kunjungan) AS tahun,
                MONTH(ktb.waktu_kunjungan) AS bulan,
                (SUM(hfc.kapasitas) - SUM(ktb.jumlah_terisi)) / SUM(hfc.kapasitas) * 100 AS persentase_kosong
            FROM kunjungan_terakhir_bulanan ktb
            JOIN history_farm_capacity hfc ON ktb.nama_farm = hfc.nama_farm
                                        AND ktb.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
            JOIN master_farm mf ON ktb.nama_farm = mf.nama_farm
            WHERE ktb.rn = 1 {$where_sql}
            GROUP BY mf.tipe_ternak, tahun, bulan
        ";
        
        // Query jika tidak ada filter tipe ternak (Tampilkan 'Semua')
        if (empty($filters['tipe_ternak'])) {
             $final_query_body = "
                SELECT 
                    'Semua' AS tipe_ternak,
                    YEAR(ktb.waktu_kunjungan) AS tahun,
                    MONTH(ktb.waktu_kunjungan) AS bulan,
                    (SUM(hfc.kapasitas) - SUM(ktb.jumlah_terisi)) / SUM(hfc.kapasitas) * 100 AS persentase_kosong
                FROM kunjungan_terakhir_bulanan ktb
                JOIN history_farm_capacity hfc ON ktb.nama_farm = hfc.nama_farm
                                            AND ktb.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                JOIN master_farm mf ON ktb.nama_farm = mf.nama_farm
                WHERE ktb.rn = 1 {$where_sql}
                GROUP BY tahun, bulan
            ";
        }

        $full_query = $cte_sql . $final_query_body . " ORDER BY tahun, bulan, tipe_ternak;";

        $query = $this->db->query($full_query);
        return $query->result_array();
    }

    public function get_harga_terbaru_by_jenis($jenis_harga_key)
    {
        log_message('debug', 'Fungsi get_harga_terbaru_by_jenis dipanggil untuk key: ' . $jenis_harga_key); // Log awal

        if ($jenis_harga_key === 'harga_pakan_broiler') {
            $data = $this->get_harga_pakan_broiler_hari_ini();
            log_message('debug', 'Pakan Broiler - Hasil dari get_harga_pakan_broiler_hari_ini: ' . print_r($data, true)); // Log Pakan
            if (!empty($data)) {
                $data['tanggal'] = date('Y-m-d');
            }
            return $data;
        }
        elseif ($jenis_harga_key === 'pakan_komplit_layer') {
            $data = $this->get_harga_pakan_layer_hari_ini(); // Panggil fungsi helper baru
            log_message('debug', 'Pakan Layer - Hasil dari get_harga_pakan_layer_hari_ini: ' . print_r($data, true));
            if (!empty($data)) {
                $data['tanggal'] = date('Y-m-d');
            }
            return $data;
        }

        $today = date('Y-m-d');

        $this->db->select('nilai_rata_rata, jumlah_sumber_data, tanggal');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', $jenis_harga_key);
        $this->db->where('tanggal', $today);
        $harga_hari_ini = $this->db->get()->row_array();
        log_message('debug', 'Query Hari Ini - Hasil untuk ' . $jenis_harga_key . ': ' . print_r($harga_hari_ini, true)); // Log Query Hari Ini

        if (!empty($harga_hari_ini) && isset($harga_hari_ini['nilai_rata_rata']) && $harga_hari_ini['nilai_rata_rata'] > 0) { // Tambah isset() untuk keamanan
            log_message('debug', 'Mengembalikan data HARI INI untuk ' . $jenis_harga_key);
            return $harga_hari_ini;
        }

        $this->db->select('nilai_rata_rata, jumlah_sumber_data, tanggal');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', $jenis_harga_key);
        $this->db->where('tanggal <', $today);
        $this->db->where('nilai_rata_rata IS NOT NULL');
        $this->db->where('nilai_rata_rata >', 0);
        $this->db->order_by('tanggal', 'DESC');
        $this->db->limit(1);
        $harga_terakhir = $this->db->get()->row_array();
        log_message('debug', 'Query Fallback - Hasil untuk ' . $jenis_harga_key . ': ' . print_r($harga_terakhir, true)); // Log Query Fallback

        if (!empty($harga_terakhir)) {
            log_message('debug', 'Mengembalikan data FALLBACK untuk ' . $jenis_harga_key);
            return $harga_terakhir;
        }

        log_message('debug', 'TIDAK ADA data valid ditemukan untuk ' . $jenis_harga_key . ', mengembalikan null.'); // Log Akhir
        return null;
    }

public function get_kondisi_lingkungan_monthly($year, $user_id_login, $selected_areas = []) { 
    $year = (int)$year;
    $where_clauses = []; 

    if ($year != 0) {
        $where_clauses[] = "YEAR(t.waktu_kunjungan) = {$year}";
    }
    
    $where_clauses[] = "TRIM(t.layer_pilihan_pakan_lain) = 'Full CP'";
    
    $current_user = $this->db->select('id_user, group_user, master_area_id, master_sub_area_id')
                             ->where('id_user', $user_id_login)
                             ->get('z_master_user')
                             ->row();

    $group    = $current_user ? $current_user->group_user : '';
    $cur_area = $current_user ? $current_user->master_area_id : null;
    $cur_sub  = $current_user ? $current_user->master_sub_area_id : null;

    // --- LOGIKA FILTER ---
    if ($group === 'surveyor') {
        if (!empty($cur_sub)) {
            $where_clauses[] = "mf.master_sub_area_id = {$cur_sub}";
        } else {
            $where_clauses[] = "1=0";
        }
    } 
    elseif ($group === 'koordinator') {
        if (!empty($cur_area)) {
            $where_clauses[] = "mf.master_area_id = {$cur_area}";
        } else {
            $where_clauses[] = "1=0";
        }
    } 
    else {
        // SKENARIO 3: ADMINISTRATOR
        // [PERBAIKAN DISINI]
        if (!empty($selected_areas)) {
            $area_in_clause = implode(',', array_map('intval', $selected_areas));
            if (!empty($area_in_clause)) {
                $where_clauses[] = "mf.master_area_id IN ({$area_in_clause})";
            }
        } else {
            // JIKA TIDAK ADA AREA DIPILIH -> JANGAN TAMPILKAN APAPUN
            $where_clauses[] = "1=0";
        }
    }

    $base_filters = "";
    if (!empty($where_clauses)) {
        $base_filters = "WHERE " . implode(" AND ", $where_clauses);
    }

    $query_lalat = "
        SELECT
            'lalat' as kategori_chart,
            DATE_FORMAT(t.waktu_kunjungan, '%b') as bulan_tahun, 
            MONTH(t.waktu_kunjungan) as bulan_num, 
            TRIM(t.kondisi_lalat_layer) as nilai,
            TRIM(t.layer_pilihan_pakan_cp) as jenis_pakan,
            COUNT(*) as jumlah
        FROM visiting_p_layer t
        LEFT JOIN master_farm mf ON t.layer_nama_farm = mf.nama_farm
        {$base_filters}
        AND TRIM(t.kondisi_lalat_layer) IS NOT NULL 
        AND TRIM(t.kondisi_lalat_layer) != '' 
        AND TRIM(t.kondisi_lalat_layer) != '-'
        AND TRIM(t.layer_pilihan_pakan_cp) IS NOT NULL
        AND TRIM(t.layer_pilihan_pakan_cp) != ''
        AND TRIM(t.layer_pilihan_pakan_cp) != 'Selain CP'
        GROUP BY bulan_tahun, bulan_num, nilai, jenis_pakan
    ";
    
    $query_kotoran = "
        SELECT
            'kotoran' as kategori_chart,
            DATE_FORMAT(t.waktu_kunjungan, '%b') as bulan_tahun, 
            MONTH(t.waktu_kunjungan) as bulan_num, 
            TRIM(t.kondisi_kotoran_layer) as nilai,
            TRIM(t.layer_pilihan_pakan_cp) as jenis_pakan,
            COUNT(*) as jumlah
        FROM visiting_p_layer t
        LEFT JOIN master_farm mf ON t.layer_nama_farm = mf.nama_farm
        {$base_filters}
        AND TRIM(t.kondisi_kotoran_layer) IS NOT NULL 
        AND TRIM(t.kondisi_kotoran_layer) != '' 
        AND TRIM(t.kondisi_kotoran_layer) != '-'
        AND TRIM(t.layer_pilihan_pakan_cp) IS NOT NULL
        AND TRIM(t.layer_pilihan_pakan_cp) != ''
        AND TRIM(t.layer_pilihan_pakan_cp) != 'Selain CP'
        GROUP BY bulan_tahun, bulan_num, nilai, jenis_pakan
    ";

    $final_query = "{$query_lalat} 
                    UNION ALL 
                    {$query_kotoran}
                    ORDER BY jenis_pakan, bulan_num, kategori_chart, nilai"; 
    
    return $this->db->query($final_query)->result_array();
}


public function get_lingkungan_avg_monthly($year, $user_id_login, $selected_areas = []) { 
    $year = (int)$year;
    $where_clauses = []; 

    if ($year != 0) {
        $where_clauses[] = "YEAR(t.waktu_kunjungan) = {$year}";
    }

    $current_user = $this->db->select('id_user, group_user, master_area_id, master_sub_area_id')
                             ->where('id_user', $user_id_login)
                             ->get('z_master_user')
                             ->row();

    $group    = $current_user ? $current_user->group_user : '';
    $cur_area = $current_user ? $current_user->master_area_id : null;
    $cur_sub  = $current_user ? $current_user->master_sub_area_id : null;

    // --- LOGIKA FILTER ---
    if ($group === 'surveyor') {
        // SKENARIO 1: SURVEYOR
        if (!empty($cur_sub)) {
            $where_clauses[] = "mf.master_sub_area_id = {$cur_sub}";
        } else {
            $where_clauses[] = "1=0"; 
        }
    } 
    elseif ($group === 'koordinator') {
        // SKENARIO 2: KOORDINATOR
        if (!empty($cur_area)) {
            $where_clauses[] = "mf.master_area_id = {$cur_area}";
        } else {
             $where_clauses[] = "1=0";
        }
    } 
    else {
        // SKENARIO 3: ADMINISTRATOR
        // [PERBAIKAN DISINI]
        if (!empty($selected_areas)) {
            $area_in_clause = implode(',', array_map('intval', $selected_areas));
            if (!empty($area_in_clause)) {
                $where_clauses[] = "mf.master_area_id IN ({$area_in_clause})";
            }
        } else {
            // JIKA TIDAK ADA AREA DIPILIH -> JANGAN TAMPILKAN APAPUN
            $where_clauses[] = "1=0"; 
        }
    }

    $numeric_check_suhu = "AND t.suhu_kandang_layer REGEXP '^-?[0-9]+(\.[0-9]+)?$'";
    $numeric_check_kelembapan = "AND t.kelembapan_kandang_layer REGEXP '^-?[0-9]+(\.[0-9]+)?$'";

    $base_filters = "";
    if (!empty($where_clauses)) {
        $base_filters = "WHERE " . implode(" AND ", $where_clauses);
    }

    $query_suhu = "
        SELECT
            'suhu' as kategori_chart,
            DATE_FORMAT(t.waktu_kunjungan, '%b') as bulan_tahun, 
            MONTH(t.waktu_kunjungan) as bulan_num, 
            AVG(CAST(TRIM(t.suhu_kandang_layer) AS DECIMAL(10,2))) as rata_rata
        FROM visiting_p_layer t
        LEFT JOIN master_farm mf ON t.layer_nama_farm = mf.nama_farm 
        {$base_filters} {$numeric_check_suhu}
        GROUP BY bulan_tahun, bulan_num 
    ";

    $query_kelembapan = "
        SELECT
            'kelembapan' as kategori_chart,
            DATE_FORMAT(t.waktu_kunjungan, '%b') as bulan_tahun, 
            MONTH(t.waktu_kunjungan) as bulan_num, 
            AVG(CAST(TRIM(t.kelembapan_kandang_layer) AS DECIMAL(10,2))) as rata_rata
        FROM visiting_p_layer t
        LEFT JOIN master_farm mf ON t.layer_nama_farm = mf.nama_farm
        {$base_filters} {$numeric_check_kelembapan}
        GROUP BY bulan_tahun, bulan_num 
    ";

    $query_heat_index = "
        SELECT
            'heat_index' as kategori_chart,
            DATE_FORMAT(t.waktu_kunjungan, '%b') as bulan_tahun, 
            MONTH(t.waktu_kunjungan) as bulan_num, 
            ((AVG(CAST(TRIM(t.suhu_kandang_layer) AS DECIMAL(10,2))) * 9/5) + 32) + AVG(CAST(TRIM(t.kelembapan_kandang_layer) AS DECIMAL(10,2))) as rata_rata
        FROM visiting_p_layer t
        LEFT JOIN master_farm mf ON t.layer_nama_farm = mf.nama_farm
        {$base_filters} {$numeric_check_suhu} {$numeric_check_kelembapan}
        GROUP BY bulan_tahun, bulan_num 
    ";

    $final_query = "{$query_suhu} 
                    UNION ALL 
                    {$query_kelembapan}
                    UNION ALL
                    {$query_heat_index}
                    ORDER BY bulan_num, kategori_chart"; 
    
    return $this->db->query($final_query)->result_array();
}

    public function get_all_areas() {
        $this->db->select('master_area_id, nama_area');
        $this->db->from('master_area');
        $this->db->order_by('nama_area', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_vip_grower_farms($user_id_filter = null, $area_id_filter = null, $selected_area_ids = []) { 

        $this->db->select('mf.nama_farm, ma.nama_area'); 
        $this->db->from('master_farm mf'); 
        $this->db->join('master_area ma', 'mf.master_area_id = ma.master_area_id', 'left');

        $this->db->where('mf.tipe_ternak', 'Grower');
        $this->db->where('mf.vip_farm', 'Ya');

        if ($user_id_filter) {
            $this->db->where('mf.id_user', $user_id_filter);
        } elseif ($area_id_filter) {
            $this->db->where('mf.master_area_id', $area_id_filter);
        }
        elseif (empty($user_id_filter) && empty($area_id_filter) && !empty($selected_area_ids)) {
             $this->db->where_in('mf.master_area_id', $selected_area_ids);
        }

        $this->db->order_by('ma.nama_area', 'ASC'); 
        $this->db->order_by('mf.nama_farm', 'ASC'); 
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_farm_visit_history($farm_name)
    {
        $this->db->select("
        DATE_FORMAT(waktu_kunjungan, '%d %M %Y, %H:%i') as waktu_kunjungan_formatted,
        waktu_kunjungan as visit_id 
        ");
        $this->db->from('visiting_p_grower');
        $this->db->where('nama_farm', $farm_name);
        $this->db->order_by('waktu_kunjungan', 'DESC'); 
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function get_grower_visit_detail($farm_name, $waktu_kunjungan)
    {
        $this->db->select("
            vpg.nama_farm, 
            (COALESCE(vpg.efektif_terisi_cp_pedaging, 0) + COALESCE(vpg.efektif_terisi_non_cp_pedaging, 0)) as efektif_terisi_pedaging,            vpg.strain_pedaging, 
            DATE_FORMAT(vpg.tanggal_chick_in_pedaging, '%d %M %Y') as tanggal_chick_in_pedaging_formatted, 
            DATE_FORMAT(vpg.waktu_kunjungan, '%d %M %Y, %H:%i') as waktu_kunjungan_formatted, 
            vpg.umur_pedaging, 
            vpg.pencapaian_berat_pedaging, 
            vpg.keseragaman_pedaging, 
            vpg.intake_pedaging, 
            vpg.deplesi_pedaging, 
            mss.berat_badan_strain, 
            mss.keseragaman_strain, 
            mss.konsumsi_pakan_kulmulatif_strain, 
            mss.konsumsi_pakan_strain, 
            mss.kematian_kulmulatif_strain,
            vpg.catatan_pedaging
        ");
        
        $this->db->from('visiting_p_grower vpg'); 

        $this->db->join(
            'master_strain_standard mss', 
            'CASE WHEN MOD(vpg.umur_pedaging, 7) BETWEEN 1 AND 3 THEN FLOOR(vpg.umur_pedaging / 7) WHEN MOD(vpg.umur_pedaging, 7) BETWEEN 4 AND 6 THEN CEILING(vpg.umur_pedaging / 7) ELSE (vpg.umur_pedaging / 7) END = mss.umur_strain', 
            'left', 
            FALSE 
        );

        $this->db->where('vpg.nama_farm', $farm_name); 
        $this->db->where('vpg.waktu_kunjungan', $waktu_kunjungan); 
        $this->db->limit(1);
        
        return $this->db->get()->row_array();
    }

    public function get_user_info_by_id($user_id) {
        $this->db->select('id_user, username, group_user, master_area_id');
        $this->db->from('z_master_user');
        $this->db->where('id_user', (int)$user_id);
        return $this->db->get()->row_array();
    }

    public function get_all_harga_years()
    {
        $this->db->select('DISTINCT(tahun)');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->order_by('tahun', 'DESC');
        return $this->db->get()->result_array();
    }
    
    public function get_farm_current_status($tipe_ternak = null)
    {
        // Cek Farm Petelur: Kunjungan terbaru untuk Petelur (Layer, Arap, Bebek Petelur, Puyuh)
        $sub_query_latest_petelur = "
            SELECT 
                mf.nama_farm,
                mf.tipe_ternak,
                COALESCE(vl.layer_pakai_pakan_cp, va.efektif_terisi_cp_petelur, vb.efektif_terisi_cp_petelur, vp.efektif_terisi_cp_petelur) AS terisi_cp,
                COALESCE(vl.layer_selain_pakan_cp, va.efektif_terisi_non_cp_petelur, vb.efektif_terisi_non_cp_petelur, vp.efektif_terisi_non_cp_petelur) AS terisi_noncp,
                ROW_NUMBER() OVER(PARTITION BY mf.nama_farm ORDER BY COALESCE(vl.waktu_kunjungan, va.waktu_kunjungan, vb.waktu_kunjungan, vp.waktu_kunjungan) DESC) as rn
            FROM master_farm mf
            LEFT JOIN visiting_p_layer vl ON mf.nama_farm = vl.layer_nama_farm AND mf.tipe_ternak = 'Layer'
            LEFT JOIN visiting_p_arap va ON mf.nama_farm = va.nama_farm AND mf.tipe_ternak = 'Arap'
            LEFT JOIN visiting_p_bebek_petelur vb ON mf.nama_farm = vb.nama_farm AND mf.tipe_ternak = 'Bebek Petelur'
            LEFT JOIN visiting_p_puyuh vp ON mf.nama_farm = vp.nama_farm AND mf.tipe_ternak = 'Puyuh'
            WHERE mf.tipe_ternak IN ('Layer', 'Arap', 'Bebek Petelur', 'Puyuh')
        ";

        $sub_query_latest_pedaging = "
        SELECT 
            mf.nama_farm,
            -- Asumsi Bebek Pedaging (vbd) menggunakan BEBEKPEDAGING digabung
            COALESCE(vg.efektif_terisi_cp_pedaging, vbd.efektif_terisi_cp_pedaging) AS terisi_cp,
            COALESCE(vg.efektif_terisi_non_cp_pedaging, vbd.efektif_terisi_non_cp_pedaging) AS terisi_noncp,
            ROW_NUMBER() OVER(PARTITION BY mf.nama_farm ORDER BY COALESCE(vg.waktu_kunjungan, vbd.waktu_kunjungan) DESC) as rn
        FROM master_farm mf
        LEFT JOIN visiting_p_grower vg ON mf.nama_farm = vg.nama_farm AND mf.tipe_ternak = 'Grower'
        LEFT JOIN visiting_p_bebek_pedaging vbd ON mf.nama_farm = vbd.nama_farm AND mf.tipe_ternak = 'Bebek Pedaging'
        WHERE mf.tipe_ternak IN ('Grower', 'Bebek Pedaging')
    ";
        
        $select_fields = "
            mf.nama_farm, 
            mf.tipe_ternak,
            hfc.kapasitas as kapasitas_farm, 
            hfc.start_date,
            
            /* Ambil terisi CP dari kunjungan terakhir (Petelur/Pedaging) */
            COALESCE(lp.terisi_cp, ld.terisi_cp, 0) AS efektif_terisi_cp,
            
            /* Ambil terisi Non-CP dari kunjungan terakhir (Petelur/Pedaging) */
            COALESCE(lp.terisi_noncp, ld.terisi_noncp, 0) AS efektif_terisi_noncp,
            
            /* Total Terisi (Selalu CP + NonCP) */
            (
                COALESCE(lp.terisi_cp, 0) + COALESCE(lp.terisi_noncp, 0) +
                COALESCE(ld.terisi_cp, 0) + COALESCE(ld.terisi_noncp, 0)
            ) AS total_terisi,

            /* Hitung Sisa/Kosong */
            (hfc.kapasitas - (
                COALESCE(lp.terisi_cp, 0) + COALESCE(lp.terisi_noncp, 0) +
                COALESCE(ld.terisi_cp, 0) + COALESCE(ld.terisi_noncp, 0)
            )) AS sisa_kosong
        ";

        $this->db->select($select_fields, FALSE);
        $this->db->from('master_farm mf');
        
        // 1. JOIN Kapasitas saat ini
        $this->db->join('history_farm_capacity hfc', 'mf.master_farm_id = hfc.master_farm_id', 'left');
        $this->db->where('hfc.end_date', '9999-12-31');

        // 2. JOIN ke kunjungan terbaru Petelur (lp)
        $this->db->join("({$sub_query_latest_petelur}) lp", "mf.nama_farm = lp.nama_farm AND lp.rn = 1", 'left');

        // 3. JOIN ke kunjungan terbaru Pedaging (ld)
        $this->db->join("({$sub_query_latest_pedaging}) ld", "mf.nama_farm = ld.nama_farm AND ld.rn = 1", 'left');

        // Terapkan filter Tipe Ternak
        if (!empty($tipe_ternak)) {
            $this->db->where('mf.tipe_ternak', $tipe_ternak);
        } else {
            $this->db->where('mf.tipe_ternak !=', 'Lainnya');
        }
        
        $this->db->order_by('mf.nama_farm', 'ASC');
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    // public function get_crm_surveyor_performance($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
    //     $first_day = date('Y-m-01 00:00:00', strtotime($start_date_str));
    //     $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

    //     $this->db->select('surveyor_id, surveyor_name, COUNT(*) as aktual');
    //     $this->db->from('crm_broiler');
        
    //     // Terapkan filter tanggal
    //     $this->db->where("check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'");
        
    //     // Pastikan data surveyor valid
    //     $this->db->where('surveyor_id IS NOT NULL');
    //     $this->db->where('surveyor_name IS NOT NULL');
    //     $this->db->where('surveyor_name !=', '');

    //     // Terapkan filter (jika ada)
    //     if ($user_id !== null) {
    //         $this->db->where('surveyor_id', $user_id);
    //     }
    //     if ($area_id !== null) {
    //         $this->db->where('region_id', $area_id);
    //     }
        
    //     $this->db->group_by('surveyor_id, surveyor_name');
    //     $this->db->order_by('aktual', 'DESC');
        
    //     return $this->db->get()->result_array();
    // }

    // /**
    //  * [CRM] Mengambil performa area (hanya total aktual).
    //  * $user adalah array user dari SESSION, untuk memfilter berdasarkan hak akses.
    //  */
    // public function get_crm_area_performance($start_date_str, $end_date_str, $user = null) {
    //     $first_day = date('Y-m-01 00:00:00', strtotime($start_date_str));
    //     $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

    //     $this->db->select('region_id, region_name, COUNT(*) as total_aktual');
    //     $this->db->from('crm_broiler');
        
    //     $this->db->where("check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'");
    //     $this->db->where('region_id IS NOT NULL');
    //     $this->db->where('region_name IS NOT NULL');
    //     $this->db->where('region_name !=', '');

    //     // Terapkan filter hak akses berdasarkan $user dari session
    //     if ($user && isset($user['group_user'])) {
            
    //         // 'master_area_id' dari session Anda diasumsikan mappimg ke 'region_id'
    //         if ($user['group_user'] === 'surveyor') {
    //             // Surveyor hanya melihat datanya sendiri
    //             $this->db->where('surveyor_id', $user['id_user']);
                
    //         } elseif ($user['group_user'] === 'koordinator') {
    //             // Koordinator melihat semua data di areanya
    //             if (isset($user['master_area_id'])) {
    //                 $this->db->where('region_id', $user['master_area_id']);
    //             }
    //         }
    //         // Admin (tanpa filter 'group_user' atau 'master_area_id') akan melihat semua
    //     }
        
    //     $this->db->group_by('region_id, region_name');
    //     $this->db->order_by('region_name', 'ASC');
        
    //     return $this->db->get()->result_array();
    // }

    // /**
    //  * [CRM] Mengambil komposisi visit berdasarkan livestock_type.
    //  */
    // public function get_crm_visit_breakdown($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
    //     $first_day = date('Y-m-01 00:00:00', strtotime($start_date_str));
    //     $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

    //     // 'livestock_type' adalah 'kategori'
    //     $this->db->select('livestock_type as kategori, COUNT(*) as jumlah_visit');
    //     $this->db->from('crm_broiler');
        
    //     $this->db->where("check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'");
    //     $this->db->where('livestock_type IS NOT NULL');
    //     $this->db->where('livestock_type !=', '');

    //     if ($user_id !== null) {
    //         $this->db->where('surveyor_id', $user_id);
    //     }
    //     if ($area_id !== null) {
    //         $this->db->where('region_id', $area_id);
    //     }
        
    //     $this->db->group_by('livestock_type');
    //     $this->db->order_by('jumlah_visit', 'DESC');
        
    //     return $this->db->get()->result_array();
    // }

    // /**
    //  * [CRM] Mengambil semua detail log untuk tabel.
    //  * Kolom-kolom di-alias agar cocok dengan tabel HTML di view.
    //  */
    // public function get_crm_all_visit_details($start_date_str, $end_date_str, $user_id = null, $area_id = null) {
    //     $first_day = date('Y-m-01 00:00:00', strtotime($start_date_str));
    //     $last_day_with_time = date('Y-m-t 23:59:59', strtotime($end_date_str));

    //     $this->db->select("
    //         surveyor_name as username,
    //         region_id as master_area_id,
    //         livestock_type as kategori_visit,
    //         check_in as waktu_kunjungan,
    //         farm_name as nama_customer,
    //         capacity as kapasitas,
    //         /* survey_note as tujuan_kunjungan, -- DIHAPUS */
    //         /* NULL as jenis_kasus, -- DIHAPUS */
    //         feed_name as pakan, 
    //         /* NULL as location_address, -- DIHAPUS */
    //         latitude,
    //         longitude
    //         ", FALSE); 


    //     $this->db->from('crm_broiler');
    //     $this->db->where("check_in BETWEEN '{$first_day}' AND '{$last_day_with_time}'");

    //     if ($user_id !== null) {
    //         $this->db->where('surveyor_id', $user_id);
    //     }
    //     if ($area_id !== null) {
    //         $this->db->where('region_id', $area_id);
    //     }
        
    //     $this->db->order_by('check_in', 'DESC');
        
    //     return $this->db->get()->result_array();
    // }
    // /**
    //  * [CRM] Helper untuk AJAX: Mendapatkan info user (terutama region_id)
    //  * dari tabel crm_broiler untuk simulasi $user object.
    //  */
    // public function get_crm_user_info_by_id($user_id) {
    //     // Ambil 1 baris data surveyor untuk mendapatkan region_id-nya
    //     $row = $this->db->select('region_id')
    //                     ->from('crm_broiler')
    //                     ->where('surveyor_id', $user_id)
    //                     ->where('region_id IS NOT NULL')
    //                     ->limit(1)
    //                     ->get()->row_array();
        
    //     $region_id = ($row) ? $row['region_id'] : null;

    //     // Kita asumsikan semua user di tabel ini adalah 'surveyor'
    //     return [
    //         'id_user' => $user_id,
    //         'group_user' => 'surveyor',
    //         'master_area_id' => $region_id // Ini akan digunakan sebagai filter 'region_id'
    //     ];
    // }

}
