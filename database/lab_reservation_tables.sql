-- Create labs table if it doesn't exist
CREATE TABLE IF NOT EXISTS `labs` (
  `lab_id` INT AUTO_INCREMENT PRIMARY KEY,
  `lab_name` VARCHAR(50) NOT NULL,
  `total_pcs` INT NOT NULL DEFAULT 50,
  `location` VARCHAR(100) NOT NULL,
  `status` ENUM('available', 'unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample lab data if the table is empty
INSERT INTO `labs` (`lab_name`, `total_pcs`, `location`, `status`)
SELECT * FROM (
    SELECT 'NET-A' as lab_name, 50 as total_pcs, 'Main Building Floor 1' as location, 'available' as status
    UNION SELECT 'NET-B', 50, 'Main Building Floor 2', 'available'
    UNION SELECT 'NET-C', 50, 'Main Building Floor 3', 'available'
    UNION SELECT 'NET-D', 50, 'Main Building Floor 4', 'available'
) AS temp
WHERE NOT EXISTS (SELECT 1 FROM `labs` LIMIT 1);

-- Create reservations table if it doesn't exist
CREATE TABLE IF NOT EXISTS `reservations` (
  `reservation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `idno` INT NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `lab_name` VARCHAR(50) NOT NULL,
  `pc_number` INT NOT NULL,
  `reservation_date` DATE NOT NULL,
  `time_slot` VARCHAR(50) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`idno`) REFERENCES `users`(`idno`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create an index for faster queries
CREATE INDEX IF NOT EXISTS `idx_reservations_lab_date_slot` 
ON `reservations` (`lab_name`, `reservation_date`, `time_slot`, `status`);

-- Create index for user's reservations
CREATE INDEX IF NOT EXISTS `idx_reservations_user` 
ON `reservations` (`idno`, `reservation_date`, `status`); 