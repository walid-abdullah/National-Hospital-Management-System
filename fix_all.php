<?php
$files = [
    '/Applications/XAMPP/xamppfiles/htdocs/nhms/pharmacy/inventory.php',
    '/Applications/XAMPP/xamppfiles/htdocs/nhms/pharmacy/pos.php',
    '/Applications/XAMPP/xamppfiles/htdocs/nhms/admin/includes/navbar.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('NHMS', 'NHIMS', $content);
        $content = str_replace('National Hospital Management System', 'National Hospital Information Management System', $content);
        $content = str_replace('nhms-hospital.com', 'nhims-hospital.com', $content);
        
        // Save to tmp first
        $tmp = '/tmp/' . basename($file);
        file_put_contents($tmp, $content);
        
        // Unlink original
        unlink($file);
        
        // Copy back
        copy($tmp, $file);
        echo "Fixed $file\n";
    }
}
