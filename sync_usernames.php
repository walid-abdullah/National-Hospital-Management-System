<?php
require_once 'config/db.php';

echo "<h2>Syncing Usernames for Presentation</h2>";

try {
    // 1. Sync Doctors to d1, d2, d3...
    $stmt = $conn->query("SELECT id, username FROM users WHERE role = 'Doctor' ORDER BY id ASC");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $i = 1;
    foreach ($doctors as $doc) {
        $new_username = "d" . $i;
        $conn->prepare("UPDATE users SET username = ? WHERE id = ?")->execute([$new_username, $doc['id']]);
        echo "Doctor {$doc['username']} -> $new_username <br>";
        $i++;
    }

    // 2. Setup specific Lab Accounts (Generic 'lab')
    $conn->query("DELETE FROM users WHERE role = 'Laboratory Staff'");
    
    // Insert new generic lab staff with password '123456'
    $pass = md5('123456');
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Laboratory Staff')");
    $stmt_insert->execute(['lab', $pass]);
    
    echo "Lab Accounts reset to: lab <br>";

    // 3. Make sure Receptionist is just 'r' or 'receptionist'
    // Let's ensure there's at least one 'r'
    $conn->query("DELETE FROM users WHERE role = 'Receptionist'");
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Receptionist')");
    $stmt_insert->execute(['r', $pass]);
    
    echo "Receptionist reset to: r <br>";
    
    // 4. Admin is 'admin'
    // Let's ensure there's an 'admin'
    $conn->query("DELETE FROM users WHERE role = 'Admin'");
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Admin')");
    $stmt_insert->execute(['admin', $pass]);
    
    echo "Admin reset to: admin <br>";

    echo "<h3>Success! All usernames synced!</h3>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
