<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$selected_hospital = $_GET['hospital_id'] ?? ($hospitals[0]['id'] ?? 0);

// Fetch Blood Bank Data
$stmt = $conn->prepare("SELECT * FROM blood_bank WHERE hospital_id = :h_id ORDER BY blood_group ASC");
$stmt->execute([':h_id' => $selected_hospital]);
$blood_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Donors
$stmt = $conn->prepare("SELECT * FROM blood_donors WHERE hospital_id = :h_id ORDER BY last_donation_date DESC LIMIT 10");
$stmt->execute([':h_id' => $selected_hospital]);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #ef4444, #dc2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #f87171, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <main class="flex-1 p-8 w-full max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">🩸 Blood Bank Inventory</h2>
            
            <form method="GET" class="flex items-center gap-3">
                <label class="font-medium text-gray-600 dark:text-gray-300">Select Branch:</label>
                <select name="hospital_id" onchange="this.form.submit()" class="p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 outline-none focus:ring-2 focus:ring-red-500 shadow-sm">
                    <?php foreach($hospitals as $h): ?>
                        <option value="<?php echo $h['id']; ?>" <?php echo $h['id']==$selected_hospital ? 'selected':''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <?php foreach($blood_stock as $stock): ?>
                <?php
                    $bags = $stock['bags_available'];
                    $bgClass = $bags < 10 ? 'border-red-500 from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30' : 'border-emerald-500 from-white to-emerald-50 dark:from-slate-800 dark:to-slate-700';
                    $textClass = $bags < 10 ? 'text-red-700 dark:text-red-400' : 'text-gray-800 dark:text-white';
                ?>
                <div class="glass p-6 rounded-2xl shadow border-b-4 <?php echo $bgClass; ?> flex flex-col items-center justify-center bg-gradient-to-br">
                    <div class="text-4xl font-black mb-2 text-red-600 drop-shadow-sm"><?php echo htmlspecialchars($stock['blood_group']); ?></div>
                    <div class="text-2xl font-bold <?php echo $textClass; ?>"><?php echo $bags; ?> <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Bags</span></div>
                    <?php if($bags < 10): ?>
                        <div class="mt-2 text-xs font-bold bg-red-200 text-red-800 px-2 py-1 rounded uppercase tracking-wider animate-pulse">Critical Low</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if(empty($blood_stock)): ?>
                <div class="col-span-4 p-10 text-center text-gray-500 glass rounded-2xl">No blood inventory found for this branch.</div>
            <?php endif; ?>
        </div>

        <div class="glass p-6 rounded-2xl shadow-lg mt-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Recent Donors</h3>
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition text-sm font-bold">➕ Add Donor</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                            <th class="p-3 border-b dark:border-gray-700">Name</th>
                            <th class="p-3 border-b dark:border-gray-700">Blood Group</th>
                            <th class="p-3 border-b dark:border-gray-700">Phone</th>
                            <th class="p-3 border-b dark:border-gray-700">Last Donation Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <?php foreach($donors as $donor): ?>
                        <tr class="hover:bg-red-50 dark:hover:bg-slate-700 transition">
                            <td class="p-3 text-sm font-semibold"><?php echo htmlspecialchars($donor['name']); ?></td>
                            <td class="p-3 text-sm font-bold text-red-600 dark:text-red-400"><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                            <td class="p-3 text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($donor['phone']); ?></td>
                            <td class="p-3 text-sm text-gray-600 dark:text-gray-400"><?php echo $donor['last_donation_date'] ? date('M d, Y', strtotime($donor['last_donation_date'])) : 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($donors)): ?>
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">No donor records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

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
