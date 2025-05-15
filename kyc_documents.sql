CREATE TABLE `kyc_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `document_type` VARCHAR(50),
  `document_number` VARCHAR(100),
  `document_file` VARCHAR(255),
  `status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  `verified_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
