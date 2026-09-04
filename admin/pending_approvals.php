<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Fetch pending users
$stmt = $conn->prepare("SELECT u.id, u.username, u.role, u.created_at, u.identification_number, u.document_path, h.name as hospital_name 
                        FROM users u 
                        LEFT JOIN hospitals h ON u.hospital_id = h.id 
                        WHERE u.status = 'Pending' 
                        ORDER BY u.created_at DESC");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action']; // 'Approve' or 'Reject'
        
        $new_status = ($action === 'Approve') ? 'Approved' : 'Rejected';
        
        $upd = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upd->execute([$new_status, $user_id]);
        
        // If it's a doctor and approved, we should also create a basic entry in the doctors table
        if ($new_status === 'Approved') {
            $check = $conn->prepare("SELECT role, hospital_id FROM users WHERE id = ?");
            $check->execute([$user_id]);
            $u_info = $check->fetch();
            
            if ($u_info['role'] === 'Doctor') {
                $doc_ins = $conn->prepare("INSERT INTO doctors (user_id, hospital_id, name, specialization, phone) VALUES (?, ?, ?, 'General', 'Pending')");
                $doc_ins->execute([$user_id, $u_info['hospital_id'], 'Dr. ' . $_POST['username']]);
            }
        }
        
        $_SESSION['msg'] = "User has been " . $new_status;
        header("Location: pending_approvals.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - Admin</title>
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
    </style>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-300">

    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1 p-8">
        <main class="w-full max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Pending Registrations</h2>
                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-bold"><?= count($pending_users) ?> Pending</span>
            </div>
            
            <?php if(isset($_SESSION['msg'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
                </div>
            <?php endif; ?>

            <div class="glass rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-slate-800 border-b dark:border-slate-700">
                            <th class="p-4 font-semibold">Date</th>
                            <th class="p-4 font-semibold">Username</th>
                            <th class="p-4 font-semibold">Role</th>
                            <th class="p-4 font-semibold">ID / License No.</th>
                            <th class="p-4 font-semibold">Branch</th>
                            <th class="p-4 font-semibold">Documents</th>
                            <th class="p-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-slate-700">
                        <?php if (count($pending_users) > 0): ?>
                            <?php foreach ($pending_users as $user): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition">
                                    <td class="p-4"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    <td class="p-4 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="p-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold"><?= $user['role'] ?></span></td>
                                    <td class="p-4 font-semibold text-gray-700"><?= htmlspecialchars($user['identification_number'] ?? 'N/A') ?></td>
                                    <td class="p-4"><?= htmlspecialchars($user['hospital_name']) ?></td>
                                    <td class="p-4">
                                        <?php 
                                            if ($user['document_path']) {
                                                $docs = json_decode($user['document_path'], true);
                                                if (is_array($docs)) {
                                                    echo '<div class="flex flex-col space-y-1">';
                                                    foreach($docs as $key => $path) {
                                                        $label = ucfirst($key);
                                                        if($key === 'nid') $label = 'NID/Passport';
                                                        if($key === 'certificate') $label = 'Certificate/ID';
                                                        echo "<a href='../" . htmlspecialchars($path) . "' target='_blank' class='text-blue-600 hover:text-blue-800 underline text-sm font-medium'>View $label</a>";
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    // Fallback for old single string paths
                                                    echo "<a href='../" . htmlspecialchars($user['document_path']) . "' target='_blank' class='text-blue-600 hover:text-blue-800 underline text-sm font-medium'>View Document</a>";
                                                }
                                            } else {
                                                echo '<span class="text-gray-400 text-sm italic">No Document</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <form method="POST" class="inline-block">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                            <button type="submit" name="action" value="Approve" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded shadow text-sm font-medium transition mr-2">Approve</button>
                                            <button type="submit" name="action" value="Reject" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow text-sm font-medium transition">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">No pending registrations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const table = document.querySelector("table");
            if (table) {
                new simpleDatatables.DataTable(table, {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 15
                });
            }
        });
    </script>
</body>
</html>
