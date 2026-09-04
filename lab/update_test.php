<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Laboratory Staff') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$test_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Invalid security token.</div>";
    } else {
    $result_text = trim($_POST['test_result'] ?? '');
    
    // File upload logic
    $file_path_db = null;
    if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['report_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_dir = '../uploads/lab_reports/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $destination = $upload_dir . $new_filename;
            if (move_uploaded_file($_FILES['report_file']['tmp_name'], $destination)) {
                $file_path_db = 'uploads/lab_reports/' . $new_filename;
            }
            }
        } else {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Invalid file type. Only PDF/JPG/PNG allowed.</div>";
        }
    }

    if (!empty($result_text) || $file_path_db) {
        try {
            if ($file_path_db) {
                $update = $conn->prepare("UPDATE laboratory_tests SET result_text = :res, status = 'Completed', file_path = :fpath WHERE id = :id");
                $update->execute([':res' => empty($result_text) ? 'See attached document.' : $result_text, ':fpath' => $file_path_db, ':id' => $test_id]);
            } else {
                $update = $conn->prepare("UPDATE laboratory_tests SET result_text = :res, status = 'Completed' WHERE id = :id");
                $update->execute([':res' => $result_text, ':id' => $test_id]);
            }
            $msg = "<div class='bg-green-100 text-green-700 p-4 rounded mb-4'>Test result updated successfully!</div>";
        } catch(PDOException $e) {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        if(empty($msg)) $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Please write a report or upload a file.</div>";
    }
}

// Fetch test details
try {
    $stmt = $conn->prepare("SELECT l.*, p.name AS patient_name, p.age, p.gender, s.service_name AS test_name FROM laboratory_tests l JOIN patients p ON l.patient_id = p.patient_id JOIN lab_services s ON l.service_id = s.id WHERE l.id = :id");
    $stmt->execute([':id' => $test_id]);
    $test = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$test) {
        die("Test not found.");
    }
} catch(PDOException $e) {
    die("Database Error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Lab Test - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold tracking-tight text-blue-600 dark:text-blue-400">NHIMS Lab</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-blue-500 hover:underline text-sm font-semibold">&larr; Back to Dashboard</a>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto w-full mt-12 p-6">
        <div class="glass p-8 rounded-2xl shadow-xl">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">Update Lab Report</h2>
            <?php echo $msg; ?>
            
            <div class="bg-gray-50 dark:bg-slate-800 p-6 rounded-xl mb-6 border border-gray-200 dark:border-slate-700">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Patient Name</p>
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($test['patient_name']); ?> (<?php echo $test['age']; ?>/<?php echo $test['gender'][0]; ?>)</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Test Required</p>
                        <p class="font-bold text-lg text-yellow-600"><?php echo htmlspecialchars($test['test_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Request Date</p>
                        <p class="font-semibold"><?php echo date('d M Y', strtotime($test['test_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Current Status</p>
                        <p class="font-semibold text-blue-600"><?php echo htmlspecialchars($test['result_text'] ?? 'Pending'); ?></p>
                    </div>
                </div>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Final Test Result / Report Details (Optional if file uploaded)</label>
                    <textarea name="test_result" rows="5" class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"><?php 
                        // If it's already pending, clear it so they can write the real report
                        echo (empty($test['result_text']) || strpos(strtolower($test['result_text']), 'pending') !== false) ? '' : htmlspecialchars($test['result_text']); 
                    ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Upload PDF/Image Report (Optional)</label>
                    <input type="file" name="report_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <?php if(!empty($test['file_path'])): ?>
                        <p class="text-sm text-green-600 mt-2">Currently uploaded: <a href="../<?php echo htmlspecialchars($test['file_path']); ?>" target="_blank" class="underline">View File</a></p>
                    <?php endif; ?>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow">Save Official Report</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
