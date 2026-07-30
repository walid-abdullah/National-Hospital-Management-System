<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = intval($_POST['patient_id']);
    $amount = floatval($_POST['amount']);
    $payment_status = trim($_POST['payment_status']);
    $bill_date = trim($_POST['bill_date']);

    if(empty($patient_id) || empty($amount) || empty($bill_date)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_billing.php");
        exit();
    }
    try {
        $stmt = $conn->prepare("INSERT INTO billing (patient_id, amount, payment_status, bill_date) VALUES (:patient_id, :amount, :payment_status, :bill_date)");
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':payment_status', $payment_status);
        $stmt->bindParam(':bill_date', $bill_date);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Bill generated successfully!";
            header("Location: billing.php");
        } else {
            $_SESSION['error'] = "Failed to generate bill.";
            header("Location: add_billing.php");
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_billing.php");
    }
}
?>
