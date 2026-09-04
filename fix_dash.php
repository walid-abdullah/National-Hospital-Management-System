<?php
$file = '/Applications/XAMPP/xamppfiles/htdocs/nhms/pharmacy/dashboard.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    $content = str_replace('NHMS', 'NHIMS', $content);
    $content = str_replace('National Hospital Management System', 'National Hospital Information Management System', $content);
    $content = str_replace('nhms-hospital.com', 'nhims-hospital.com', $content);
    
    $tmp = '/tmp/' . basename($file);
    file_put_contents($tmp, $content);
    unlink($file);
    copy($tmp, $file);
    echo "Fixed $file\n";
}
