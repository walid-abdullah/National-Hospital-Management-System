<?php
require_once '../includes/security.php';
init_secure_session();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Invalid security token.";
        header("Location: add_billing.php");
        exit();
    }
    $patient_id = intval($_POST['patient_id']);
    $total_amount = (float) ($_POST['total_amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    $status = trim($_POST['status'] ?? 'Unpaid');
    $bill_date = trim($_POST['bill_date']);

    if (empty($patient_id) || $total_amount <= 0 || empty($bill_date)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_billing.php");
        exit();
    }
    try {
        $hospital_stmt = $conn->prepare("SELECT hospital_id FROM patients WHERE id = :patient_id");
        $hospital_stmt->execute([':patient_id' => $patient_id]);
        $hospital_id = $hospital_stmt->fetchColumn();
        if (!$hospital_id) {
            throw new RuntimeException('Patient record not found.');
        }
        $invoice_number = 'INV-' . date('YmdHis') . '-' . random_int(1000, 9999);
        $details = json_encode([['name' => 'Hospital Services', 'cost' => $total_amount]], JSON_THROW_ON_ERROR);
        $stmt = $conn->prepare(
            "INSERT INTO billing
             (hospital_id, patient_id, invoice_number, total_amount, payment_method, status, bill_date, details)
             VALUES (:hospital_id, :patient_id, :invoice_number, :total_amount, :payment_method, :status, :bill_date, :details)"
        );
        $stmt->execute([
            ':hospital_id' => $hospital_id,
            ':patient_id' => $patient_id,
            ':invoice_number' => $invoice_number,
            ':total_amount' => $total_amount,
            ':payment_method' => $payment_method !== '' ? $payment_method : null,
            ':status' => $status,
            ':bill_date' => $bill_date,
            ':details' => $details,
        ]);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Bill generated successfully!";
            header("Location: billing.php");
        } else {
            $_SESSION['error'] = "Failed to generate bill.";
            header("Location: add_billing.php");
        }
    } catch (PDOException | RuntimeException | JsonException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_billing.php");
    }
}
?>
