<?php
require_once '../includes/security.php';
init_secure_session();
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
$doctor_id = $doctor['doctor_id'];

// Get patients assigned to this doctor
$stmt_pat = $conn->prepare("SELECT DISTINCT p.id as patient_id, p.name FROM patients p JOIN appointments a ON p.id = a.patient_id WHERE a.doctor_id = :did");
$stmt_pat->bindParam(':did', $doctor_id);
$stmt_pat->execute();
$patients = $stmt_pat->fetchAll(PDO::FETCH_ASSOC);

$selected_patient_id = '';
if (isset($_GET['apt_id'])) {
    $apt_id = intval($_GET['apt_id']);
    $stmt_apt = $conn->prepare("SELECT patient_id FROM appointments WHERE id = ?");
    $stmt_apt->execute([$apt_id]);
    $apt_data = $stmt_apt->fetch(PDO::FETCH_ASSOC);
    if ($apt_data) {
        $selected_patient_id = $apt_data['patient_id'];
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Invalid security token. Please try again.</div>";
    } else {
    $patient_id = $_POST['patient_id'];
    $medicine = trim($_POST['medicine'] ?? '');
    $dosage = trim($_POST['dosage'] ?? '');
    $tests = trim($_POST['tests'] ?? '');

    if (!empty($patient_id) && (!empty($medicine) || !empty($tests))) {
        try {
            $authorized = $conn->prepare(
                "SELECT 1 FROM appointments WHERE patient_id = :patient_id AND doctor_id = :doctor_id LIMIT 1"
            );
            $authorized->execute([':patient_id' => $patient_id, ':doctor_id' => $doctor_id]);
            if (!$authorized->fetchColumn()) {
                throw new RuntimeException('You are not authorized to prescribe for this patient.');
            }

            $medicines_json = null;
            if (!empty($medicine)) {
                $medicines_json = json_encode([['name' => $medicine, 'dosage' => $dosage]]);
            }
            $insert = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, medicines, tests) VALUES (?, ?, ?, ?)");
            $insert->execute([$patient_id, $doctor_id, $medicines_json, empty($tests) ? null : $tests]);
            $msg = "<div class='bg-green-100 text-green-700 p-4 rounded mb-4'>Prescription issued successfully!</div>";
        } catch(PDOException $e) {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Unable to issue prescription.</div>";
        } catch (RuntimeException $e) {
            $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>" . e($e->getMessage()) . "</div>";
        }
    } else {
        $msg = "<div class='bg-red-100 text-red-700 p-4 rounded mb-4'>Please prescribe at least one Medicine or Lab Test.</div>";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Prescription - NHIMS</title>
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
            <a href="prescriptions.php" class="text-blue-500 hover:underline text-sm font-semibold">&larr; Back to Prescriptions</a>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto w-full mt-12 p-6">
        <div class="glass p-8 rounded-2xl shadow-xl">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">Write New Prescription</h2>
            <?php echo $msg; ?>
            <form action="" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Patient</label>
                    <select name="patient_id" required class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="" disabled selected>Choose a patient...</option>
                        <?php foreach($patients as $pat): ?>
                            <option value="<?php echo $pat['patient_id']; ?>" <?php echo ($selected_patient_id == $pat['patient_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($pat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Medicine (Optional)</label>
                    <textarea name="medicine" rows="2" placeholder="e.g., Paracetamol 500mg, Amoxicillin" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Dosage Instructions</label>
                    <input type="text" name="dosage" placeholder="e.g., 1-1-1 (After meal) for 7 days" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Lab Tests (Optional)</label>
                    <textarea name="tests" rows="2" placeholder="e.g., Complete Blood Count (CBC), Chest X-Ray" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow">Submit Prescription</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
