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
    $appointment_date = trim($_POST['appointment_date']);
    $status = trim($_POST['status']);

    if(empty($patient_id) || empty($doctor_id) || empty($appointment_date)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_appointment.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (:patient_id, :doctor_id, :appointment_date, :status)");
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':status', $status);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Appointment booked successfully!";
            header("Location: appointments.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to book appointment.";
            header("Location: add_appointment.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_appointment.php");
        exit();
    }
} else {
    header("Location: add_appointment.php");
    exit();
}
?>
