<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$selected_hospital = $_GET['hospital_id'] ?? ($hospitals[0]['id'] ?? 0);

// Handle Dispatch Action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dispatch_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $dispatch_id = $_POST['dispatch_id'];
    $stmt = $conn->prepare("UPDATE ambulances SET status = 'On-Trip' WHERE id = ? AND hospital_id = ?");
    $stmt->execute([$dispatch_id, $selected_hospital]);
    $msg = "Ambulance dispatched successfully!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $return_id = $_POST['return_id'];
    $stmt = $conn->prepare("UPDATE ambulances SET status = 'Available' WHERE id = ? AND hospital_id = ?");
    $stmt->execute([$return_id, $selected_hospital]);
    $msg = "Ambulance returned to base.";
}

// Fetch Ambulances
$stmt = $conn->prepare("SELECT * FROM ambulances WHERE hospital_id = :h_id ORDER BY status ASC");
$stmt->execute([':h_id' => $selected_hospital]);
$ambulances = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambulance Dispatch - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #eab308, #ca8a04); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #fde047, #eab308); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <main class="flex-1 p-8 w-full max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">🚑 Ambulance Dispatch</h2>
            
            <form method="GET" class="flex items-center gap-3">
                <label class="font-medium text-gray-600 dark:text-gray-300">Select Branch:</label>
                <select name="hospital_id" onchange="this.form.submit()" class="p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 outline-none focus:ring-2 focus:ring-yellow-500 shadow-sm">
                    <?php foreach($hospitals as $h): ?>
                        <option value="<?php echo $h['id']; ?>" <?php echo $h['id']==$selected_hospital ? 'selected':''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if(isset($msg)): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 shadow-sm border border-green-200"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($ambulances as $amb): ?>
                <?php
                    $isAvailable = $amb['status'] === 'Available';
                    $bg = $isAvailable ? 'border-emerald-500' : 'border-rose-500';
                    $btnTxt = $isAvailable ? 'Dispatch' : 'Mark Returned';
                    $btnColor = $isAvailable ? 'bg-rose-500 hover:bg-rose-600 text-white' : 'bg-emerald-500 hover:bg-emerald-600 text-white';
                    $actionName = $isAvailable ? 'dispatch_id' : 'return_id';
                ?>
                <div class="glass p-6 rounded-2xl shadow-lg border-t-4 <?php echo $bg; ?> flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white font-mono"><?php echo htmlspecialchars($amb['vehicle_number']); ?></h3>
                            <span class="text-xs font-semibold px-2 py-1 rounded <?php echo $isAvailable ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'; ?>">
                                <?php echo htmlspecialchars($amb['status']); ?>
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <strong>Driver:</strong> <?php echo htmlspecialchars($amb['driver_name']); ?>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($amb['driver_phone']); ?>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="<?php echo $actionName; ?>" value="<?php echo $amb['id']; ?>">
                        <button type="submit" class="w-full py-3 rounded-xl font-bold shadow-md transition-transform transform hover:-translate-y-0.5 <?php echo $btnColor; ?>">
                            <?php echo $isAvailable ? '🚀 ' : '✅ '; ?> <?php echo $btnTxt; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($ambulances)): ?>
                <div class="col-span-3 p-10 text-center text-gray-500 glass rounded-2xl">No ambulances registered for this branch.</div>
            <?php endif; ?>
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
