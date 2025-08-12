-- Momilove52 Database Setup
-- Database: outsrglr_mom
-- User: outsrglr_mom
-- Password: born#1852Niptuck

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `outsrglr_mom` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `outsrglr_mom`;

-- Users table (patients, caregivers, family members)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('patient', 'caregiver', 'family', 'admin') NOT NULL DEFAULT 'patient',
  `phone` varchar(20),
  `date_of_birth` date,
  `emergency_contact` text,
  `medical_history` text,
  `allergies` text,
  `avatar_url` varchar(255),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily check-ins table
CREATE TABLE `daily_checkins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `checkin_date` date NOT NULL,
  `mood` tinyint(1) COMMENT '1=bad, 2=okay, 3=good, 4=great',
  `energy_level` tinyint(2) COMMENT '0-10 scale',
  `pain_level` tinyint(2) COMMENT '0-10 scale',
  `stress_level` tinyint(2) COMMENT '0-10 scale (caregivers)',
  `care_load` tinyint(2) COMMENT 'Number of tasks (caregivers)',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_date` (`user_id`, `checkin_date`),
  KEY `idx_checkin_date` (`checkin_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medications table
CREATE TABLE `medications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `dosage` varchar(50),
  `frequency` varchar(100),
  `prescribing_doctor` varchar(100),
  `start_date` date,
  `end_date` date,
  `instructions` text,
  `side_effects` text,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_patient_active` (`patient_id`, `is_active`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medication logs table
CREATE TABLE `medication_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medication_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `taken_at` timestamp NOT NULL,
  `status` enum('taken', 'skipped', 'missed') NOT NULL,
  `notes` text,
  `administered_by` int(11) COMMENT 'User ID of who administered',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`medication_id`) REFERENCES `medications`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`administered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_taken_at` (`taken_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks table
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `priority` enum('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `status` enum('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
  `due_date` timestamp NULL,
  `completed_at` timestamp NULL,
  `category` varchar(50) COMMENT 'medical, personal_care, household, etc.',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments table
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `doctor_name` varchar(100),
  `location` varchar(255),
  `appointment_date` timestamp NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `type` varchar(50) COMMENT 'routine, specialist, emergency, etc.',
  `status` enum('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_appointment_date` (`appointment_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Symptoms tracking table
CREATE TABLE `symptoms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `symptom_name` varchar(100) NOT NULL,
  `severity` tinyint(2) COMMENT '1-10 scale',
  `description` text,
  `body_location` varchar(100),
  `onset_date` timestamp NOT NULL,
  `resolved_date` timestamp NULL,
  `triggers` text,
  `treatment_notes` text,
  `is_chronic` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_onset_date` (`onset_date`),
  KEY `idx_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vitals tracking table
CREATE TABLE `vitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `measurement_date` timestamp NOT NULL,
  `blood_pressure_systolic` int(11),
  `blood_pressure_diastolic` int(11),
  `heart_rate` int(11),
  `temperature` decimal(4,1),
  `oxygen_saturation` int(11),
  `weight` decimal(5,2),
  `height` decimal(5,2),
  `glucose_level` int(11),
  `notes` text,
  `measured_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`measured_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_measurement_date` (`measurement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents table
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50),
  `file_size` int(11),
  `category` varchar(50) COMMENT 'lab_results, insurance, prescription, etc.',
  `description` text,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_category` (`category`),
  KEY `idx_upload_date` (`upload_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Care notes table
CREATE TABLE `care_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `note_type` varchar(50) COMMENT 'general, medical, behavioral, etc.',
  `title` varchar(200),
  `content` text NOT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_note_type` (`note_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts table
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `contact_type` enum('emergency', 'family', 'medical', 'pharmacy', 'insurance') NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50),
  `phone_primary` varchar(20),
  `phone_secondary` varchar(20),
  `email` varchar(100),
  `address` text,
  `notes` text,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_contact_type` (`contact_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Error logs table
CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `error_level` enum('DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'ERROR',
  `error_message` text NOT NULL,
  `file_path` varchar(255),
  `line_number` int(11),
  `stack_trace` text,
  `request_data` text,
  `user_agent` varchar(500),
  `ip_address` varchar(45),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_error_level` (`error_level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table for session management
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11),
  `session_data` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `phone`, `date_of_birth`) VALUES
('diana_patient', 'diana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diana', 'Johnson', 'patient', '555-0101', '1950-03-15'),
('chance_caregiver', 'chance@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chance', 'Johnson', 'caregiver', '555-0102', '1975-08-22'),
('ethan_family', 'ethan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ethan', 'Johnson', 'family', '555-0103', '2000-11-10');

-- Insert sample medications
INSERT INTO `medications` (`patient_id`, `medication_name`, `dosage`, `frequency`, `prescribing_doctor`, `start_date`, `instructions`) VALUES
(1, 'Lisinopril', '10mg', 'Once daily', 'Dr. Smith', '2024-01-01', 'Take with food in the morning'),
(1, 'Metformin', '500mg', 'Twice daily', 'Dr. Smith', '2024-01-01', 'Take with meals');

-- Insert sample contacts
INSERT INTO `contacts` (`patient_id`, `contact_type`, `name`, `relationship`, `phone_primary`, `email`, `is_primary`) VALUES
(1, 'emergency', 'Chance Johnson', 'Son', '555-0102', 'chance@example.com', 1),
(1, 'medical', 'Dr. Smith', 'Primary Care', '555-0200', 'drsmith@clinic.com', 1),
(1, 'pharmacy', 'Milford Pharmacy', 'Pharmacy', '555-0300', 'info@milfordpharmacy.com', 1);

-- Create views for common queries
CREATE VIEW `patient_dashboard` AS
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(DISTINCT m.id) as medication_count,
    COUNT(DISTINCT a.id) as upcoming_appointments,
    COUNT(DISTINCT t.id) as open_tasks
FROM users u
LEFT JOIN medications m ON u.id = m.patient_id AND m.is_active = 1
LEFT JOIN appointments a ON u.id = a.patient_id AND a.appointment_date > NOW() AND a.status = 'scheduled'
LEFT JOIN tasks t ON u.id = t.patient_id AND t.status IN ('pending', 'in_progress')
WHERE u.role = 'patient'
GROUP BY u.id;

-- Create indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_medications_patient_active ON medications(patient_id, is_active);
CREATE INDEX idx_tasks_assigned_status ON tasks(assigned_to, status);
CREATE INDEX idx_appointments_patient_date ON appointments(patient_id, appointment_date);