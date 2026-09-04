<?php
require_once __DIR__ . '/includes/security.php';
init_secure_session();
require_once __DIR__ . '/config/db.php';

// Fetch hospitals
$stmt = $conn->query("SELECT id, name, location FROM hospitals");
$hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctors
$stmt = $conn->query("SELECT id, name, specialization, hospital_id FROM doctors");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error_msg = 'Invalid security token. Please try again.';
    } else {
    $hospital_id = $_POST['hospital_id'];
    $appointment_type = $_POST['appointment_type'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    
    // Patient Info
    $name = $_POST['name'];
    $phone = $_POST['phone']; // Used as username
    $password = $_POST['password'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $disease = $_POST['disease']; // Stored as symptoms in appointment? Actually we don't have disease in new schema, we'll append to billing or create a record later. Wait, we don't have disease in appointments table. I'll put it in medical_records later, for now ignore or just skip.
    // Wait, let's just make sure patient is registered.

    try {
        $conn->beginTransaction();

        // 1. Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $phone]);
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $user_id = $existing_user['id'];
            // Get patient_id
            $stmt = $conn->prepare("SELECT id FROM patients WHERE user_id = :uid LIMIT 1");
            $stmt->execute(['uid' => $user_id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            if($patient) {
                $patient_id = $patient['id'];
            } else {
                throw new Exception("Account exists but patient record is missing.");
            }
        } else {
            // 2. Create User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status) VALUES (?, ?, ?, 'Patient', 'Approved')");
            $stmt->execute([$hospital_id, $phone, $hashed_password]);
            $user_id = $conn->lastInsertId();

            // 3. Create Patient
            $stmt = $conn->prepare("INSERT INTO patients (user_id, hospital_id, name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?, 'Self Registered via Web')");
            $stmt->execute([$user_id, $hospital_id, $name, $age, $gender, $phone]);
            $patient_id = $conn->lastInsertId();
        }

        // 4. Create Appointment
        $stmt = $conn->prepare("INSERT INTO appointments (hospital_id, patient_id, doctor_id, appointment_date, appointment_type, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$hospital_id, $patient_id, $doctor_id, $appointment_date, $appointment_type]);
        $appointment_id = $conn->lastInsertId();
        
        // 5. Generate basic bill (Pending)
        // Wait, bill generation usually happens after consult, but let's just generate an unpaid bill.
        $stmt = $conn->prepare("INSERT INTO billing (hospital_id, patient_id, invoice_number, total_amount, payment_method, status, details) VALUES (?, ?, ?, 1000.00, 'Cash', 'Unpaid', '{\"type\": \"Consultation Fee\"}')");
        $stmt->execute([$hospital_id, $patient_id, 'INV-'.time()]);

        $conn->commit();
        $success_msg = "Appointment Request Submitted Successfully! You can now log into the portal using your Phone Number as Username and the Password you just created to check the status.";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Error booking appointment: " . $e->getMessage();
    }
    }
}

require_once 'includes/header_public.php';

$pre_hospital_id = isset($_GET['hospital_id']) ? $_GET['hospital_id'] : '';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">
    
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Book an Appointment</h1>
        <p class="text-gray-600 dark:text-gray-400">Hybrid Hospital Network: Choose Physical Visit or Telemedicine</p>
    </div>

    <div class="glass p-8 md:p-10 rounded-3xl shadow-2xl border border-white/50 dark:border-white/10 relative overflow-hidden">
        
        <div class="relative z-10">
            <?php if($success_msg): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-6 rounded-lg mb-8 shadow-sm">
                    <h3 class="text-lg font-bold">Success!</h3>
                    <p><?php echo $success_msg; ?></p>
                    <a href="login.php" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Go to Portal Login</a>
                </div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 rounded-lg mb-8">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <?php if(!$success_msg): ?>
            <form action="book_online.php" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Info -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">1. Personal Details</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white" placeholder="John Doe">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone Number (Your Username)</label>
                            <input type="text" name="phone" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white" placeholder="017XXXXXXXX">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Set a Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white" placeholder="••••••••">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Age</label>
                                <input type="number" name="age" required min="1" max="120" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gender</label>
                                <select name="gender" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white [&>option]:text-gray-900">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Info -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">2. Appointment Details</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Select Branch</label>
                            <select name="hospital_id" id="hospitalSelect" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                                <option value="" disabled <?php if(!$pre_hospital_id) echo 'selected'; ?>>Choose a hospital branch...</option>
                                <?php foreach($hospitals as $h): ?>
                                    <option value="<?php echo $h['id']; ?>" <?php if($pre_hospital_id == $h['id']) echo 'selected'; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Appointment Type</label>
                            <select name="appointment_type" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                                <option value="Physical">In-Person Visit (Physical)</option>
                                <option value="Telemedicine">Telemedicine (Online Video Call)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Select Doctor</label>
                            <select name="doctor_id" id="doctorSelect" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                                <option value="" disabled selected>First select a branch...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Preferred Date</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                        </div>
                        
                        <!-- Simple JS to filter doctors by hospital -->
                        <script>
                            const allDoctors = <?php echo json_encode($doctors); ?>;
                            const hSelect = document.getElementById('hospitalSelect');
                            const dSelect = document.getElementById('doctorSelect');
                            
                            function filterDoctors() {
                                const hId = hSelect.value;
                                dSelect.innerHTML = '<option value="" disabled selected>Choose a doctor...</option>';
                                const filtered = allDoctors.filter(d => d.hospital_id == hId);
                                filtered.forEach(d => {
                                    dSelect.innerHTML += `<option value="${d.id}">Dr. ${d.name} (${d.specialization})</option>`;
                                });
                            }

                            hSelect.addEventListener('change', filterDoctors);
                            
                            // Auto trigger if hospital is pre-selected
                            if(hSelect.value) {
                                filterDoctors();
                            }
                        </script>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 text-lg">
                        Confirm Appointment Request
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer_public.php';
?>
