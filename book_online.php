<?php
require_once 'config/db.php';

// Fetch doctors for the dropdown
try {
    $stmt = $conn->prepare("SELECT doctor_id, doctor_name, specialization FROM doctors ORDER BY doctor_name ASC");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $doctors = [];
}

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone']; // Used as username
    $password = $_POST['password']; // Set a password for portal access
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $disease = $_POST['disease'];

    try {
        $conn->beginTransaction();

        // 1. Check if user already exists based on phone (username)
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $phone);
        $stmt->execute();
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $user_id = $existing_user['user_id'];
            // Get patient_id
            $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = :uid LIMIT 1");
            $stmt->bindParam(':uid', $user_id);
            $stmt->execute();
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            if($patient) {
                $patient_id = $patient['patient_id'];
            } else {
                throw new Exception("Account exists but patient record is missing.");
            }
        } else {
            // 2. Create User
            $hashed_password = md5($password);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'Patient')");
            $stmt->bindParam(':username', $phone);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            $user_id = $conn->lastInsertId();

            // 3. Create Patient
            $stmt = $conn->prepare("INSERT INTO patients (user_id, name, age, gender, phone, address) VALUES (:uid, :name, :age, :gender, :phone, 'Self Registered via Web')");
            $stmt->bindParam(':uid', $user_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            $patient_id = $conn->lastInsertId();
        }

        // 4. Create Appointment (Status = Pending)
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, status, disease) VALUES (:pid, :did, :date, 'Pending', :disease)");
        $stmt->bindParam(':pid', $patient_id);
        $stmt->bindParam(':did', $doctor_id);
        $stmt->bindParam(':date', $appointment_date);
        $stmt->bindParam(':disease', $disease);
        $stmt->execute();

        $conn->commit();
        $success_msg = "Appointment Request Submitted Successfully! You can now log into the portal using your Phone Number as Username and the Password you just created to check the status.";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = "Error booking appointment: " . $e->getMessage();
    }
}

require_once 'includes/header_public.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">
    
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Book an Appointment</h1>
        <p class="text-gray-600 dark:text-gray-400">Fill out the form below to request a consultation. A patient portal account will be automatically created for you.</p>
    </div>

    <div class="glass p-8 md:p-10 rounded-3xl shadow-2xl border border-white/50 dark:border-white/10 relative overflow-hidden">
        
        <!-- Decorative Blurs -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-400/20 dark:bg-blue-600/20 rounded-full filter blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-400/20 dark:bg-indigo-600/20 rounded-full filter blur-3xl"></div>

        <div class="relative z-10">
            <?php if($success_msg): ?>
                <div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-6 rounded-lg mb-8 shadow-sm">
                    <div class="flex items-center mb-2">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-lg font-bold">Success!</h3>
                    </div>
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
                            <p class="text-xs text-gray-500 mt-1">This will be used as your login username.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Set a Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white" placeholder="••••••••">
                            <p class="text-xs text-gray-500 mt-1">To log into the portal and check your reports later.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Age</label>
                                <input type="number" name="age" required min="1" max="120" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gender</label>
                                <select name="gender" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white [&>option]:text-gray-900">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Info -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">2. Appointment Details</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Select Doctor</label>
                            <select name="doctor_id" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white [&>option]:text-gray-900">
                                <option value="" disabled selected>Choose a specialist...</option>
                                <?php foreach($doctors as $doc): ?>
                                    <option value="<?php echo $doc['doctor_id']; ?>" <?php echo (isset($_GET['doctor_id']) && $_GET['doctor_id'] == $doc['doctor_id']) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($doc['doctor_name']); ?> (<?php echo htmlspecialchars($doc['specialization']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Preferred Date</label>
                            <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Symptoms / Reason</label>
                            <textarea name="disease" rows="4" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white" placeholder="Please briefly describe your symptoms or reason for visit..."></textarea>
                        </div>
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
