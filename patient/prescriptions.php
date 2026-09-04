<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id AS patient_id FROM patients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($patient) {
    $patient_id = $patient['patient_id'];
    $query = "SELECT p.id as prescription_id, d.name as doctor_name, p.medicines, p.date_issued 
              FROM prescriptions p 
              JOIN doctors d ON p.doctor_id = d.id 
              WHERE p.patient_id = :patient_id 
              ORDER BY p.date_issued DESC";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindParam(':patient_id', $patient_id);
    $stmt2->execute();
    $prescriptions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $prescriptions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - NHIMS</title>
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
            <a href="prescriptions.php" class="text-blue-600 font-bold transition">Prescriptions</a>
            <a href="lab_tests.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Lab Reports</a>
            <span class="border-l border-teal-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto w-full mt-10 p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-100">My Prescriptions</h2>
        <div class="glass rounded-2xl shadow-xl overflow-hidden border border-white/50">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">Date</th>
                        <th class="p-4 border-b">Doctor</th>
                        <th class="p-4 border-b">Medicines & Dosage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if(!empty($prescriptions)): ?>
                        <?php foreach($prescriptions as $p): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition">
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400 align-top"><?php echo date('M d, Y', strtotime($p['date_issued'])); ?></td>
                            <td class="p-4 font-bold text-teal-700 dark:text-teal-400 align-top"><?php echo htmlspecialchars($p['doctor_name']); ?></td>
                            <td class="p-4 text-sm text-gray-800 dark:text-gray-200">
                                <?php 
                                    $medicines = json_decode($p['medicines'], true);
                                    if(is_array($medicines) && !empty($medicines)) {
                                        echo "<p class='font-semibold text-blue-600 dark:text-blue-400 mb-1'>Medicines:</p>";
                                        echo "<ul class='list-disc pl-5 space-y-1 mb-3'>";
                                        foreach($medicines as $med) {
                                            $medName = htmlspecialchars($med['name'] ?? $med['medicine'] ?? 'Unknown');
                                            $dosage = htmlspecialchars($med['dosage'] ?? '');
                                            echo "<li><span class='font-semibold'>{$medName}</span> - <span class='text-gray-600 dark:text-gray-400'>{$dosage}</span></li>";
                                        }
                                        echo "</ul>";
                                    } elseif(!empty($p['medicines']) && !is_array($medicines)) {
                                        echo "<p class='font-semibold text-blue-600 dark:text-blue-400 mb-1'>Medicines:</p>";
                                        echo "<div class='mb-3'>" . nl2br(htmlspecialchars($p['medicines'])) . "</div>";
                                    }

                                    if(!empty($p['tests'])) {
                                        echo "<p class='font-semibold text-purple-600 dark:text-purple-400 mb-1'>Prescribed Lab Tests:</p>";
                                        echo "<div class='text-gray-600 dark:text-gray-400 mb-3 whitespace-pre-wrap'>" . htmlspecialchars($p['tests']) . "</div>";
                                    }
                                ?>
                                <a href="print_prescription.php?id=<?php echo $p['prescription_id']; ?>" target="_blank" class="inline-flex items-center text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg transition-colors mt-2">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Download PDF
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="p-6 text-center text-gray-500">No prescriptions found.</td></tr>
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
