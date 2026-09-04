<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    die("Prescription ID not provided.");
}

$prescription_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get Patient ID
$stmt = $conn->prepare("SELECT id AS patient_id, name, age, gender, phone, blood_group FROM patients WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

$patient_id = $patient['patient_id'];

// Get Prescription and Doctor Details
$query = "SELECT p.*, d.name as doctor_name, d.specialization, h.name as hospital_name, h.location as hospital_location, h.contact_number as hospital_contact 
          FROM prescriptions p 
          JOIN doctors d ON p.doctor_id = d.id 
          JOIN hospitals h ON d.hospital_id = h.id
          WHERE p.id = :pid AND p.patient_id = :patient_id";
$stmt2 = $conn->prepare($query);
$stmt2->execute([':pid' => $prescription_id, ':patient_id' => $patient_id]);
$prescription = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    die("Prescription not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - NHIMS</title>
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
            min-height: 1050px; /* Standard A4 height approximation */
            position: relative;
        }
        @media print {
            body { background: #ffffff; margin: 0; padding: 0; }
            .pad-container { box-shadow: none; margin: 0; padding: 20px; width: 100%; height: 100%; }
            .no-print { display: none !important; }
        }
        .header-border { border-bottom: 2px solid #3b82f6; }
        .footer-border { border-top: 2px solid #3b82f6; }
    </style>
</head>
<body>
    
    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-lg mr-4">Print / Save as PDF</button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow-lg">Close</button>
    </div>

    <div class="pad-container flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center header-border pb-4 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-blue-700"><?php echo htmlspecialchars($prescription['hospital_name']); ?></h1>
                <p class="text-gray-600 text-sm mt-1"><?php echo htmlspecialchars($prescription['hospital_location']); ?></p>
                <p class="text-gray-600 text-sm">Contact: <?php echo htmlspecialchars($prescription['hospital_contact']); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($prescription['doctor_name']); ?></h2>
                <p class="text-blue-600 font-semibold"><?php echo htmlspecialchars($prescription['specialization']); ?> Specialist</p>
            </div>
        </div>

        <!-- Patient Info -->
        <div class="flex justify-between text-sm bg-blue-50 p-4 rounded-lg mb-8">
            <div>
                <p><span class="font-semibold">Patient Name:</span> <?php echo htmlspecialchars($patient['name']); ?></p>
                <p><span class="font-semibold">Age/Gender:</span> <?php echo htmlspecialchars($patient['age']) . ' / ' . htmlspecialchars($patient['gender']); ?></p>
                <p><span class="font-semibold">Phone:</span> <?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></p>
            </div>
            <div class="text-right">
                <p><span class="font-semibold">Date:</span> <?php echo date('d M Y, h:i A', strtotime($prescription['date_issued'])); ?></p>
                <p><span class="font-semibold">Prescription ID:</span> #<?php echo str_pad($prescription['id'], 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <!-- RX Symbol -->
        <div class="text-4xl font-serif font-bold text-gray-800 mb-6">
            &#8478;
        </div>

        <!-- Content Area -->
        <div class="flex-grow grid grid-cols-12 gap-8">
            
            <!-- Left Column: Lab Tests -->
            <div class="col-span-4 border-r border-gray-200 pr-4">
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Advised Investigations</h3>
                <?php if(!empty($prescription['tests'])): ?>
                    <div class="text-gray-700 text-sm whitespace-pre-wrap leading-relaxed"><?php echo htmlspecialchars($prescription['tests']); ?></div>
                <?php else: ?>
                    <p class="text-gray-400 text-sm italic">None</p>
                <?php endif; ?>
            </div>

            <!-- Right Column: Medicines -->
            <div class="col-span-8 pl-4">
                <h3 class="font-bold text-gray-700 border-b pb-2 mb-4">Medicines</h3>
                <?php 
                    $medicines = json_decode($prescription['medicines'], true);
                    if(is_array($medicines) && !empty($medicines)) {
                        echo "<ol class='list-decimal pl-5 space-y-4'>";
                        foreach($medicines as $med) {
                            $medName = htmlspecialchars($med['name'] ?? $med['medicine'] ?? 'Unknown');
                            $dosage = htmlspecialchars($med['dosage'] ?? '');
                            echo "<li class='text-gray-800 text-lg'><span class='font-bold'>{$medName}</span>";
                            if (!empty($dosage)) {
                                echo "<p class='text-sm text-gray-600 mt-1 italic'>&#10148; {$dosage}</p>";
                            }
                            echo "</li>";
                        }
                        echo "</ol>";
                    } elseif(!empty($prescription['medicines']) && !is_array($medicines)) {
                        echo "<div class='text-gray-800 whitespace-pre-wrap text-lg leading-loose'>" . htmlspecialchars($prescription['medicines']) . "</div>";
                    } else {
                        echo "<p class='text-gray-400 italic'>No medicines prescribed.</p>";
                    }
                ?>
            </div>

        </div>

        <!-- Footer -->
        <div class="footer-border pt-4 mt-8 flex justify-between items-end">
            <div class="text-xs text-gray-500">
                <p>This is a computer-generated document from NHIMS.</p>
                <p>Please bring this prescription on your next visit.</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-800 w-32 mx-auto mb-1"></div>
                <p class="text-sm font-semibold text-gray-800">Doctor's Signature</p>
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
