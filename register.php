<?php
require_once 'includes/security.php';
init_secure_session();
require_once 'config/db.php';

// Fetch hospitals for the dropdown
$stmt = $conn->query("SELECT * FROM hospitals");
$hospitals = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Invalid security token. Please try again.";
        header("Location: register.php");
        exit();
    }
    $hospital_id = $_POST['hospital_id'] ?? null;
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $allowed_roles = ['Patient', 'Doctor', 'Receptionist', 'Laboratory Staff', 'Pharmacist'];
    if (!in_array($role, $allowed_roles, true)) {
        $_SESSION['error'] = "Invalid registration role.";
        header("Location: register.php");
        exit();
    }
    
    // Fetch identification number
    $identification_number = $_POST['identification_number'] ?? null;

    // Handle document uploads
    $document_path = null;
    if ($role !== 'Patient') {
        $docs = [];
        $upload_dir = 'uploads/documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $fields = ['nid_doc' => 'nid', 'certificate_doc' => 'certificate', 'photo_doc' => 'photo'];
        foreach ($fields as $input_name => $json_key) {
            if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                    $_SESSION['error'] = "Invalid document type. Only PDF, JPG, and PNG files are allowed.";
                    header("Location: register.php");
                    exit();
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $extension;
                $target_file = $upload_dir . $filename;
                if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_file)) {
                    $docs[$json_key] = $target_file;
                }
            }
        }
        if (!empty($docs)) {
            $document_path = json_encode($docs);
        }
    }

    // Patient is approved instantly, others are Pending
    $status = ($role === 'Patient') ? 'Approved' : 'Pending';

    try {
        $stmt = $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number, document_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hospital_id, $username, $password, $role, $status, $identification_number, $document_path]);
        $user_id = $conn->lastInsertId();

        // If patient, also add to patients table
        if ($role === 'Patient') {
            $name = $_POST['name'] ?? $username;
            $phone = $_POST['phone'] ?? $username;
            $stmt_p = $conn->prepare("INSERT INTO patients (user_id, hospital_id, name, phone, age, gender, address) VALUES (?, ?, ?, ?, 0, 'Unknown', 'N/A')");
            $stmt_p->execute([$user_id, $hospital_id, $name, $phone]);
            
            $_SESSION['success'] = "Registration successful! You can now log in.";
        } else {
            $_SESSION['success'] = "Registration successful! Please wait for Admin approval.";
        }
        
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed. Username might be taken.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHIMS - Universal Registration</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
          darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); }
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 dark:bg-gray-900 transition-colors duration-300 antialiased selection:bg-blue-200 selection:text-blue-900 relative">

    <div class="absolute top-4 right-4 z-50">
        <button onclick="toggleDarkMode()" class="p-2 rounded-full glass hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 text-gray-600 dark:text-gray-300 shadow-md" title="Toggle Dark Mode">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
        </button>
    </div>

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="glass p-8 rounded-3xl shadow-2xl w-full max-w-md text-gray-800 dark:text-gray-100 animate-fade-in-up border border-white/50 dark:border-white/10 relative overflow-hidden my-8">
            <!-- Decorative gradient orb -->
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-400 dark:bg-blue-600 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-30 animate-pulse"></div>
            
            <div class="text-center mb-8 relative z-10">
                <h1 class="text-3xl font-extrabold mb-2 custom-gradient-text tracking-tight">Join NHIMS Network</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Select your branch and role to register</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 rounded mb-6 text-sm flex items-center relative z-10 shadow-sm">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <?php echo e($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" enctype="multipart/form-data" class="space-y-5 relative z-10">
            <?php echo csrf_field(); ?>
                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">I am a...</label>
                    <select name="role" id="roleSelect" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100 appearance-none">
                        <option value="Patient" selected>Patient</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Laboratory Staff">Laboratory Staff</option>
                        <option value="Pharmacist">Pharmacist</option>
                        <option value="Receptionist">Receptionist</option>
                    </select>
                </div>

                <!-- Hospital Branch -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Select Branch</label>
                    <select name="hospital_id" required class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100 appearance-none">
                        <option value="" disabled selected>-- Choose Hospital --</option>
                        <?php foreach($hospitals as $h): ?>
                            <option value="<?= (int) $h['id'] ?>"><?= e($h['name']) ?> (<?= e($h['location']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Basic Info -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Full Name</label>
                    <input type="text" name="name" required placeholder="Enter your full name"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Phone Number (Username)</label>
                    <input type="text" name="username" required placeholder="e.g. 01700000000"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100">
                </div>

                <!-- Dynamic Identification Number -->
                <div id="idNumberDiv">
                    <label id="idLabel" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">NID / Passport Number</label>
                    <input type="text" name="identification_number" id="idInput" placeholder="Enter NID/Passport" required 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100">
                    <p id="idHelp" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1 hidden font-medium">* Required for Admin Verification</p>
                </div>

                <!-- Document Uploads for Staff/Doctors -->
                <div id="documentUploadDiv" class="hidden space-y-4 bg-blue-50/50 dark:bg-blue-900/10 p-4 rounded-xl border border-blue-100 dark:border-blue-900/30">
                    <p class="text-sm font-bold text-blue-800 dark:text-blue-300 border-b border-blue-200 dark:border-blue-800/50 pb-2 mb-3">Verification Documents</p>
                    
                    <div>
                        <label id="certLabel" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">BMDC Certificate / Staff ID (Required)</label>
                        <input type="file" name="certificate_doc" id="certInput" accept=".pdf,image/*" 
                            class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">NID / Passport Copy (Required)</label>
                        <input type="file" name="nid_doc" id="nidInput" accept=".pdf,image/*" 
                            class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Profile Picture (Optional)</label>
                        <input type="file" name="photo_doc" accept="image/*" 
                            class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 mt-6">
                    Register Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="login.php" class="text-sm text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                    Already have an account? Login here
                </a>
            </div>
        </div>
    </div>

    <footer class="py-6 text-center text-gray-500 dark:text-gray-500 text-sm w-full border-t border-gray-200 dark:border-slate-800/50 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md">
        &copy; <?php echo date('Y'); ?> National Hospital Information Management System. All rights reserved.
    </footer>

    <script>
        document.getElementById('roleSelect').addEventListener('change', function() {
            const role = this.value;
            const idLabel = document.getElementById('idLabel');
            const idInput = document.getElementById('idInput');
            const idHelp = document.getElementById('idHelp');
            const docDiv = document.getElementById('documentUploadDiv');
            const certInput = document.getElementById('certInput');
            const certLabel = document.getElementById('certLabel');
            const nidInput = document.getElementById('nidInput');
            
            if (role === 'Patient') {
                idLabel.innerText = "NID / Passport Number";
                idInput.placeholder = "Enter NID/Passport";
                idHelp.classList.add('hidden');
                docDiv.classList.add('hidden');
                certInput.required = false;
                nidInput.required = false;
            } else if (role === 'Doctor') {
                idLabel.innerText = "BMDC Medical License Number";
                idInput.placeholder = "e.g. A-12345";
                certLabel.innerText = "BMDC Certificate (Required)";
                idHelp.classList.remove('hidden');
                docDiv.classList.remove('hidden');
                certInput.required = true;
                nidInput.required = true;
            } else {
                idLabel.innerText = "Official Staff ID Number";
                idInput.placeholder = "e.g. STF-9876";
                certLabel.innerText = "Official Staff ID Card (Required)";
                idHelp.classList.remove('hidden');
                docDiv.classList.remove('hidden');
                certInput.required = true;
                nidInput.required = true;
            }
        });
    </script>
</body>
</html>
