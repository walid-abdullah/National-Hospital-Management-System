<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $visit_date = trim($_POST['visit_date']);
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);

    if(empty($patient_id) || empty($doctor_id) || empty($visit_date) || empty($diagnosis) || empty($treatment)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_medical_record.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, visit_date, diagnosis, treatment) VALUES (:patient_id, :doctor_id, :visit_date, :diagnosis, :treatment)");
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':visit_date', $visit_date);
        $stmt->bindParam(':diagnosis', $diagnosis);
        $stmt->bindParam(':treatment', $treatment);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Medical record added successfully!";
            header("Location: medical_records.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add record.";
            header("Location: add_medical_record.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_medical_record.php");
        exit();
    }
} else {
    header("Location: add_medical_record.php");
    exit();
}
?>
