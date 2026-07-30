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
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND role = :role LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check password (in real world use password_verify with hashed passwords)
            // For simplicity in this demo, we check MD5 since we used md5 in the SQL dump, 
            // OR simple plain text. Let's assume MD5 for some security feeling.
            if (md5($password) === $user['password']) {
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
