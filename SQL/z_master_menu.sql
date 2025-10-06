-- Adminer 4.3.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `z_master_menu`;
CREATE TABLE `z_master_menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `caption` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `menu_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `src` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `urutan` float NOT NULL,
  `submenu` int NOT NULL,
  `has_sub` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `icon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `z_master_menu` (`id_menu`, `caption`, `menu_type`, `src`, `urutan`, `submenu`, `has_sub`, `icon`) VALUES
(53,	'Seminar',	'head',	'SeminarController',	6,	0,	'y',	'fas fa-chalkboard-teacher'),
(54,	'Form Seminar',	'input',	'FormSeminarController',	7,	53,	'n',	''),
(55,	'Sample',	'head',	'SampleController',	6,	0,	'y',	'fas fa-vial'),
(56,	'Form Sample',	'input',	'FormSampleController',	6,	55,	'n',	''),
(57,	'Tambah Data Baru',	'head',	'MasterTambahDataBaruController',	6,	0,	'y',	'fas fa-plus'),
(58,	'Form Tambah Data Baru',	'input',	'Master_Tambah_Data_Baru_Controller',	1,	57,	'n',	''),
(59,	'Visiting',	'head',	'Visiting',	6,	0,	'y',	'fas fa-compass'),
(60,	'Sub Agen',	'input',	'Visiting_Controller/Subagen',	6,	59,	'n',	''),
(61,	'Kemitraan',	'input',	'Visiting_Controller/Kemitraan',	6,	59,	'n',	''),
(62,	'Peternak',	'input',	'Visiting_Controller/Peternak',	6,	59,	'n',	''),
(63,	'Agen',	'input',	'Visiting_Controller/Agen',	6,	59,	'n',	''),
(64,	'Kantor',	'input',	'Visiting_Controller/Kantor',	6,	59,	'n',	''),
(65,	'Koordinasi',	'input',	'Visiting_Controller/Koordinasi',	6,	59,	'n',	''),
(71,	'Agen',	'input',	'Tambah_Data_Baru_Master_Controller/Agen',	1,	57,	'n',	''),
(72,	'Strain',	'input',	'Tambah_Data_Baru_Master_Controller/Strain',	1,	57,	'n',	''),
(73,	'Pakan',	'input',	'Tambah_Data_Baru_Master_Controller/Pakan',	1,	57,	'n',	''),
(74,	'Lokasi Baru',	'input',	'Tambah_Data_Baru_Master_Controller/Lokasi_Baru',	1,	57,	'n',	''),
(75,	'Peternak',	'input',	'Tambah_Data_Baru_Master_Controller/Peternak',	1,	57,	'n',	''),
(76,	'Farm',	'input',	'Tambah_Data_Baru_Master_Controller/Farm',	1,	57,	'n',	''),
(77,	'Kemitraan',	'input',	'Tambah_Data_Baru_Master_Controller/Kemitraan',	1,	57,	'n',	''),
(81,	'Sub Agen',	'input',	'Tambah_Data_Baru_Master_Controller/Subagen',	1,	57,	'n',	'');

-- 2025-10-06 03:01:35
