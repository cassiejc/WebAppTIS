<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Visual extends CI_Model {
    
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

    public function get_surveyor_performance($month, $year, $user_id = null, $area_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        $final_query = '';
        $union_query = $this->_build_union_query_for_aktual($month, $year, $user_id, $area_id);
        $base_query_filter = "WHERE u.group_user = 'surveyor'"; 
        $permission_filter = ""; 
        if ($user_id !== null) {
            $permission_filter = "AND u.id_user = {$user_id}";
        } elseif ($area_id !== null) {
            $permission_filter = "AND u.master_area_id = {$area_id}";
        }
        $main_user_filter = $base_query_filter . " " . $permission_filter;

        if ($month != 0 && $year != 0) {
            $first_day_of_month = "'{$year}-{$month}-01'";
            $last_day_of_month = "LAST_DAY({$first_day_of_month})";
            $target_date_condition = "ht.start_date <= {$last_day_of_month} AND ht.end_date >= {$first_day_of_month}";

            $final_query = "
                SELECT 
                    u.id_user,
                    u.username AS surveyor_name,
                    COALESCE(ht.target, 0) AS target,
                    COALESCE(visit_counts.aktual, 0) AS aktual,
                    CASE WHEN COALESCE(ht.target, 0) > 0 THEN (COALESCE(visit_counts.aktual, 0) / ht.target * 100) ELSE 0 END AS achievement_percent
                FROM z_master_user u
                LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                LEFT JOIN history_target ht ON u.id_user = ht.id_user AND {$target_date_condition}
                {$main_user_filter} /* <-- Filter BARU diterapkan di sini */
                ORDER BY achievement_percent DESC, aktual DESC;
            ";
        } else {
            $target_year_filter = ($year != 0) ? "WHERE YEAR(start_date) <= {$year} AND YEAR(end_date) >= {$year}" : "";
            $final_query = "
                SELECT 
                    u.id_user,
                    u.username AS surveyor_name,
                    COALESCE(target_sums.total_target, 0) AS target,
                    COALESCE(visit_counts.aktual, 0) AS aktual,
                    CASE WHEN COALESCE(target_sums.total_target, 0) > 0 THEN (COALESCE(visit_counts.aktual, 0) / target_sums.total_target * 100) ELSE 0 END AS achievement_percent
                FROM z_master_user u
                LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                LEFT JOIN (SELECT id_user, SUM(target) as total_target FROM history_target {$target_year_filter} GROUP BY id_user) as target_sums ON u.id_user = target_sums.id_user
                {$main_user_filter} /* <-- Filter BARU diterapkan di sini juga */
                ORDER BY achievement_percent DESC, aktual DESC;
            ";
        }
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_area_performance($month, $year, $user = null) {
        $month = (int)$month;
        $year = (int)$year;
        $final_query = '';
        
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
        
        $union_query = $this->_build_union_query_for_aktual($month, $year, $user_id_filter, $area_id_filter);
        
        if ($month != 0 && $year != 0) {
            $first_day_of_month = "'{$year}-{$month}-01'";
            $last_day_of_month = "LAST_DAY({$first_day_of_month})";
            $target_date_condition = "mt.start_date <= {$last_day_of_month} AND mt.end_date >= {$first_day_of_month}";

            $final_query = "
                SELECT ma.master_area_id, ma.nama_area, SUM(user_performance.target) AS total_target, SUM(user_performance.aktual) AS total_aktual,
                    CASE WHEN SUM(user_performance.target) > 0 THEN (SUM(user_performance.aktual) / SUM(user_performance.target) * 100) ELSE 0 END AS achievement_percent
                FROM master_area ma
                LEFT JOIN (
                    SELECT u.master_area_id, COALESCE(mt.target, 0) AS target, COALESCE(visit_counts.aktual, 0) AS aktual
                    FROM z_master_user u
                    LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                    LEFT JOIN history_target mt ON u.id_user = mt.id_user AND {$target_date_condition}
                ) AS user_performance ON ma.master_area_id = user_performance.master_area_id
                {$area_filter_sql} /* <-- FILTER TAMPILAN DITERAPKAN DI SINI */
                GROUP BY ma.master_area_id, ma.nama_area ORDER BY ma.nama_area ASC;
            ";
        } else {
            $target_year_filter = ($year != 0) ? "WHERE YEAR(start_date) <= {$year} AND YEAR(end_date) >= {$year}" : "";
            $final_query = "
                SELECT ma_master_area_id, ma.nama_area, SUM(user_performance.target) AS total_target, SUM(user_performance.aktual) AS total_aktual,
                    CASE WHEN SUM(user_performance.target) > 0 THEN (SUM(user_performance.aktual) / SUM(user_performance.target) * 100) ELSE 0 END AS achievement_percent
                FROM master_area ma
                LEFT JOIN (
                    SELECT u.master_area_id, COALESCE(target_sums.total_target, 0) AS target, COALESCE(visit_counts.aktual, 0) AS aktual
                    FROM z_master_user u
                    LEFT JOIN (SELECT id_user, COUNT(*) as aktual FROM ({$union_query}) as all_visits GROUP BY id_user) as visit_counts ON u.id_user = visit_counts.id_user
                    LEFT JOIN (SELECT id_user, SUM(target) as total_target FROM history_target {$target_year_filter} GROUP BY id_user) as target_sums ON u.id_user = target_sums.id_user
                ) AS user_performance ON ma.master_area_id = user_performance.master_area_id
                {$area_filter_sql} /* <-- FILTER TAMPILAN DITERAPKAN DI SINI JUGA */
                GROUP BY ma.master_area_id, ma.nama_area ORDER BY ma.nama_area ASC;
            ";
        }

        return $this->db->query($final_query)->result_array();
    }

    public function get_visit_breakdown($month, $year, $user_id = null, $area_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        $sub_queries = [];
        $where_clause = '';

        if ($month != 0 && $year != 0) {
            $where_clause = "WHERE MONTH(t.waktu_kunjungan) = {$month} AND YEAR(t.waktu_kunjungan) = {$year}";
        }
        
        $user_filter_sql = '';
        if ($user_id !== null) {
            $user_filter_sql = $where_clause ? " AND t.id_user = {$user_id}" : "WHERE t.id_user = {$user_id}";
        }
        
        $area_filter_sql = '';
        if ($area_id !== null) {
            $area_filter_sql = ($where_clause || $user_filter_sql) ? " AND u.master_area_id = {$area_id}" : "WHERE u.master_area_id = {$area_id}";
        }
        
        foreach ($this->table_mapping as $kategori => $nama_tabel) {
            
            $sub_queries[] = "
                SELECT '{$kategori}' as kategori 
                FROM {$nama_tabel} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user
                {$where_clause} {$user_filter_sql} {$area_filter_sql}
            ";
        }
        
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

    public function get_all_visit_details($month, $year, $user_id = null, $area_id = null) {
        $month = (int)$month;
        $year = (int)$year;
        
        $where_clause = '';
        if ($month != 0 && $year != 0) {
            $where_clause = "WHERE MONTH(t.waktu_kunjungan) = {$month} AND YEAR(t.waktu_kunjungan) = {$year}";
        }
        
        $user_filter_sql = '';
        if ($user_id !== null) {
            $user_filter_sql = $where_clause ? " AND t.id_user = {$user_id}" : "WHERE t.id_user = {$user_id}";
        }

        $area_filter_sql = '';
        if ($area_id !== null) {
            $area_filter_sql = ($where_clause || $user_filter_sql) ? " AND u.master_area_id = {$area_id}" : "WHERE u.master_area_id = {$area_id}";
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
                case 'visiting_p_pedaging':
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
                        // Jika 'nama_farm' tidak ada, cek 'layer_nama_farm'
                        $customer_select_sql = "t.layer_nama_farm as nama_customer";
                    }
                    // Jika keduanya tidak ada, $customer_select_sql akan tetap "NULL as nama_customer"
                    // seperti yang sudah di-set di awal.
                    break;
            }
            $select_parts[] = $customer_select_sql; 
            $kapasitas_select_sql = "NULL as kapasitas"; 

            if (in_array('nama_farm', $existing_columns)) {
                // Jika 'nama_farm' ada
                $kapasitas_select_sql = "
                    (
                        SELECT hfc.kapasitas 
                        FROM history_farm_capacity hfc
                        WHERE hfc.nama_farm = t.nama_farm 
                        AND t.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                        LIMIT 1
                    ) as kapasitas
                ";
            } elseif (in_array('layer_nama_farm', $existing_columns)) {
                // Jika 'nama_farm' tidak ada, tapi 'layer_nama_farm' ada
                $kapasitas_select_sql = "
                    (
                        SELECT hfc.kapasitas 
                        FROM history_farm_capacity hfc
                        WHERE hfc.nama_farm = t.layer_nama_farm 
                        AND t.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                        LIMIT 1
                    ) as kapasitas
                ";
            }

            $select_parts[] = $kapasitas_select_sql;
            $select_string = implode(', ', $select_parts);
            $sub_queries[] = "
                SELECT {$select_string}
                FROM {$nama_tabel} t
                LEFT JOIN z_master_user u ON t.id_user = u.id_user
                {$where_clause} {$user_filter_sql} {$area_filter_sql}
            ";
        }
        
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT * FROM ({$union_query}) as semua_visit
            ORDER BY waktu_kunjungan DESC
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_kasus_breakdown_stacked($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = ['visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer', 'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(waktu_kunjungan) = {$year}" : "";
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT 
                    DATE_FORMAT(waktu_kunjungan, '%b %Y') as bulan_tahun,
                    CASE WHEN INSTR(jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(jenis_kasus, ':', 1) ELSE jenis_kasus END as kategori_kasus
                FROM {$table}
                WHERE jenis_kasus IS NOT NULL AND jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql}
            ";
        }
        $union_query = implode(' UNION ALL ', $sub_queries);

        $base_count_query = "
            SELECT 
                bulan_tahun, 
                kategori_kasus, 
                COUNT(*) as jumlah
            FROM ({$union_query}) as semua_kasus
            WHERE kategori_kasus IS NOT NULL AND kategori_kasus != '' AND bulan_tahun IS NOT NULL
            GROUP BY bulan_tahun, kategori_kasus
        ";

        $final_query = "
            SELECT
                bulan_tahun,
                kategori_kasus,
                jumlah,
                (jumlah * 100.0 / SUM(jumlah) OVER (PARTITION BY bulan_tahun)) as persentase
            FROM ({$base_count_query}) as counts_per_kasus
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

        foreach ($peternakan_tables as $table) {
            $sub_queries[] = "
                SELECT t.id_user, CASE WHEN INSTR(t.jenis_kasus, ':') > 0 THEN SUBSTRING_INDEX(t.jenis_kasus, ':', 1) ELSE t.jenis_kasus END as kategori_kasus
                FROM {$table} t
                WHERE t.jenis_kasus IS NOT NULL AND t.jenis_kasus != '-' {$year_filter_sql} {$user_filter_sql}
            ";
        }
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT ma.nama_area, semua_kasus.kategori_kasus, COUNT(*) as jumlah
            FROM ({$union_query}) as semua_kasus
            JOIN z_master_user u ON semua_kasus.id_user = u.id_user
            JOIN master_area ma ON u.master_area_id = ma.master_area_id
            WHERE semua_kasus.kategori_kasus IS NOT NULL AND semua_kasus.kategori_kasus != ''
            GROUP BY ma.nama_area, semua_kasus.kategori_kasus
            ORDER BY ma.nama_area, semua_kasus.kategori_kasus;
        ";
        
        return $this->db->query($final_query)->result_array();
    }

    public function get_kasus_detail_list($year, $user_id = null) {
        $year = (int)$year;
        $peternakan_tables = [
            'visiting_p_grower', 'visiting_p_bebek_pedaging', 'visiting_p_layer',
            'visiting_p_bebek_petelur', 'visiting_p_puyuh', 'visiting_p_arap', 'visiting_p_lainnya'
        ];
        $sub_queries = [];
        
        $year_filter_sql = ($year != 0) ? "AND YEAR(waktu_kunjungan) = {$year}" : "";
        
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";

        foreach ($peternakan_tables as $table) {
            $farm_column_selection = 'nama_farm';
            if ($table === 'visiting_p_layer') {
                $farm_column_selection = 'layer_nama_farm AS nama_farm';
            }

            $sub_queries[] = "
                SELECT 
                    waktu_kunjungan,
                    {$farm_column_selection},
                    jenis_kasus
                FROM {$table}
                WHERE 
                    jenis_kasus IS NOT NULL 
                    AND jenis_kasus != '-'
                    {$year_filter_sql}
                    {$user_filter_sql}
            ";
        }
        
        $union_query = implode(' UNION ALL ', $sub_queries);

        $final_query = "
            SELECT * FROM ({$union_query}) as semua_kasus
            ORDER BY waktu_kunjungan DESC
        ";
        
        return $this->db->query($final_query)->result_array();
    }
    
    private function _build_union_query_for_aktual($month, $year, $user_id = null) {
        $sub_queries = [];
        
        $date_filter_sql = '';
        $date_filter_created_at = '';
        if ($month != 0 && $year != 0) {
            $date_filter_sql = "AND MONTH(waktu_kunjungan) = {$month} AND YEAR(waktu_kunjungan) = {$year}";
            $date_filter_created_at = "WHERE MONTH(created_at) = {$month} AND YEAR(created_at) = {$year}";
        } elseif ($month == 0 && $year != 0) {
            $date_filter_sql = "AND YEAR(waktu_kunjungan) = {$year}";
            $date_filter_created_at = "WHERE YEAR(created_at) = {$year}";
        }
        
        $user_filter_sql = ($user_id !== null) ? "AND id_user = {$user_id}" : "";
        $user_filter_created_at = ($user_id !== null) ? ($date_filter_created_at ? " AND id_user = {$user_id}" : "WHERE id_user = {$user_id}") : "";

        foreach ($this->table_mapping as $table) {
            $sub_queries[] = "SELECT id_user FROM {$table} WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        }
        
        $sub_queries[] = "SELECT id_user FROM seminar WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        $sub_queries[] = "SELECT id_user FROM sample_form WHERE id_user IS NOT NULL AND id_user != 0 {$date_filter_sql} {$user_filter_sql}";
        $sub_queries[] = "SELECT id_user FROM master_farm {$date_filter_created_at} {$user_filter_created_at}";
        $sub_queries[] = "SELECT id_user FROM master_subagen {$date_filter_created_at} {$user_filter_created_at}";
        $sub_queries[] = "SELECT id_user FROM master_kemitraan {$date_filter_created_at} {$user_filter_created_at}";
        
        return implode(' UNION ALL ', $sub_queries);
    }

    // public function get_seminar_count_by_month($month, $year, $user_id = null) {
    //     $this->db->reset_query();
    //     if ($month != 0 && $year != 0) {
    //         $this->db->where('MONTH(waktu_kunjungan)', $month);
    //         $this->db->where('YEAR(waktu_kunjungan)', $year);
    //     } elseif ($month == 0 && $year != 0) {
    //         $this->db->where('YEAR(waktu_kunjungan)', $year);
    //     }
    //     if ($user_id !== null) {
    //         $this->db->where('id_user', $user_id);
    //     }
    //     return $this->db->count_all_results('seminar'); 
    // }

    // public function get_sample_count_by_month($month, $year, $user_id = null) {
    //     $this->db->reset_query();
    //     if ($month != 0 && $year != 0) {
    //         $this->db->where('MONTH(waktu_kunjungan)', $month);
    //         $this->db->where('YEAR(waktu_kunjungan)', $year);
    //     } elseif ($month == 0 && $year != 0) {
    //         $this->db->where('YEAR(waktu_kunjungan)', $year);
    //     }
    //     if ($user_id !== null) {
    //         $this->db->where('id_user', $user_id);
    //     }
    //     return $this->db->count_all_results('sample_form'); 
    // }
    
    // public function get_new_customer_count_by_month($month, $year, $user_id = null) {
    //     $total_new = 0;
    //     $tables = ['master_farm', 'master_subagen', 'master_kemitraan'];
        
    //     foreach ($tables as $table) {
    //         $this->db->reset_query();
    //         if ($month != 0 && $year != 0) {
    //             $this->db->where('MONTH(created_at)', $month);
    //             $this->db->where('YEAR(created_at)', $year);
    //         } elseif ($month == 0 && $year != 0) {
    //             $this->db->where('YEAR(created_at)', $year);
    //         }
    //         if ($user_id !== null) {
    //             $this->db->where('id_user', $user_id);
    //         }
    //         $total_new += $this->db->count_all_results($table);
    //     }
    //     return $total_new;
    // }

    // GANTI FUNGSI INI DI M_Visual.php
    public function get_seminar_count_by_month($month, $year, $user_id = null, $area_id = null) {
        $this->db->reset_query();
        $this->db->from('seminar t'); // 't' alias

        if ($month != 0 && $year != 0) {
            $this->db->where('MONTH(t.waktu_kunjungan)', $month);
            $this->db->where('YEAR(t.waktu_kunjungan)', $year);
        } elseif ($month == 0 && $year != 0) {
            $this->db->where('YEAR(t.waktu_kunjungan)', $year);
        }

        if ($user_id !== null) {
            $this->db->where('t.id_user', $user_id);
        }
        
        // [BARU] Tambahkan filter area jika user_id null dan area_id ada
        if ($area_id !== null && $user_id === null) {
            $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
            $this->db->where('u.master_area_id', $area_id);
        }

        return $this->db->count_all_results(); 
    }

    // GANTI FUNGSI INI DI M_Visual.php
    public function get_sample_count_by_month($month, $year, $user_id = null, $area_id = null) {
        $this->db->reset_query();
        $this->db->from('sample_form t'); // 't' alias

        if ($month != 0 && $year != 0) {
            $this->db->where('MONTH(t.waktu_kunjungan)', $month);
            $this->db->where('YEAR(t.waktu_kunjungan)', $year);
        } elseif ($month == 0 && $year != 0) {
            $this->db->where('YEAR(t.waktu_kunjungan)', $year);
        }

        if ($user_id !== null) {
            $this->db->where('t.id_user', $user_id);
        }
        
        // [BARU] Tambahkan filter area jika user_id null dan area_id ada
        if ($area_id !== null && $user_id === null) {
            $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
            $this->db->where('u.master_area_id', $area_id);
        }

        return $this->db->count_all_results(); 
    }
    
    // GANTI FUNGSI INI DI M_Visual.php
    public function get_new_customer_count_by_month($month, $year, $user_id = null, $area_id = null) {
        $total_new = 0;
        $tables = ['master_farm', 'master_subagen', 'master_kemitraan'];
        
        foreach ($tables as $table) {
            $this->db->reset_query();
            $this->db->from("{$table} t"); // 't' alias

            if ($month != 0 && $year != 0) {
                $this->db->where('MONTH(t.created_at)', $month);
                $this->db->where('YEAR(t.created_at)', $year);
            } elseif ($month == 0 && $year != 0) {
                $this->db->where('YEAR(t.created_at)', $year);
            }

            if ($user_id !== null) {
                $this->db->where('t.id_user', $user_id);
            }
            
            // [BARU] Tambahkan filter area jika user_id null dan area_id ada
            if ($area_id !== null && $user_id === null) {
                // Asumsi semua tabel ini punya relasi ke z_master_user via id_user
                $this->db->join('z_master_user u', 't.id_user = u.id_user', 'left');
                $this->db->where('u.master_area_id', $area_id);
            }
            
            $total_new += $this->db->count_all_results();
        }
        return $total_new;
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
     * Mengambil data harga jual telur layer bulanan untuk 12 bulan terakhir.
     * @return array
     */
    public function get_harga_telur_bulanan_chart($year)
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        
        if ($year !== null) {
            $this->db->where('tahun', $year);
            $this->db->order_by('bulan', 'ASC');
        } else {
            $this->db->order_by('tahun', 'ASC');
            $this->db->order_by('bulan', 'ASC');
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Mengambil data harga jual telur layer tahunan.
     * @return array
     */
    public function get_harga_telur_tahunan_chart()
    {
        $this->db->select('tahun, nilai_rata_rata');
        $this->db->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'harga_jual_telur_layer');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil harga rata-rata telur layer HARI INI saja.
     * @return array|null
     */
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

    public function get_harga_jagung_harian_chart() {
        $this->db->select('tanggal, nilai_rata_rata');
        $this->db->from('harga_rata_rata_harian');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->where('tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_harga_jagung_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_jagung'); 
        $this->db->order_by('tahun', 'DESC');
        $this->db->order_by('bulan', 'DESC');
        $this->db->limit(12);
        $query = $this->db->get();
        return array_reverse($query->result_array());
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

    public function get_harga_katul_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_katul'); 
        $this->db->order_by('tahun', 'DESC');
        $this->db->order_by('bulan', 'DESC');
        $this->db->limit(12);
        $query = $this->db->get();
        return array_reverse($query->result_array());
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

    public function get_harga_afkir_bulanan_chart()
    {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_afkir');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_telur_puyuh_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_puyuh');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_telur_bebek_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_telur_bebek');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_bebek_pedaging_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_bebek_pedaging');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_live_bird_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_live_bird');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_pakan_broiler_bulanan_chart() { return []; }
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
    
    public function get_harga_doc_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata');
        $this->db->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_doc');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_konsentrat_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'harga_konsentrat_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_hpp_konsentrat_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_konsentrat_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_hpp_komplit_layer_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_komplit_layer');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_cost_komplit_broiler_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'cost_komplit_broiler');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
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
    public function get_harga_hpp_broiler_bulanan_chart() {
        $this->db->select('tahun, bulan, nilai_rata_rata')->from('harga_rata_rata_bulanan');
        $this->db->where('jenis_harga', 'hpp_broiler');
        $this->db->order_by('tahun DESC, bulan DESC')->limit(12);
        return array_reverse($this->db->get()->result_array());
    }
    public function get_harga_hpp_broiler_tahunan_chart() {
        $this->db->select('tahun, nilai_rata_rata')->from('harga_rata_rata_tahunan');
        $this->db->where('jenis_harga', 'hpp_broiler')->order_by('tahun', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Helper function to get all unique livestock types from the farm master.
     * This is used to populate the filter dropdown in the view.
     * @return array
     */
    public function get_all_tipe_ternak()
    {
        $this->db->select('tipe_ternak');
        $this->db->from('master_farm');
        $this->db->distinct();
        $this->db->order_by('tipe_ternak', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil data persentase kandang kosong bulanan per tipe ternak.
     * VERSI BARU: Menggunakan 'nama_farm' sebagai kunci join dan kapasitas dinamis.
     * @param array $filters Filter yang diterapkan.
     * @return array Data yang siap untuk diolah menjadi chart.
     */
    public function get_monthly_vacancy_percentage($filters = [])
    {
        $cte_sql = "
            WITH 
            semua_kunjungan AS (
                /* ... (isi CTE sama persis) ... */
                SELECT layer_nama_farm AS nama_farm, waktu_kunjungan, (layer_pakai_pakan_cp + layer_selain_pakan_cp) AS jumlah_terisi FROM visiting_p_layer
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_arap
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_bebek_petelur
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_petelur AS jumlah_terisi FROM visiting_p_puyuh
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_pedaging AS jumlah_terisi FROM visiting_p_grower
                UNION ALL
                SELECT nama_farm, waktu_kunjungan, efektif_terisi_pedaging AS jumlah_terisi FROM visiting_p_bebek_pedaging
            ),
            kunjungan_terakhir_bulanan AS (
                /* ... (isi CTE sama persis) ... */
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

        $date_clauses = []; 
        if (!empty($filters['start_month'])) {
            $start_date = date('Y-m-01', strtotime($filters['start_month']));
            $date_clauses[] = "ktb.waktu_kunjungan >= " . $this->db->escape($start_date);
        }
        if (!empty($filters['end_month'])) {
            $end_date = date('Y-m-t', strtotime($filters['end_month']));
            $date_clauses[] = "ktb.waktu_kunjungan <= " . $this->db->escape($end_date);
        }

        $final_query_body = '';
        if (empty($filters['tipe_ternak'])) {
            $where_sql_gabungan = !empty($date_clauses) ? "AND " . implode(" AND ", $date_clauses) : '';

            $final_query_body = "
                SELECT 
                    'Semua' AS tipe_ternak, -- Label manual
                    YEAR(ktb.waktu_kunjungan) AS tahun,
                    MONTH(ktb.waktu_kunjungan) AS bulan,
                    (SUM(hfc.kapasitas) - SUM(ktb.jumlah_terisi)) / SUM(hfc.kapasitas) * 100 AS persentase_kosong
                FROM kunjungan_terakhir_bulanan ktb
                JOIN history_farm_capacity hfc ON ktb.nama_farm = hfc.nama_farm
                                            AND ktb.waktu_kunjungan BETWEEN hfc.start_date AND hfc.end_date
                JOIN master_farm mf ON ktb.nama_farm = mf.nama_farm
                WHERE ktb.rn = 1 {$where_sql_gabungan} -- Filter (HANYA tanggal)
                GROUP BY tahun, bulan -- Group by hanya tahun dan bulan
            ";

        } else {

            $breakdown_where_clauses = $date_clauses; 
        
            $breakdown_where_clauses[] = "mf.tipe_ternak = " . $this->db->escape($filters['tipe_ternak']);
            
            $where_sql_breakdown = !empty($breakdown_where_clauses) ? "AND " . implode(" AND ", $breakdown_where_clauses) : '';

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
                WHERE ktb.rn = 1 {$where_sql_breakdown} -- Filter (tanggal + tipe_ternak)
                GROUP BY mf.tipe_ternak, tahun, bulan
            ";
        }

        $full_query = $cte_sql . $final_query_body . " ORDER BY tahun, bulan, tipe_ternak;";

        $query = $this->db->query($full_query);
        return $query->result_array();
    }

    /**
     * Mengambil harga rata-rata terbaru untuk jenis harga tertentu.
     * Prioritas 1: Ambil data hari ini (tanggal = CURDATE()).
     * Prioritas 2: Jika hari ini kosong (atau 0), ambil data valid terakhir SEBELUM hari ini.
     *
     * @param string $jenis_harga_key Kunci jenis harga (e.g., 'harga_jual_telur_layer', 'harga_jagung')
     * @return array|null Data harga (termasuk 'tanggal') atau null jika tidak ada data sama sekali.
     */
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

    /**
     * Mengambil data breakdown BULANAN untuk kondisi lalat dan kotoran.
     * @param int $year
     * @param int|null $user_id
     * @param int|null $area_id
     * @return array
     */
    public function get_kondisi_lingkungan_monthly($year, $user_id = null, $area_id = null, $pakan_filter = []) {        $year = (int)$year;
        $base_filters = ''; 

        if ($year != 0) {
            $base_filters = "WHERE YEAR(t.waktu_kunjungan) = {$year}";
        }
        
        if ($user_id !== null) {
            $base_filters .= ($base_filters ? " AND " : "WHERE ") . "t.id_user = {$user_id}";
        }
        
        if ($area_id !== null && $user_id === null) { 
            $base_filters .= ($base_filters ? " AND " : "WHERE ") . "u.master_area_id = {$area_id}";
        }
        if (!empty($pakan_filter) && is_array($pakan_filter)) {
            $escaped_pakan = [];
            foreach ($pakan_filter as $pakan) {
                if(!empty(trim($pakan))) {
                    $escaped_pakan[] = $this->db->escape(trim($pakan));
                }
            }
            
            if (!empty($escaped_pakan)) {
                $pakan_in_clause = implode(',', $escaped_pakan);

                $pakan_filter_sql = "
                    (TRIM(t.layer_pilihan_pakan_cp) IN ({$pakan_in_clause}) 
                     OR TRIM(t.layer_pilihan_pakan_lain) IN ({$pakan_in_clause}))
                ";
                
                $base_filters .= ($base_filters ? " AND " : "WHERE ") . $pakan_filter_sql;
            }
        }
        
        $query_lalat = "
            SELECT
                'lalat' as kategori_chart,
                DATE_FORMAT(t.waktu_kunjungan, '%b %Y') as bulan_tahun,
                TRIM(t.kondisi_lalat_layer) as nilai,
                COUNT(*) as jumlah
            FROM visiting_p_layer t
            LEFT JOIN z_master_user u ON t.id_user = u.id_user
            {$base_filters}
            AND TRIM(t.kondisi_lalat_layer) IS NOT NULL 
            AND TRIM(t.kondisi_lalat_layer) != '' 
            AND TRIM(t.kondisi_lalat_layer) != '-'
            GROUP BY bulan_tahun, nilai
        ";
        
        $query_kotoran = "
            SELECT
                'kotoran' as kategori_chart,
                DATE_FORMAT(t.waktu_kunjungan, '%b %Y') as bulan_tahun,
                TRIM(t.kondisi_kotoran_layer) as nilai,
                COUNT(*) as jumlah
            FROM visiting_p_layer t
            LEFT JOIN z_master_user u ON t.id_user = u.id_user
            {$base_filters}
            AND TRIM(t.kondisi_kotoran_layer) IS NOT NULL 
            AND TRIM(t.kondisi_kotoran_layer) != '' 
            AND TRIM(t.kondisi_kotoran_layer) != '-'
            GROUP BY bulan_tahun, nilai
        ";

        $final_query = "{$query_lalat} 
                        UNION ALL 
                        {$query_kotoran} 
                        ORDER BY STR_TO_DATE(CONCAT('01 ', bulan_tahun), '%d %b %Y'), kategori_chart, nilai";
        
        return $this->db->query($final_query)->result_array();
    }

    /**
     * Mengambil semua opsi pakan unik dari tabel visiting_p_layer
     * @return array
     */
    public function get_all_pakan_layer_options() {
        $query_cp = "
            SELECT TRIM(layer_pilihan_pakan_cp) as pakan 
            FROM visiting_p_layer 
            WHERE TRIM(layer_pilihan_pakan_cp) IS NOT NULL 
              AND TRIM(layer_pilihan_pakan_cp) != '' 
              AND TRIM(layer_pilihan_pakan_cp) != '-'
        ";
        
        $query_lain = "
            SELECT TRIM(layer_pilihan_pakan_lain) as pakan 
            FROM visiting_p_layer 
            WHERE TRIM(layer_pilihan_pakan_lain) IS NOT NULL 
              AND TRIM(layer_pilihan_pakan_lain) != '' 
              AND TRIM(layer_pilihan_pakan_lain) != '-'
        ";
        
        $final_query = "{$query_cp} UNION {$query_lain} ORDER BY pakan ASC";
        
        $result = $this->db->query($final_query)->result_array();
        
        return array_filter($result, function($item) {
            return !empty($item['pakan']);
        });
    }
    
    /**
     * BARU: Mengambil semua area dari master_area
     * @return array
     */
    public function get_all_areas() {
        $this->db->select('master_area_id, nama_area');
        $this->db->from('master_area');
        $this->db->order_by('nama_area', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil daftar farm yang merupakan VIP dan tipe ternak Grower.
     * Menerapkan filter berdasarkan peran pengguna (surveyor/koordinator).
     * BARU: Menambahkan filter area spesifik untuk Admin.
     * @param int|null $user_id_filter ID user jika login sebagai surveyor
     * @param int|null $area_id_filter ID area jika login sebagai koordinator
     * @param array $selected_area_ids Array ID area yang dipilih dari filter (HANYA untuk Admin)
     * @return array
     */
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

    /**
     * Mengambil riwayat kunjungan (waktu_kunjungan) untuk satu farm spesifik
     * dari tabel visiting_p_grower.
     */
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
            vpg.efektif_terisi_pedaging, 
            vpg.strain_pedaging, 
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

    /**
     * BARU: Mengambil info dasar user berdasarkan ID
     * @return array
     */
    public function get_user_info_by_id($user_id) {
        $this->db->select('id_user, username, group_user, master_area_id');
        $this->db->from('z_master_user');
        $this->db->where('id_user', (int)$user_id);
        return $this->db->get()->row_array();
    }
}
