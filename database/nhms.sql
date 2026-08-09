-- NHMS 5.0 Enterprise Database Schema
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `nhms`
CREATE DATABASE IF NOT EXISTS `nhms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nhms`;

-- Drop existing tables if any to ensure clean build
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `system_logs`, `patient_feedback`, `payrolls`, `attendance`, `ambulance_dispatch`, `ambulances`, `blood_requests`, `blood_donors`, `blood_bank`, `admissions`, `beds`, `wards`, `pharmacy_sales`, `pharmacy_inventory`, `lab_services`, `laboratory_tests`, `medical_records`, `prescriptions`, `appointments`, `billing`, `doctors`, `patients`, `receptionists`, `pharmacists`, `lab_staff`, `departments`, `users`, `hospitals`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Hospitals (Multi-Branch)
CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Users (Universal Auth)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Receptionist','Laboratory Staff','Pharmacist','Patient') NOT NULL,
  `status` enum('Pending','Approved','Rejected','Suspended') NOT NULL DEFAULT 'Pending',
  `identification_number` varchar(100) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Departments
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Medical','Laboratory','Administrative') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `dept_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Patients
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `pat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pat_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Doctors
CREATE TABLE `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL DEFAULT 1000.00,
  `base_salary` decimal(10,2) NOT NULL DEFAULT 50000.00,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hospital_id` (`hospital_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `doc_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doc_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doc_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Appointments (Hybrid)
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_type` enum('Physical','Telemedicine') NOT NULL DEFAULT 'Physical',
  `status` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `queue_token` int(11) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `app_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Billing
CREATE TABLE `billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Online','Card') DEFAULT NULL,
  `status` enum('Unpaid','Paid','Refunded') NOT NULL DEFAULT 'Unpaid',
  `bill_date` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text NOT NULL, -- JSON or string list of items
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `bill_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Lab Services Catalog
CREATE TABLE `lab_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `department` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `lserv_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Laboratory Tests
CREATE TABLE `laboratory_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('Pending','Sample Collected','Processing','Completed') NOT NULL DEFAULT 'Pending',
  `result_text` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `test_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `ltest_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ltest_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `lab_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Pharmacy Inventory
CREATE TABLE `pharmacy_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `unit_price` decimal(10,2) NOT NULL,
  `expiry_date` date NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `pharm_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pharmacy_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_name` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `phsales_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Prescriptions
CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medicines` text NOT NULL, -- JSON formatted list
  `date_issued` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `presc_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presc_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Medical Records
CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `treatment` text NOT NULL,
  `visit_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `mrec_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mrec_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Wards & Beds
CREATE TABLE `wards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('General','ICU','CCU','VIP Cabin') NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `ward_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `beds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ward_id` int(11) NOT NULL,
  `bed_number` varchar(20) NOT NULL,
  `status` enum('Available','Occupied','Maintenance') NOT NULL DEFAULT 'Available',
  PRIMARY KEY (`id`),
  KEY `ward_id` (`ward_id`),
  CONSTRAINT `bed_ibfk_1` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Admissions
CREATE TABLE `admissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `bed_id` int(11) NOT NULL,
  `admission_date` datetime NOT NULL DEFAULT current_timestamp(),
  `discharge_date` datetime DEFAULT NULL,
  `status` enum('Admitted','Discharged') NOT NULL DEFAULT 'Admitted',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `bed_id` (`bed_id`),
  CONSTRAINT `adm_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `adm_ibfk_2` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Blood Bank
CREATE TABLE `blood_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `bags_available` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hosp_bg` (`hospital_id`, `blood_group`),
  CONSTRAINT `bb_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `blood_donors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `last_donation_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `bd_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Ambulances
CREATE TABLE `ambulances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `vehicle_number` varchar(50) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `driver_phone` varchar(20) NOT NULL,
  `status` enum('Available','On-Trip','Maintenance') NOT NULL DEFAULT 'Available',
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  CONSTRAINT `amb_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. HR & Payroll
CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `month_year` varchar(20) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `status` enum('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pay_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Patient Feedback
CREATE TABLE `patient_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL, -- 1 to 5
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `hospital_id` (`hospital_id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `pf_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pf_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. System Logs (Audit Trail)
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sysl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
