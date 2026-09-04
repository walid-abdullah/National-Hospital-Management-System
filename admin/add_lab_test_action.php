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
    $test_name = trim($_POST['test_name']);
    $test_date = trim($_POST['test_date']);
    $test_result = trim($_POST['test_result']);

    if(empty($patient_id) || empty($test_name) || empty($test_date) || empty($test_result)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_lab_test.php");
        exit();
    }
    try {
        $stmt = $conn->prepare("INSERT INTO laboratory_tests (patient_id, test_name, test_result, test_date) VALUES (:patient_id, :test_name, :test_result, :test_date)");
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':test_name', $test_name);
        $stmt->bindParam(':test_result', $test_result);
        $stmt->bindParam(':test_date', $test_date);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Lab test added successfully!";
            header("Location: lab_tests.php");
        } else {
            $_SESSION['error'] = "Failed to add lab test.";
            header("Location: add_lab_test.php");
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_lab_test.php");
    }
}
?>
