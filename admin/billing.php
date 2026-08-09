<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Fetch all bills
try {
    $query = "SELECT b.id as bill_id, p.name AS patient_name, b.total_amount as amount, b.status as payment_status, b.bill_date 
              FROM billing b 
              JOIN patients p ON b.patient_id = p.id 
              ORDER BY b.bill_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching billing data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Management - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>

    
    <script>
        tailwind.config = {
          darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
        // Check local storage for dark mode preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>


<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-200 selection:text-blue-900 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-6xl mx-auto animate-fade-in-up p-6 mt-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Billing Management</h2>
            <a href="add_billing.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 px-5 py-2 rounded-lg font-medium shadow transition">
                + Generate Bill
            </a>
        </div>

        <!-- Billing Table -->
        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">Bill ID</th>
                        <th class="p-4 border-b">Patient Name</th>
                        <th class="p-4 border-b">Amount</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b">Bill Date</th>
                        <th class="p-4 border-b text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(!empty($bills)): ?>
                        <?php foreach($bills as $bill): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200">#<?php echo $bill['bill_id']; ?></td>
                            <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($bill['patient_name']); ?></td>
                            <td class="p-4 text-sm font-medium text-gray-800 dark:text-gray-100">$<?php echo number_format($bill['amount'], 2); ?></td>
                            <td class="p-4 text-sm">
                                <?php if($bill['payment_status'] == 'Paid'): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Paid</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></td>
                            <td class="p-4 text-center space-x-2">
                                <a href="print_invoice.php?id=<?php echo $bill['bill_id']; ?>" target="_blank" class="text-blue-500 hover:text-blue-700 font-medium text-sm border border-blue-500 px-3 py-1 rounded hover:bg-blue-50 transition">Print Invoice</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">No bills found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>

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
