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
$hospital_id = $stmt->fetch(PDO::FETCH_ASSOC)['hospital_id'];

// Handle Add Medicine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medicine'])) {
    $med_name = trim($_POST['medicine_name']);
    $category = trim($_POST['category']);
    $stock = (int)$_POST['stock_quantity'];
    $price = (float)$_POST['unit_price'];
    $expiry = $_POST['expiry_date'];
    $manufac = trim($_POST['manufacturer']);
    
    $ins = $conn->prepare("INSERT INTO pharmacy_inventory (hospital_id, medicine_name, category, stock_quantity, unit_price, expiry_date, manufacturer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$hospital_id, $med_name, $category, $stock, $price, $expiry, $manufac]);
    $success = "Medicine added successfully!";
}

$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM pharmacy_inventory WHERE hospital_id = :hospital_id ";
if ($filter === 'low_stock') {
    $query .= " AND stock_quantity < 50 ";
}
$query .= " ORDER BY medicine_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute([':hospital_id' => $hospital_id]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Inventory - NHMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #059669, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #34d399, #6ee7b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHMS - Pharmacy</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">Dashboard</a>
            <a href="inventory.php" class="text-emerald-600 font-medium">Inventory</a>
            <a href="pos.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">POS Terminal</a>
            <span class="border-l border-emerald-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-10 w-full p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 glass rounded-2xl shadow-xl overflow-hidden flex flex-col h-[700px]">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
                <h2 class="text-xl font-bold">Inventory List</h2>
                <div>
                    <a href="inventory.php" class="px-3 py-1 rounded-l-md text-sm <?php echo $filter=='all'?'bg-emerald-600 text-white':'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300';?> border-r border-gray-300 dark:border-gray-600">All</a>
                    <a href="inventory.php?filter=low_stock" class="px-3 py-1 rounded-r-md text-sm <?php echo $filter=='low_stock'?'bg-emerald-600 text-white':'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300';?>">Low Stock</a>
                </div>
            </div>
            <div class="overflow-y-auto flex-grow p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-gray-100 dark:bg-gray-800 shadow-sm z-10">
                        <tr class="text-gray-600 dark:text-gray-300 uppercase text-xs">
                            <th class="p-4 border-b dark:border-gray-700">Medicine Name</th>
                            <th class="p-4 border-b dark:border-gray-700">Category</th>
                            <th class="p-4 border-b dark:border-gray-700 text-right">Stock</th>
                            <th class="p-4 border-b dark:border-gray-700 text-right">Price (৳)</th>
                            <th class="p-4 border-b dark:border-gray-700">Expiry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach($inventory as $item): ?>
                        <tr class="hover:bg-emerald-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="p-4 text-sm font-bold text-gray-800 dark:text-gray-200">
                                <?php echo htmlspecialchars($item['medicine_name']); ?>
                                <div class="text-xs font-normal text-gray-500"><?php echo htmlspecialchars($item['manufacturer']); ?></div>
                            </td>
                            <td class="p-4 text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($item['category']); ?></td>
                            <td class="p-4 text-sm text-right font-mono">
                                <?php if($item['stock_quantity'] < 50): ?>
                                    <span class="text-rose-600 font-bold bg-rose-100 px-2 py-1 rounded"><?php echo $item['stock_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="text-emerald-600 font-bold"><?php echo $item['stock_quantity']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm text-right font-mono font-bold">৳<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="p-4 text-sm text-gray-600 dark:text-gray-400">
                                <?php 
                                    $exp = strtotime($item['expiry_date']);
                                    $now = time();
                                    $class = ($exp < $now + (30*86400)) ? 'text-rose-500 font-bold' : '';
                                    echo "<span class='$class'>" . date('M Y', $exp) . "</span>";
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($inventory)): ?>
                        <tr><td colspan="5" class="p-6 text-center text-gray-500">No inventory found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="glass p-6 rounded-2xl shadow-xl h-fit sticky top-24">
            <h3 class="text-xl font-bold mb-6 text-gray-800 dark:text-white border-b pb-2">Add New Medicine</h3>
            <?php if(isset($success)) echo "<p class='bg-emerald-100 text-emerald-800 p-3 rounded mb-4 text-sm font-semibold'>$success</p>"; ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medicine Name</label>
                    <input type="text" name="medicine_name" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <input type="text" name="category" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial Stock</label>
                        <input type="number" name="stock_quantity" min="0" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Price (৳)</label>
                        <input type="number" step="0.01" name="unit_price" min="0" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manufacturer</label>
                    <input type="text" name="manufacturer" required class="w-full p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <button type="submit" name="add_medicine" class="w-full mt-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    ➕ Add to Inventory
                </button>
            </form>
        </div>

    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 w-full glass">
        &copy; 2026 National Hospital Management System.
    </footer>
</body>
</html>
