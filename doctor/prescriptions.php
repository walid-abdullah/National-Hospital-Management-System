<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id as doctor_id FROM doctors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doctor) {
    $doctor_id = $doctor['doctor_id'];
    $query = "SELECT p.prescription_id, pat.name AS patient_name, p.medicine, p.dosage, app.appointment_date 
              FROM prescriptions p 
              JOIN patients pat ON p.patient_id = pat.patient_id 
              LEFT JOIN appointments app ON app.patient_id = pat.id AND app.doctor_id = p.doctor_id
              WHERE p.doctor_id = :doctor_id 
              GROUP BY p.prescription_id
              ORDER BY p.prescription_id DESC";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindParam(':doctor_id', $doctor_id);
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
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS Doctor</h1>
        <div class="flex items-center space-x-4">
            <span class="border-l border-blue-400 h-6 mx-2"></span>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm text-white transition shadow">Logout</a>
        </div>
    </nav>

    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-slate-800 shadow-lg hidden md:block">
            <ul class="p-4 space-y-2">
                <li><a href="dashboard.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Dashboard</a></li>
                <li><a href="appointments.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Appointments</a></li>
                <li><a href="medical_records.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Medical Records</a></li>
                <li><a href="prescriptions.php" class="block p-3 bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-400 rounded-lg font-semibold">Prescriptions</a></li>
            </ul>
        </aside>

        <main class="flex-1 p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Issued Prescriptions</h2>
                <a href="add_prescription.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">Write New Prescription</a>
            </div>

            <div class="glass rounded-2xl shadow-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                            <th class="p-4 border-b">ID</th>
                            <th class="p-4 border-b">Patient</th>
                            <th class="p-4 border-b">Medicine</th>
                            <th class="p-4 border-b">Dosage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php if(!empty($prescriptions)): ?>
                            <?php foreach($prescriptions as $p): ?>
                            <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition">
                                <td class="p-4 text-sm">#<?php echo $p['prescription_id']; ?></td>
                                <td class="p-4 font-semibold text-blue-700 dark:text-blue-400"><?php echo htmlspecialchars($p['patient_name']); ?></td>
                                <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($p['medicine']); ?></td>
                                <td class="p-4 text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($p['dosage']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="p-6 text-center text-gray-500">No prescriptions issued yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
