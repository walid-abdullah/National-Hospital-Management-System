<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_name = trim($_POST['doctor_name']);
    $specialization = trim($_POST['specialization']);
    $phone = trim($_POST['phone']);
    $schedule = trim($_POST['schedule']);

    if(empty($doctor_name) || empty($specialization) || empty($phone) || empty($schedule)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_doctor.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO doctors (doctor_name, specialization, phone, schedule) VALUES (:doctor_name, :specialization, :phone, :schedule)");
        $stmt->bindParam(':doctor_name', $doctor_name);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':schedule', $schedule);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Doctor added successfully!";
            header("Location: doctors.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add doctor.";
            header("Location: add_doctor.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_doctor.php");
        exit();
    }
} else {
    header("Location: add_doctor.php");
    exit();
}
?>
