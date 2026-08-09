<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Fetch all hospitals for filter
$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$selected_hospital = $_GET['hospital_id'] ?? ($hospitals[0]['id'] ?? 0);

// Fetch Wards and their Beds for the selected hospital
$stmt = $conn->prepare("
    SELECT w.id as ward_id, w.name as ward_name, w.type, w.price_per_day,
           b.id as bed_id, b.bed_number, b.status
    FROM wards w
    LEFT JOIN beds b ON w.id = b.ward_id
    WHERE w.hospital_id = :h_id
    ORDER BY w.type ASC, b.bed_number ASC
");
$stmt->execute([':h_id' => $selected_hospital]);
$ward_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group data by Ward
$wards = [];
foreach($ward_data as $row) {
    $wid = $row['ward_id'];
    if(!isset($wards[$wid])) {
        $wards[$wid] = [
            'name' => $row['ward_name'],
            'type' => $row['type'],
            'price' => $row['price_per_day'],
            'beds' => []
        ];
    }
    if($row['bed_id']) {
        $wards[$wid]['beds'][] = [
            'id' => $row['bed_id'],
            'number' => $row['bed_number'],
            'status' => $row['status']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bed & Ward Management - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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
<body class="bg-slate-50 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1">
        <!-- Sidebar placeholder if needed, or full width. We will just use full width for this module page for better view -->
        <main class="flex-1 p-8 w-full max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">🛏️ Live Bed Occupancy</h2>
                
                <form method="GET" class="flex items-center gap-3">
                    <label class="font-medium text-gray-600 dark:text-gray-300">Select Branch:</label>
                    <select name="hospital_id" onchange="this.form.submit()" class="p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <?php foreach($hospitals as $h): ?>
                            <option value="<?php echo $h['id']; ?>" <?php echo $h['id']==$selected_hospital ? 'selected':''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($wards as $wid => $w): ?>
                <div class="glass p-5 rounded-2xl shadow-lg flex flex-col hover:shadow-xl transition">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($w['name']); ?></h3>
                            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"><?php echo htmlspecialchars($w['type']); ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-mono font-bold text-emerald-600 dark:text-emerald-400">৳<?php echo number_format($w['price'], 0); ?>/day</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3 flex-1 content-start">
                        <?php foreach($w['beds'] as $bed): ?>
                            <?php 
                                $bg = 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-700';
                                if($bed['status'] == 'Occupied') {
                                    $bg = 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-700';
                                } elseif($bed['status'] == 'Maintenance') {
                                    $bg = 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-700';
                                }
                            ?>
                            <div class="border rounded-xl p-3 text-center <?php echo $bg; ?> flex flex-col items-center justify-center cursor-pointer transform hover:scale-105 transition shadow-sm">
                                <svg class="w-6 h-6 mb-1 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span class="font-bold text-sm"><?php echo htmlspecialchars($bed['number']); ?></span>
                                <span class="text-[10px] uppercase tracking-wide opacity-80 font-semibold mt-1"><?php echo $bed['status']; ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($w['beds'])): ?>
                            <div class="col-span-3 text-center text-sm text-gray-500 py-4">No beds added yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($wards)): ?>
                    <div class="col-span-3 p-10 text-center text-gray-500 glass rounded-2xl text-lg font-medium">No wards found for this hospital branch.</div>
                <?php endif; ?>
            </div>
            
        </main>
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
