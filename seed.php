<?php
require_once 'config/db.php';

try {
    // 1. Add more Users (2 Doctors, 3 Patients)
    // Password is 'password' (MD5 = 5f4dcc3b5aa765d61d8327deb882cf99)
    $sql = "INSERT IGNORE INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
    (6, 'doctor2', '5f4dcc3b5aa765d61d8327deb882cf99', 'Doctor', '2026-07-30 10:00:00'),
    (7, 'doctor3', '5f4dcc3b5aa765d61d8327deb882cf99', 'Doctor', '2026-07-30 10:00:00'),
    (8, 'patient2', '5f4dcc3b5aa765d61d8327deb882cf99', 'Patient', '2026-07-30 10:00:00'),
    (9, 'patient3', '5f4dcc3b5aa765d61d8327deb882cf99', 'Patient', '2026-07-30 10:00:00'),
    (10, 'patient4', '5f4dcc3b5aa765d61d8327deb882cf99', 'Patient', '2026-07-30 10:00:00')";
    $conn->exec($sql);

    // 2. Add Patients
    $sql = "INSERT IGNORE INTO `patients` (`patient_id`, `user_id`, `name`, `age`, `gender`, `phone`, `address`) VALUES
    (2, 8, 'Alice Smith', 28, 'Female', '01711111111', 'Gulshan, Dhaka'),
    (3, 9, 'Bob Johnson', 45, 'Male', '01722222222', 'Banani, Dhaka'),
    (4, 10, 'Charlie Brown', 34, 'Male', '01733333333', 'Dhanmondi, Dhaka')";
    $conn->exec($sql);

    // 3. Add Doctors
    $sql = "INSERT IGNORE INTO `doctors` (`doctor_id`, `user_id`, `doctor_name`, `specialization`, `phone`, `schedule`) VALUES
    (2, 6, 'Dr. Sarah Khan', 'Neurologist', '01811111111', 'Mon, Wed, Fri 4PM-8PM'),
    (3, 7, 'Dr. Ahmed Ali', 'Orthopedics', '01822222222', 'Tue, Thu, Sat 9AM-1PM')";
    $conn->exec($sql);

    // 4. Add Appointments
    $sql = "INSERT IGNORE INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `status`) VALUES
    (1, 1, 1, '2026-08-01', 'Confirmed'),
    (2, 2, 2, '2026-08-02', 'Pending'),
    (3, 3, 3, '2026-08-03', 'Completed'),
    (4, 4, 1, '2026-08-04', 'Confirmed'),
    (5, 1, 2, '2026-07-28', 'Completed')";
    $conn->exec($sql);

    // 5. Add Medical Records
    $sql = "INSERT IGNORE INTO `medical_records` (`record_id`, `patient_id`, `doctor_id`, `diagnosis`, `treatment`, `visit_date`) VALUES
    (1, 3, 3, 'Fractured left arm', 'Applied plaster cast. Prescribed painkillers.', '2026-08-03'),
    (2, 1, 2, 'Severe Migraine', 'Advised rest and prescribed migraine relief medication.', '2026-07-28')";
    $conn->exec($sql);

    // 6. Add Laboratory Tests
    $sql = "INSERT IGNORE INTO `laboratory_tests` (`test_id`, `patient_id`, `test_name`, `test_result`, `test_date`) VALUES
    (1, 1, 'Complete Blood Count (CBC)', 'Hemoglobin: 13.5 g/dL (Normal)\nWBC: 7,000 /mcL (Normal)', '2026-07-29'),
    (2, 3, 'X-Ray Left Arm', 'Clear fracture visible on the radius bone.', '2026-08-03'),
    (3, 2, 'Lipid Profile', 'Cholesterol: 210 mg/dL (High)\nTriglycerides: 150 mg/dL', '2026-07-30')";
    $conn->exec($sql);

    // 7. Add Billing
    $sql = "INSERT IGNORE INTO `billing` (`bill_id`, `patient_id`, `amount`, `payment_status`, `bill_date`) VALUES
    (1, 1, 1500.00, 'Paid', '2026-07-28'),
    (2, 3, 4500.00, 'Paid', '2026-08-03'),
    (3, 2, 1200.00, 'Unpaid', '2026-07-30'),
    (4, 4, 800.00, 'Unpaid', '2026-08-01')";
    $conn->exec($sql);

    echo "<h1>Demo Data Successfully Added!</h1>";
    echo "<p>You can now log in and see the populated tables.</p>";
    echo "<a href='index.php'>Go to Login Page</a>";

    // Self-delete after running
    unlink(__FILE__);

} catch(PDOException $e) {
    echo "Error inserting demo data: " . $e->getMessage();
}
?>
