<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("Test ID not provided.");
}

$test_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get Patient ID
$stmt = $conn->prepare("SELECT id, name, age, gender, phone, blood_group FROM patients WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

$patient_id = $patient['id'];

// Get Lab Test Details
$query = "SELECT l.*, s.service_name, h.name as hospital_name, h.location as hospital_location, h.contact_number as hospital_contact 
          FROM laboratory_tests l 
          JOIN lab_services s ON l.service_id = s.id 
          JOIN hospitals h ON l.hospital_id = h.id
          WHERE l.id = :tid AND l.patient_id = :patient_id AND l.status = 'Completed'";
$stmt2 = $conn->prepare($query);
$stmt2->execute([':tid' => $test_id, ':patient_id' => $patient_id]);
$test = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$test) {
    die("Report not found, access denied, or test is not completed yet.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e2e8f0; }
        .pad-container { 
            background: #ffffff; 
            max-width: 800px; 
            margin: 40px auto; 
            padding: 40px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            min-height: 1050px; 
            position: relative;
        }
        @media print {
            body { background: #ffffff; margin: 0; padding: 0; }
            .pad-container { box-shadow: none; margin: 0; padding: 20px; width: 100%; height: 100%; }
            .no-print { display: none !important; }
        }
        .header-border { border-bottom: 2px solid #9333ea; }
        .footer-border { border-top: 2px solid #9333ea; }
    </style>
</head>
<body>
    
    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded shadow-lg mr-4">Print / Save as PDF</button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow-lg">Close</button>
    </div>

    <div class="pad-container flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center header-border pb-4 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-purple-700"><?php echo htmlspecialchars($test['hospital_name']); ?></h1>
                <p class="text-gray-600 text-sm mt-1">Diagnostic Center & Laboratory</p>
                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($test['hospital_location']); ?></p>
                <p class="text-gray-600 text-sm">Contact: <?php echo htmlspecialchars($test['hospital_contact']); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800">Laboratory Report</h2>
                <p class="text-purple-600 font-semibold">Official Document</p>
            </div>
        </div>

        <!-- Patient Info -->
        <div class="flex justify-between text-sm bg-purple-50 p-4 rounded-lg mb-8">
            <div>
                <p><span class="font-semibold">Patient Name:</span> <?php echo htmlspecialchars($patient['name']); ?></p>
                <p><span class="font-semibold">Age/Gender:</span> <?php echo htmlspecialchars($patient['age']) . ' / ' . htmlspecialchars($patient['gender']); ?></p>
            </div>
            <div class="text-right">
                <p><span class="font-semibold">Test Date:</span> <?php echo date('d M Y', strtotime($test['test_date'])); ?></p>
                <p><span class="font-semibold">Report ID:</span> #<?php echo str_pad($test['id'], 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-xl font-bold text-gray-800 border-b-2 border-gray-200 pb-2 inline-block">Test Name: <?php echo htmlspecialchars($test['service_name']); ?></h3>
        </div>

        <!-- Content Area -->
        <div class="flex-grow">
            <h4 class="font-bold text-gray-700 mb-4 uppercase tracking-wide text-sm">Clinical Findings / Results</h4>
            <div class="text-gray-800 text-lg leading-relaxed whitespace-pre-wrap p-6 border border-gray-100 rounded-xl bg-gray-50 min-h-[300px]">
                <?php echo htmlspecialchars($test['result_text'] ?? 'Pending'); ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-border pt-4 mt-8 flex justify-between items-end">
            <div class="text-xs text-gray-500">
                <p>This is a computer-generated laboratory report from NHIMS.</p>
                <p>Please consult your referring doctor for clinical correlation.</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-800 w-40 mx-auto mb-1 text-xs text-gray-600 pb-1">Verified Electronically</div>
                <p class="text-sm font-semibold text-gray-800">Lab Incharge</p>
            </div>
        </div>
    </div>

    <script>
        // Auto print when loaded
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
