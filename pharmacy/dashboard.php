<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Pharmacist') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
// Get hospital_id for the pharmacist
$stmt = $conn->prepare("SELECT hospital_id FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
$hospital_id = $user_info['hospital_id'];

// Get Stats
$stmt = $conn->prepare("SELECT COUNT(*) as total_medicines FROM pharmacy_inventory WHERE hospital_id = :hospital_id");
$stmt->execute([':hospital_id' => $hospital_id]);
$total_medicines = $stmt->fetch(PDO::FETCH_ASSOC)['total_medicines'];

$stmt = $conn->prepare("SELECT COUNT(*) as low_stock FROM pharmacy_inventory WHERE hospital_id = :hospital_id AND stock_quantity < 50");
$stmt->execute([':hospital_id' => $hospital_id]);
$low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];

$stmt = $conn->prepare("SELECT SUM(total_amount) as total_sales FROM pharmacy_sales WHERE hospital_id = :hospital_id AND DATE(sale_date) = CURDATE()");
$stmt->execute([':hospital_id' => $hospital_id]);
$today_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard - NHMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #059669, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #34d399, #6ee7b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHMS - Pharmacy</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-emerald-600 font-medium">Dashboard</a>
            <a href="inventory.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">Inventory</a>
            <a href="pos.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">POS Terminal</a>
            <span class="border-l border-emerald-400 h-6 mx-2"></span>
            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto animate-fade-in-up mt-10 w-full p-6">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-8">Pharmacy Overview</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass p-6 rounded-2xl shadow-lg border-l-4 border-emerald-500">
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Total Medicines</h3>
                <div class="text-4xl font-black text-gray-800 dark:text-white mt-2"><?php echo $total_medicines; ?></div>
                <div class="mt-4"><a href="inventory.php" class="text-emerald-600 dark:text-emerald-400 text-sm font-medium hover:underline">Manage Inventory &rarr;</a></div>
            </div>
            
            <div class="glass p-6 rounded-2xl shadow-lg border-l-4 border-rose-500">
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Low Stock Alerts</h3>
                <div class="text-4xl font-black text-rose-600 dark:text-rose-400 mt-2"><?php echo $low_stock; ?></div>
                <div class="mt-4"><a href="inventory.php?filter=low_stock" class="text-rose-600 dark:text-rose-400 text-sm font-medium hover:underline">View Low Stock &rarr;</a></div>
            </div>

            <div class="glass p-6 rounded-2xl shadow-lg border-l-4 border-blue-500">
                <h3 class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Today's Sales</h3>
                <div class="text-4xl font-black text-gray-800 dark:text-white mt-2 font-mono">৳<?php echo number_format($today_sales, 2); ?></div>
                <div class="mt-4"><a href="pos.php" class="text-blue-600 dark:text-blue-400 text-sm font-medium hover:underline">Open POS Terminal &rarr;</a></div>
            </div>
        </div>
        
        <div class="glass p-8 rounded-2xl shadow-xl">
            <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Quick Actions</h3>
            <div class="flex flex-wrap gap-4">
                <a href="pos.php" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-6 py-3 rounded-lg shadow-lg font-bold hover:shadow-xl transform hover:-translate-y-1 transition-all">🛒 Start New Sale</a>
                <a href="inventory.php" class="bg-gradient-to-r from-slate-600 to-gray-700 text-white px-6 py-3 rounded-lg shadow-lg font-bold hover:shadow-xl transform hover:-translate-y-1 transition-all">📦 Add New Medicine</a>
            </div>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 w-full glass">
        &copy; 2026 National Hospital Management System.
    </footer>
</body>
</html>
