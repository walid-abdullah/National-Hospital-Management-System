<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if(empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: login.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = :username AND role = :role LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['status'] !== 'Approved') {
                    $_SESSION['error'] = "Your account is currently " . $user['status'] . ". Please contact Admin.";
                    header("Location: login.php");
                    exit();
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'Admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'Doctor':
                        header("Location: doctor/dashboard.php");
                        break;
                    case 'Receptionist':
                        header("Location: receptionist/dashboard.php");
                        break;
                    case 'Laboratory Staff':
                        header("Location: lab/dashboard.php");
                        break;
                    case 'Pharmacist':
                        header("Location: pharmacy/dashboard.php");
                        break;
                    case 'Patient':
                        header("Location: patient/dashboard.php");
                        break;
                    default:
                        header("Location: login.php");
                        break;
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid username or role.";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
