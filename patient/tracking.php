<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];

// Get patient details
$stmt = $conn->prepare("SELECT p.id, u.created_at as registration_date FROM patients p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient record not found.");
}

$patient_id = $patient['id'];
$events = [];

// 1. Registration Event
$events[] = [
    'date' => date('Y-m-d H:i:s', strtotime($patient['registration_date'])),
    'type' => 'Registration',
    'title' => 'Joined NHIMS Network',
    'description' => 'Your patient account was successfully created.',
    'icon' => '👤',
    'color' => 'bg-blue-500'
];

// 2. Appointments
$stmt = $conn->prepare("SELECT a.appointment_date, a.status, a.appointment_type, d.name as doctor_name, d.specialization FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.patient_id = ?");
$stmt->execute([$patient_id]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        'date' => $row['appointment_date'] . ' 09:00:00',
        'type' => 'Appointment',
        'title' => 'Doctor Appointment: ' . $row['doctor_name'],
        'description' => "Specialist: {$row['specialization']} ({$row['appointment_type']}). Status: {$row['status']}.",
        'icon' => '🩺',
        'color' => 'bg-teal-500'
    ];
}

// 3. Prescriptions
$stmt = $conn->prepare("SELECT p.date_issued, d.name as doctor_name, p.medicines, p.id as prescription_id FROM prescriptions p JOIN doctors d ON p.doctor_id = d.id WHERE p.patient_id = ?");
$stmt->execute([$patient_id]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $med_list = '';
    $meds = json_decode($row['medicines'], true);
    if (is_array($meds)) {
        $names = array_map(function($m) { return is_array($m) ? ($m['name'] ?? 'Medicine') : $m; }, $meds);
        $med_list = implode(', ', array_slice($names, 0, 3));
        if (count($names) > 3) $med_list .= '...';
    } else {
        $med_list = !empty($row['medicines']) ? substr($row['medicines'], 0, 40) : 'Prescribed regimen';
    }
    $events[] = [
        'date' => $row['date_issued'],
        'type' => 'Prescription',
        'title' => 'Prescription Issued (#' . $row['prescription_id'] . ')',
        'description' => "By Dr. {$row['doctor_name']}. Advised: {$med_list}",
        'icon' => '📄',
        'color' => 'bg-emerald-500'
    ];
}

// 4. Clinical Medical Records
$stmt = $conn->prepare("SELECT m.visit_date, m.diagnosis, m.treatment, d.name as doctor_name FROM medical_records m JOIN doctors d ON m.doctor_id = d.id WHERE m.patient_id = ?");
$stmt->execute([$patient_id]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        'date' => $row['visit_date'] . ' 11:00:00',
        'type' => 'Clinical Record',
        'title' => 'Diagnosis: ' . $row['diagnosis'],
        'description' => "Treatment: {$row['treatment']} (Consultant: Dr. {$row['doctor_name']})",
        'icon' => '📋',
        'color' => 'bg-indigo-500'
    ];
}

// 5. Lab Tests
$stmt = $conn->prepare("SELECT l.test_date, s.service_name, l.status, l.result_text FROM laboratory_tests l JOIN lab_services s ON l.service_id = s.id WHERE l.patient_id = ?");
$stmt->execute([$patient_id]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $desc = "Result Status: {$row['status']}";
    if ($row['status'] === 'Completed' && !empty($row['result_text'])) {
        $desc .= " — " . substr($row['result_text'], 0, 40);
    }
    $events[] = [
        'date' => $row['test_date'] . ' 14:00:00',
        'type' => 'Lab Test',
        'title' => 'Medical Test: ' . $row['service_name'],
        'description' => $desc,
        'icon' => '🔬',
        'color' => 'bg-purple-500'
    ];
}

// 6. Billing
$stmt = $conn->prepare("SELECT bill_date, status, total_amount, invoice_number FROM billing WHERE patient_id = ?");
$stmt->execute([$patient_id]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        'date' => $row['bill_date'],
        'type' => 'Billing',
        'title' => 'Invoice: ' . ($row['invoice_number'] ?? 'NHIMS-INV'),
        'description' => "Amount: ৳" . number_format($row['total_amount'], 2) . ". Status: {$row['status']}",
        'icon' => '💳',
        'color' => 'bg-orange-500'
    ];
}

// Sort events by date DESC
usort($events, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Tracking - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen dark:bg-gray-900 dark:text-gray-100">
    <!-- Navbar -->
    <nav class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center border-b border-gray-200 dark:border-slate-700">
        <h1 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 tracking-tight">NHIMS - Patient Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-teal-600 hover:text-blue-600 dark:text-teal-400 font-medium">Dashboard</a>
            <span class="border-l border-teal-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-10 px-4 w-full flex-grow">
        <h2 class="text-3xl font-bold mb-2 text-gray-800 dark:text-white">Your Treatment Journey</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-10">A visual timeline of your medical history and interactions.</p>
        
        <div class="relative border-l-4 border-indigo-200 dark:border-indigo-900/50 ml-6 pl-8 space-y-10">
            <?php foreach($events as $event): ?>
            <div class="relative">
                <div class="absolute -left-[45px] top-1 w-10 h-10 rounded-full flex items-center justify-center text-white text-xl shadow-lg border-4 border-slate-50 dark:border-gray-900 <?= e($event['color']) ?>">
                    <?= e($event['icon']) ?>
                </div>
                
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-gray-100 dark:border-slate-700 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white"><?= e($event['title']) ?></h3>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                            <?= date('M d, Y h:i A', strtotime($event['date'])) ?>
                        </span>
                    </div>
                    <p class="text-sm font-medium text-indigo-500 mb-2"><?= e($event['type']) ?></p>
                    <p class="text-gray-600 dark:text-gray-300"><?= e($event['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
