<?php
require_once 'config/db.php';

try {
    // Disable foreign key checks for truncation
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $conn->exec("TRUNCATE TABLE billing");
    $conn->exec("TRUNCATE TABLE laboratory_tests");
    $conn->exec("TRUNCATE TABLE medical_records");
    $conn->exec("TRUNCATE TABLE appointments");
    $conn->exec("TRUNCATE TABLE patients");
    $conn->exec("TRUNCATE TABLE doctors");
    $conn->exec("TRUNCATE TABLE users");
    
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Tables truncated successfully.<br>";

    // Insert Users (Core Roles)
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    
    $users = [
        ['admin', 'password', 'Admin'],
        ['receptionist', 'password', 'Receptionist'],
        ['labstaff', 'password', 'Laboratory Staff'],
    ];

    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // Insert 20 Doctors
    $doctors_data = [
        ['dr_smith', 'password', 'Doctor', 'Dr. John Smith', 'Cardiology', 'Mon-Wed-Fri, 10 AM - 2 PM', '01711223344'],
        ['dr_hossain', 'password', 'Doctor', 'Dr. Akram Hossain', 'Neurology', 'Tue-Thu, 4 PM - 8 PM', '01811223344'],
        ['dr_sarah', 'password', 'Doctor', 'Dr. Sarah Connor', 'Pediatrics', 'Mon-Fri, 9 AM - 1 PM', '01911223344'],
        ['dr_ahmed', 'password', 'Doctor', 'Dr. Shafiq Ahmed', 'Orthopedics', 'Sat-Sun, 5 PM - 9 PM', '01611223344'],
        ['dr_lee', 'password', 'Doctor', 'Dr. Bruce Lee', 'Cardiology', 'Sun-Tue, 11 AM - 3 PM', '01511223344'],
        ['dr_fatima', 'password', 'Doctor', 'Dr. Fatima Rahman', 'Gynecology', 'Mon-Wed-Thu, 6 PM - 9 PM', '01311223344'],
        ['dr_kumar', 'password', 'Doctor', 'Dr. Rajesh Kumar', 'Dermatology', 'Mon-Fri, 10 AM - 1 PM', '01722334455'],
        ['dr_williams', 'password', 'Doctor', 'Dr. Emma Williams', 'Psychiatry', 'Tue-Thu, 2 PM - 6 PM', '01822334455'],
        ['dr_rahman', 'password', 'Doctor', 'Dr. Mahfuz Rahman', 'General Surgery', 'Wed-Sat, 9 AM - 2 PM', '01922334455'],
        ['dr_taylor', 'password', 'Doctor', 'Dr. Michael Taylor', 'Neurology', 'Mon-Thu, 3 PM - 7 PM', '01622334455'],
        ['dr_khan', 'password', 'Doctor', 'Dr. Tariq Khan', 'Orthopedics', 'Sun-Wed, 10 AM - 1 PM', '01522334455'],
        ['dr_jones', 'password', 'Doctor', 'Dr. Olivia Jones', 'Pediatrics', 'Fri-Sun, 9 AM - 12 PM', '01322334455'],
        ['dr_ali', 'password', 'Doctor', 'Dr. Zulfikar Ali', 'Cardiology', 'Mon-Tue, 4 PM - 8 PM', '01733445566'],
        ['dr_brown', 'password', 'Doctor', 'Dr. William Brown', 'Ophthalmology', 'Tue-Fri, 10 AM - 3 PM', '01833445566'],
        ['dr_davis', 'password', 'Doctor', 'Dr. Sophia Davis', 'Gynecology', 'Wed-Sun, 1 PM - 5 PM', '01933445566'],
        ['dr_haque', 'password', 'Doctor', 'Dr. Anisul Haque', 'General Surgery', 'Sat-Mon, 5 PM - 9 PM', '01633445566'],
        ['dr_miller', 'password', 'Doctor', 'Dr. James Miller', 'Psychiatry', 'Tue-Thu, 11 AM - 3 PM', '01533445566'],
        ['dr_wilson', 'password', 'Doctor', 'Dr. Mia Wilson', 'Dermatology', 'Mon-Wed, 2 PM - 6 PM', '01333445566'],
        ['dr_moore', 'password', 'Doctor', 'Dr. Benjamin Moore', 'Neurology', 'Thu-Sun, 10 AM - 2 PM', '01744556677'],
        ['dr_white', 'password', 'Doctor', 'Dr. Charlotte White', 'Pediatrics', 'Mon-Fri, 4 PM - 7 PM', '01844556677']
    ];

    $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt_doc = $conn->prepare("INSERT INTO doctors (user_id, doctor_name, specialization, schedule, phone) VALUES (?, ?, ?, ?, ?)");

    $doctor_ids = [];
    foreach ($doctors_data as $d) {
        $stmt_user->execute([$d[0], $d[1], $d[2]]);
        $uid = $conn->lastInsertId();
        $stmt_doc->execute([$uid, $d[3], $d[4], $d[5], $d[6]]);
        $doctor_ids[] = $conn->lastInsertId();
    }
    
    // Insert 10 Patients
    $patients_data = [
        ['patient1', 'password', 'Patient', 'Alice Bob', 35, 'Female', '01999888777', '123 Main St, Dhaka'],
        ['patient2', 'password', 'Patient', 'Charlie Davis', 40, 'Male', '01888777666', '456 Oak Ave, Dhaka'],
        ['patient3', 'password', 'Patient', 'Eve Foster', 28, 'Female', '01777666555', '789 Pine Rd, Sylhet'],
        ['patient4', 'password', 'Patient', 'Frank Green', 45, 'Male', '01666555444', '321 Elm St, Rajshahi'],
        ['patient5', 'password', 'Patient', 'Grace Hall', 32, 'Female', '01555444333', '654 Maple Dr, Chittagong'],
        ['patient6', 'password', 'Patient', 'Henry King', 50, 'Male', '01444333222', '987 Cedar Ct, Dhaka'],
        ['patient7', 'password', 'Patient', 'Ivy Lewis', 25, 'Female', '01333222111', '147 Birch Blvd, Khulna'],
        ['patient8', 'password', 'Patient', 'Jack Martin', 55, 'Male', '01911122233', '258 Spruce Way, Barisal'],
        ['patient9', 'password', 'Patient', 'Karen Nelson', 30, 'Female', '01822233344', '369 Ash Ln, Dhaka'],
        ['patient10', 'password', 'Patient', 'Leo Perez', 38, 'Male', '01733344455', '741 Walnut Cir, Comilla']
    ];

    $stmt_pat = $conn->prepare("INSERT INTO patients (user_id, name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
    $patient_ids = [];
    foreach ($patients_data as $p) {
        $stmt_user->execute([$p[0], $p[1], $p[2]]);
        $uid = $conn->lastInsertId();
        $stmt_pat->execute([$uid, $p[3], $p[4], $p[5], $p[6], $p[7]]);
        $patient_ids[] = $conn->lastInsertId();
    }

    // Insert 15 Appointments (Mixed statuses)
    $appointments_data = [
        [$patient_ids[0], $doctor_ids[0], date('Y-m-d', strtotime('+1 day')), 'Pending', 'Heart palpitations'],
        [$patient_ids[1], $doctor_ids[1], date('Y-m-d', strtotime('+2 days')), 'Completed', 'Severe headaches'],
        [$patient_ids[2], $doctor_ids[2], date('Y-m-d', strtotime('+3 days')), 'Pending', 'Child vaccination'],
        [$patient_ids[3], $doctor_ids[3], date('Y-m-d', strtotime('+1 day')), 'Cancelled', 'Knee pain'],
        [$patient_ids[4], $doctor_ids[4], date('Y-m-d', strtotime('+4 days')), 'Pending', 'Chest pain'],
        [$patient_ids[5], $doctor_ids[5], date('Y-m-d', strtotime('+2 days')), 'Completed', 'Regular checkup'],
        [$patient_ids[6], $doctor_ids[6], date('Y-m-d', strtotime('+5 days')), 'Pending', 'Skin rash'],
        [$patient_ids[7], $doctor_ids[7], date('Y-m-d', strtotime('+1 day')), 'Pending', 'Anxiety'],
        [$patient_ids[8], $doctor_ids[8], date('Y-m-d', strtotime('+6 days')), 'Completed', 'Appendicitis follow-up'],
        [$patient_ids[9], $doctor_ids[9], date('Y-m-d', strtotime('+2 days')), 'Pending', 'Migraine'],
        [$patient_ids[0], $doctor_ids[10], date('Y-m-d', strtotime('+7 days')), 'Pending', 'Back pain'],
        [$patient_ids[1], $doctor_ids[11], date('Y-m-d', strtotime('+3 days')), 'Cancelled', 'Fever'],
        [$patient_ids[2], $doctor_ids[12], date('Y-m-d', strtotime('+8 days')), 'Pending', 'Blood pressure check'],
        [$patient_ids[3], $doctor_ids[13], date('Y-m-d', strtotime('+4 days')), 'Completed', 'Eye exam'],
        [$patient_ids[4], $doctor_ids[14], date('Y-m-d', strtotime('+9 days')), 'Pending', 'Pregnancy checkup']
    ];

    $stmt_app = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status, disease) VALUES (?, ?, ?, ?, ?)");
    foreach ($appointments_data as $a) {
        $stmt_app->execute($a);
    }

    echo "Demo Doctors, Patients, and Appointments injected successfully.<br>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
