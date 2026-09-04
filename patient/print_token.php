<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    die("Appointment ID not provided.");
}

$appointment_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get Patient ID
$stmt = $conn->prepare("SELECT id AS patient_id, name, age, phone FROM patients WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

$patient_id = $patient['patient_id'];

// Get Appointment and Doctor Details
$query = "SELECT a.*, d.name as doctor_name, d.specialization, h.name as hospital_name 
          FROM appointments a 
          JOIN doctors d ON a.doctor_id = d.id 
          JOIN hospitals h ON d.hospital_id = h.id
          WHERE a.id = :aid AND a.patient_id = :patient_id AND a.status = 'Confirmed'";
$stmt2 = $conn->prepare($query);
$stmt2->execute([':aid' => $appointment_id, ':patient_id' => $patient_id]);
$appointment = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    die("Appointment not found or not yet confirmed.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Token - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e2e8f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .token-card { 
            background: #ffffff; 
            width: 350px; 
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            overflow: hidden;
            position: relative;
        }
        .token-header {
            background: linear-gradient(135deg, #0f766e, #059669);
            color: white;
            text-align: center;
            padding: 24px 20px;
        }
        .token-body {
            padding: 24px;
            text-align: center;
        }
        .cut-out-left, .cut-out-right {
            position: absolute;
            top: 90px;
            width: 30px;
            height: 30px;
            background: #e2e8f0;
            border-radius: 50%;
        }
        .cut-out-left { left: -15px; }
        .cut-out-right { right: -15px; }
        
        @media print {
            body { background: #ffffff; align-items: flex-start; margin-top: 20px;}
            .token-card { box-shadow: none; border: 2px dashed #cbd5e1; }
            .cut-out-left, .cut-out-right { display: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    
    <div class="absolute top-4 right-4 no-print space-x-2">
        <button onclick="window.print()" class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded shadow-lg transition">🖨️ Print Token</button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded shadow-lg transition">Close</button>
    </div>

    <div class="token-card">
        <div class="token-header">
            <h1 class="text-2xl font-extrabold tracking-tight"><?php echo htmlspecialchars($appointment['hospital_name']); ?></h1>
            <p class="text-teal-100 text-sm mt-1 uppercase tracking-widest font-semibold">Queue Token</p>
        </div>
        
        <div class="cut-out-left"></div>
        <div class="cut-out-right"></div>
        
        <div class="border-b-2 border-dashed border-gray-200 mx-6 my-2"></div>
        
        <div class="token-body">
            <p class="text-gray-500 text-sm font-semibold mb-1">YOUR SERIAL NUMBER</p>
            <h2 class="text-6xl font-extrabold text-teal-600 mb-6 drop-shadow-sm"><?php echo str_pad($appointment['id'] % 100, 2, '0', STR_PAD_LEFT); ?></h2>
            
            <div class="bg-gray-50 rounded-lg p-4 text-left border border-gray-100">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-bold mb-1">Patient Name</p>
                <p class="text-gray-800 font-bold mb-3"><?php echo htmlspecialchars($patient['name']); ?></p>
                
                <p class="text-xs text-gray-500 uppercase tracking-wide font-bold mb-1">Consulting Doctor</p>
                <p class="text-gray-800 font-bold"><?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($appointment['specialization']); ?></p>
                
                <p class="text-xs text-gray-500 uppercase tracking-wide font-bold mb-1">Date & Time</p>
                <p class="text-gray-800 font-bold"><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></p>
                <p class="text-sm text-gray-600">Morning Shift (9:00 AM - 1:00 PM)</p>
            </div>
            
            <div class="mt-6">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=NHIMS-APT-<?php echo $appointment['id']; ?>" alt="QR Code" class="mx-auto rounded-lg shadow-sm border border-gray-100 p-1 bg-white">
                <p class="text-xs text-gray-400 mt-2">Scan to verify appointment</p>
            </div>
        </div>
    </div>

</body>
</html>
