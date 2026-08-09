<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$selected_hospital = $_GET['hospital_id'] ?? ($hospitals[0]['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT f.*, p.name as patient_name 
    FROM patient_feedback f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.hospital_id = :h_id 
    ORDER BY f.created_at DESC
");
$stmt->execute([':h_id' => $selected_hospital]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Feedback - Admin</title>
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

    <main class="flex-1 p-8 w-full max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">⭐ Patient Feedback</h2>
            
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
            <?php foreach($feedbacks as $fb): ?>
                <div class="glass p-6 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($fb['patient_name']); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo date('M d, Y', strtotime($fb['created_at'])); ?></div>
                    </div>
                    <div class="flex gap-1 mb-3">
                        <?php for($i=0; $i<5; $i++): ?>
                            <?php if($i < $fb['rating']): ?>
                                <span class="text-yellow-400 text-lg">⭐</span>
                            <?php else: ?>
                                <span class="text-gray-300 dark:text-gray-600 text-lg">⭐</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm italic flex-1">"<?php echo nl2br(htmlspecialchars($fb['review'])); ?>"</p>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($feedbacks)): ?>
                <div class="col-span-3 p-10 text-center text-gray-500 glass rounded-2xl">No feedback received for this branch yet.</div>
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
