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
        header("Location: add_doctor.php");
        exit();
    }
    $doctor_name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'] ?? '';
    $consultation_fee = (float) ($_POST['consultation_fee'] ?? 1000);
    $base_salary = (float) ($_POST['base_salary'] ?? 50000);

    if (empty($doctor_name) || empty($specialization) || empty($phone) || strlen($password) < 8) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: add_doctor.php");
        exit();
    }

    try {
        $conn->beginTransaction();
        $admin = $conn->prepare("SELECT hospital_id FROM users WHERE id = :user_id");
        $admin->execute([':user_id' => $_SESSION['user_id']]);
        $hospital_id = $admin->fetchColumn();
        if (!$hospital_id) {
            throw new RuntimeException('Admin hospital is not configured.');
        }

        $user = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $user->execute([':username' => $phone]);
        $user_id = $user->fetchColumn();
        if ($user_id) {
            $role_check = $conn->prepare("SELECT role FROM users WHERE id = :user_id");
            $role_check->execute([':user_id' => $user_id]);
            if ($role_check->fetchColumn() !== 'Doctor') {
                throw new RuntimeException('The phone number is already registered to another role.');
            }
        }
        if (!$user_id) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $create_user = $conn->prepare(
                "INSERT INTO users (hospital_id, username, password, role, status)
                 VALUES (:hospital_id, :username, :password, 'Doctor', 'Approved')"
            );
            $create_user->execute([
                ':hospital_id' => $hospital_id,
                ':username' => $phone,
                ':password' => $hashed_password,
            ]);
            $user_id = $conn->lastInsertId();
        }

        $stmt = $conn->prepare(
            "INSERT INTO doctors
             (user_id, hospital_id, department_id, name, specialization, phone, consultation_fee, base_salary)
             VALUES (:user_id, :hospital_id, NULL, :name, :specialization, :phone, :consultation_fee, :base_salary)"
        );
        $stmt->execute([
            ':user_id' => $user_id,
            ':hospital_id' => $hospital_id,
            ':name' => $doctor_name,
            ':specialization' => $specialization,
            ':phone' => $phone,
            ':consultation_fee' => $consultation_fee,
            ':base_salary' => $base_salary,
        ]);
        
        if ($stmt->rowCount() === 1) {
            $conn->commit();
            $_SESSION['success'] = "Doctor added successfully!";
            header("Location: doctors.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add doctor.";
            header("Location: add_doctor.php");
            exit();
        }
    } catch (PDOException | RuntimeException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: add_doctor.php");
        exit();
    }
} else {
    header("Location: add_doctor.php");
    exit();
}
?>
