<?php
require_once __DIR__ . '/includes/security.php';
init_secure_session();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
