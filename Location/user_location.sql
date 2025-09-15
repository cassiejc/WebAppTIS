-- Create user_location table
CREATE TABLE IF NOT EXISTS `user_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`id_user`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_user_location_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Add indexes for better performance
CREATE INDEX `idx_user_location_coords` ON `user_location` (`latitude`, `longitude`);
CREATE INDEX `idx_user_location_user_date` ON `user_location` (`id_user`, `created_at` DESC);
