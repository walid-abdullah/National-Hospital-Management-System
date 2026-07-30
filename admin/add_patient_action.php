<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $age = intval($_POST['age']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);

    if(empty($name) || empty($phone) || empty($age) || empty($gender) || empty($address)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_patient.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO patients (name, age, gender, phone, address) VALUES (:name, :age, :gender, :phone, :address)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Patient registered successfully!";
            header("Location: patients.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to register patient.";
            header("Location: add_patient.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_patient.php");
        exit();
    }
} else {
    header("Location: add_patient.php");
    exit();
}
?>
