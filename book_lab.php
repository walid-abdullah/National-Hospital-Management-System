<?php
require_once __DIR__ . '/includes/security.php';
init_secure_session();
require_once __DIR__ . '/config/db.php';

$success_msg = '';
$error_msg = '';
$pre_selected_test = isset($_GET['test_name']) ? $_GET['test_name'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error_msg = 'Invalid security token. Please try again.';
    } else {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $test_name = $_POST['test_name'];
    $test_date = $_POST['test_date'];
    $collection_type = $_POST['collection_type'];

    try {
        $conn->beginTransaction();

        $service = $conn->prepare("SELECT id, hospital_id FROM lab_services WHERE service_name = :service_name OR service_name LIKE :service_search LIMIT 1");
        $service->execute([
            ':service_name' => $test_name,
            ':service_search' => $test_name . '%',
        ]);
        $service_data = $service->fetch(PDO::FETCH_ASSOC);
        if (!$service_data) {
            throw new Exception("The selected laboratory service is unavailable.");
        }
        $hospital_id = (int) $service_data['hospital_id'];
        $service_id = (int) $service_data['id'];

        // 1. Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $phone);
        $stmt->execute();
        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $user_id = $existing_user['id'];
            $stmt = $conn->prepare("SELECT id FROM patients WHERE user_id = :uid LIMIT 1");
            $stmt->bindParam(':uid', $user_id);
            $stmt->execute();
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            if($patient) {
                $patient_id = $patient['id'];
            } else {
                throw new Exception("Account exists but patient record is missing.");
            }
        } else {
            // 2. Create User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status) VALUES (:hospital_id, :username, :password, 'Patient', 'Approved')");
            $stmt->bindParam(':hospital_id', $hospital_id, PDO::PARAM_INT);
            $stmt->bindParam(':username', $phone);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            $user_id = $conn->lastInsertId();

            // 3. Create Patient
            $stmt = $conn->prepare("INSERT INTO patients (user_id, hospital_id, name, age, gender, phone, address) VALUES (:uid, :hospital_id, :name, :age, :gender, :phone, 'Self Registered via Web')");
            $stmt->bindParam(':uid', $user_id);
            $stmt->bindParam(':hospital_id', $hospital_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            $patient_id = $conn->lastInsertId();
        }

        // 4. Create Lab Test Booking
        $stmt = $conn->prepare(
            "INSERT INTO laboratory_tests (hospital_id, patient_id, service_id, status, test_date)
             VALUES (:hospital_id, :patient_id, :service_id, 'Pending', :test_date)"
        );
        $stmt->execute([
            ':hospital_id' => $hospital_id,
            ':patient_id' => $patient_id,
            ':service_id' => $service_id,
            ':test_date' => $test_date,
        ]);

        $conn->commit();
        $success_msg = "Laboratory Test Request Submitted Successfully! You can log into the portal to check when your reports are ready.";
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error_msg = "Error booking lab test: " . $e->getMessage();
    }
    }
}

require_once 'includes/header_public.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade-in-up">
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Book Laboratory Test</h1>
        <p class="text-gray-600 dark:text-gray-400">Request a lab test online. We offer both hospital visits and convenient home sample collection.</p>
    </div>

    <div class="glass p-8 md:p-10 rounded-3xl shadow-2xl border border-white/50 dark:border-white/10 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-indigo-400/20 dark:bg-indigo-600/20 rounded-full filter blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-purple-400/20 dark:bg-purple-600/20 rounded-full filter blur-3xl"></div>

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
            <form action="book_lab.php" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">1. Personal Details</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white" placeholder="John Doe">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone Number (Username)</label>
                            <input type="text" name="phone" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white" placeholder="017XXXXXXXX">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Set a Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white" placeholder="••••••••">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Age</label>
                                <input type="number" name="age" required min="1" max="120" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Gender</label>
                                <select name="gender" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white [&>option]:text-gray-900">
                                    <option value="" disabled selected>Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">2. Test Details</h3>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Select Test Name</label>
                            <select name="test_name" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white [&>option]:text-gray-900">
                                <option value="" disabled <?php echo empty($pre_selected_test) ? 'selected' : ''; ?>>Choose a test...</option>
                                <option value="Complete Blood Count (CBC)" <?php echo $pre_selected_test == 'Complete Blood Count (CBC)' ? 'selected' : ''; ?>>Complete Blood Count (CBC) - ৳ 1,200</option>
                                <option value="MRI Scan" <?php echo $pre_selected_test == 'MRI Scan' ? 'selected' : ''; ?>>MRI Scan - ৳ 8,500</option>
                                <option value="Digital X-Ray" <?php echo $pre_selected_test == 'Digital X-Ray' ? 'selected' : ''; ?>>Digital X-Ray - ৳ 1,500</option>
                                <option value="Lipid Profile" <?php echo $pre_selected_test == 'Lipid Profile' ? 'selected' : ''; ?>>Lipid Profile - ৳ 1,800</option>
                                <option value="ECG / EKG" <?php echo $pre_selected_test == 'ECG / EKG' ? 'selected' : ''; ?>>ECG / EKG - ৳ 1,000</option>
                                <option value="Thyroid Test (TSH)" <?php echo $pre_selected_test == 'Thyroid Test (TSH)' ? 'selected' : ''; ?>>Thyroid Test (TSH) - ৳ 2,200</option>
                                <option value="Other">Other / Not Listed</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Preferred Date</label>
                            <input type="date" name="test_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 text-gray-900 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sample Collection</label>
                            <div class="flex flex-col gap-3 mt-2">
                                <label class="flex items-center p-3 border border-gray-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <input type="radio" name="collection_type" value="Hospital Visit" checked class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Hospital Visit (I will come to the lab)</span>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                                    <input type="radio" name="collection_type" value="Home Collection" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Home Collection (+ ৳ 300 charge)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 text-lg">
                        Confirm Lab Test Request
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
