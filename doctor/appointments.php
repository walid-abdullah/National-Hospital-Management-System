<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$per_page = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$like = '%' . $search . '%';

// Get doctor id
$stmt = $conn->prepare("SELECT id as doctor_id FROM doctors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doctor) {
    $doctor_id = $doctor['doctor_id'];
    $count_query = "SELECT COUNT(*) FROM appointments a JOIN patients p ON a.patient_id = p.id
                    WHERE a.doctor_id = :doctor_id
                    AND (p.name LIKE :name_search OR a.appointment_type LIKE :type_search OR a.status LIKE :status_search)";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute([
        ':doctor_id' => $doctor_id,
        ':name_search' => $like,
        ':type_search' => $like,
        ':status_search' => $like,
    ]);
    $total_items = (int) $count_stmt->fetchColumn();
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;

    // Get appointments
    $query = "SELECT a.id as appointment_id, p.name AS patient_name, p.age, p.gender, a.appointment_date, a.appointment_type, a.status
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              WHERE a.doctor_id = :doctor_id
              AND (p.name LIKE :name_search OR a.appointment_type LIKE :type_search OR a.status LIKE :status_search)
              ORDER BY a.appointment_date ASC LIMIT :limit OFFSET :offset";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindValue(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt2->bindValue(':name_search', $like, PDO::PARAM_STR);
    $stmt2->bindValue(':type_search', $like, PDO::PARAM_STR);
    $stmt2->bindValue(':status_search', $like, PDO::PARAM_STR);
    $stmt2->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt2->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt2->execute();
    $appointments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $appointments = [];
    $total_items = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Doctor Portal</title>
    <script src="https://cdn.tailwindcss.com">    </script>

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
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>


<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-200 selection:text-blue-900 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm text-gray-800 dark:text-gray-100 flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Doctor Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-blue-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <a href="appointments.php" class="text-white font-medium">Appointments</a>
            <span class="border-l border-blue-400 h-6 mx-2"></span>
            <span>Dr. <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto animate-fade-in-up mt-10 w-full p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">My Appointments</h2>
        </div>
        <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
            <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search by patient, type, or status..." class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold">Search</button>
            <?php if ($search !== ''): ?><a href="appointments.php" class="px-5 py-2.5 rounded-xl bg-gray-200 text-gray-700 font-semibold text-center">Clear</a><?php endif; ?>
        </form>

        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">Apt ID</th>
                        <th class="p-4 border-b">Patient Name</th>
                        <th class="p-4 border-b">Age/Gender</th>
                        <th class="p-4 border-b">Type</th>
                        <th class="p-4 border-b">Date</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    <?php if(!empty($appointments)): ?>
                        <?php foreach($appointments as $apt): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200">#<?php echo $apt['appointment_id']; ?></td>
                            <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo e($apt['age']); ?> Yrs / <?php echo e($apt['gender']); ?></td>
                            <td class="p-4 text-sm">
                                <?php if($apt['appointment_type'] === 'Telemedicine'): ?>
                                    <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-semibold">🌐 Telemedicine</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">🏢 In-Person</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm text-gray-800 dark:text-gray-100 font-medium"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                            <td class="p-4 text-sm">
                                <?php if($apt['status'] == 'Pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Pending</span>
                                <?php elseif($apt['status'] == 'Completed'): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">Completed</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold"><?php echo htmlspecialchars($apt['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm">
                                <?php if($apt['status'] == 'Confirmed' && $apt['appointment_type'] === 'Telemedicine'): ?>
                                    <button type="button" data-appointment-id="<?php echo (int) $apt['appointment_id']; ?>" class="join-tele-btn bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white px-4 py-2 rounded text-xs font-bold shadow-lg transform hover:scale-105 transition-all inline-block">📹 Start Call</button>
                                <?php elseif($apt['status'] == 'Confirmed'): ?>
                                    <a href="add_prescription.php?apt_id=<?php echo $apt['appointment_id']; ?>" class="bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white px-4 py-2 rounded text-xs font-bold shadow-lg transform hover:scale-105 transition-all inline-block">Write Prescription</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500 dark:text-gray-400">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-6">
            <span class="text-sm text-gray-500 dark:text-gray-400">Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_items; ?> appointments)</span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">Prev</a><?php endif; ?>
                <?php if ($page < $total_pages): ?><a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Next</a><?php endif; ?>
            </div>
        </div>
    </div>

    <div id="video-call-modal" class="hidden fixed inset-0 z-50 bg-slate-950/80 p-4 sm:p-8" role="dialog" aria-modal="true" aria-labelledby="video-call-title">
        <div class="mx-auto flex w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-slate-700">
                <h2 id="video-call-title" class="text-lg font-bold text-gray-900 dark:text-white">NHIMS Telemedicine Consultation</h2>
                <button type="button" id="close-video-call" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600" aria-label="End call and close">End Call / Close</button>
            </div>
            <iframe id="video-call-frame" title="Jitsi video consultation" width="100%" height="550px" class="w-full border-0" allow="camera; microphone; fullscreen; display-capture; autoplay"></iframe>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let bgClass = type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-blue-500 to-indigo-600';
            
            toast.className = `transform translate-x-full opacity-0 transition-all duration-500 ease-out flex items-center p-4 rounded-xl shadow-2xl text-white ${bgClass}`;
            toast.innerHTML = `
                <svg class="w-6 h-6 mr-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <div class="font-semibold text-sm">${message}</div>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            }, 50);
            
            // Animate out
            setTimeout(() => {
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        // Attach event listeners to telemedicine buttons
        document.addEventListener('DOMContentLoaded', () => {
            const teleBtns = document.querySelectorAll('.join-tele-btn');
            teleBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    return;
                });
            });
        });
    </script>

    <script>
        const videoCallModal = document.getElementById('video-call-modal');
        const videoCallFrame = document.getElementById('video-call-frame');
        function showToast() {}
        function closeVideoCall() {
            videoCallFrame.src = '';
            videoCallModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        document.querySelectorAll('.join-tele-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                const room = `NHIMS_Consultation_Room_APT_${encodeURIComponent(btn.dataset.appointmentId)}`;
                const displayName = <?php echo json_encode($_SESSION['username'] ?? 'Doctor'); ?>;
                videoCallFrame.src = `https://meet.jit.si/${room}#userInfo.displayName=${encodeURIComponent(JSON.stringify(displayName))}`;
                videoCallModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            });
        });
        document.getElementById('close-video-call').addEventListener('click', closeVideoCall);
        videoCallModal.addEventListener('click', (event) => {
            if (event.target === videoCallModal) closeVideoCall();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !videoCallModal.classList.contains('hidden')) closeVideoCall();
        });
    </script>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>
</body>
</html>
