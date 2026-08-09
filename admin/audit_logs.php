<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch logs
$stmt = $conn->prepare("SELECT l.*, u.username, u.role FROM system_logs l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total
$total_logs = $conn->query("SELECT COUNT(*) FROM system_logs")->fetchColumn();
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Audit Logs - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <main class="flex-1 p-8 w-full max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center">
                <svg class="w-8 h-8 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                System Audit Logs
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">Tracking every system activity</div>
        </div>

        <div class="glass rounded-2xl shadow-lg overflow-hidden border border-white/50 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100/50 dark:bg-slate-800/50 border-b border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 text-sm tracking-wider uppercase">
                            <th class="p-4 font-semibold">Time</th>
                            <th class="p-4 font-semibold">User</th>
                            <th class="p-4 font-semibold">Role</th>
                            <th class="p-4 font-semibold">Action</th>
                            <th class="p-4 font-semibold">Details</th>
                            <th class="p-4 font-semibold">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 dark:text-gray-300 divide-y divide-gray-100 dark:divide-slate-700/50">
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-gray-500">No logs found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-blue-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                        <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                    </td>
                                    <td class="p-4 font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($log['username']); ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold">
                                            <?php echo htmlspecialchars($log['role']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 font-semibold text-emerald-600 dark:text-emerald-400">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </td>
                                    <td class="p-4 max-w-xs truncate" title="<?php echo htmlspecialchars($log['details']); ?>">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </td>
                                    <td class="p-4 text-xs font-mono text-gray-400">
                                        <?php echo htmlspecialchars($log['ip_address']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="p-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50 flex justify-between items-center">
                <span class="text-sm text-gray-500">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <div class="flex space-x-2">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>" class="px-3 py-1 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded text-sm hover:bg-gray-100 dark:hover:bg-slate-600">Previous</a>
                    <?php endif; ?>
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?>" class="px-3 py-1 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded text-sm hover:bg-gray-100 dark:hover:bg-slate-600">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

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
