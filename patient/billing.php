<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id as patient_id FROM patients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($patient) {
    $patient_id = $patient['patient_id'];
    $query = "SELECT id, invoice_number, total_amount, status, bill_date, details 
              FROM billing 
              WHERE patient_id = :patient_id 
              ORDER BY bill_date DESC";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindParam(':patient_id', $patient_id);
    $stmt2->execute();
    $bills = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $bills = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bills - NHIMS</title>
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
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Patient Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <a href="appointments.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Appointments</a>
            <a href="records.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">History</a>
            <a href="prescriptions.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Prescriptions</a>
            <a href="lab_tests.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Lab Reports</a>
            <span class="border-l border-teal-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>
    <div class="max-w-6xl mx-auto w-full mt-10 p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-100">My Bills</h2>
        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">Invoice #</th>
                        <th class="p-4 border-b">Date</th>
                        <th class="p-4 border-b">Details</th>
                        <th class="p-4 border-b text-right">Amount</th>
                        <th class="p-4 border-b text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if(!empty($bills)): ?>
                        <?php foreach($bills as $bill): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200 align-top"><?php echo htmlspecialchars($bill['invoice_number']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400 align-top"><?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></td>
                            <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                                <?php 
                                    $details = json_decode($bill['details'], true);
                                    if(is_array($details)) {
                                        echo "<ul class='list-disc pl-5'>";
                                        foreach($details as $item) {
                                            $name = htmlspecialchars($item['name'] ?? $item['item'] ?? 'Item');
                                            $cost = number_format((float)($item['cost'] ?? $item['price'] ?? 0), 2);
                                            echo "<li>{$name}: <span class='text-gray-400 font-mono'>$cost</span></li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo nl2br(htmlspecialchars($bill['details']));
                                    }
                                ?>
                            </td>
                            <td class="p-4 text-sm font-bold text-gray-800 dark:text-gray-100 text-right align-top">$<?php echo number_format($bill['total_amount'], 2); ?></td>
                            <td class="p-4 text-center align-top">
                                <?php if($bill['status'] == 'Paid'): ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold shadow-sm">Paid</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-bold shadow-sm"><?php echo htmlspecialchars($bill['status']); ?></span>
                                    <div class="mt-2">
                                        <a href="#" class="inline-block bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs px-3 py-1 rounded shadow hover:scale-105 transition transform">Pay Now</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="p-6 text-center text-gray-500 dark:text-gray-400">No billing history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.querySelector("table");
            if (table) {
                new simpleDatatables.DataTable(table, {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 15
                });
            }
        });
    </script>
</body>
</html>
