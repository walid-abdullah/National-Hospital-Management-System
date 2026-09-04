<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Fetch Financial Summary
$total_billing = $conn->query("SELECT SUM(total_amount) FROM billing WHERE status = 'Paid'")->fetchColumn() ?: 0;
$total_pharmacy = $conn->query("SELECT SUM(total_amount) FROM pharmacy_sales")->fetchColumn() ?: 0;
$total_revenue = $total_billing + $total_pharmacy;

// Fetch Pending Approvals List
$pending_users = $conn->query("SELECT id, username, role, created_at, hospital_id FROM users WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Activities
$recent_logs = $conn->query("SELECT l.*, u.username, u.role FROM system_logs l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
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
    <script>
        // Check local storage for dark mode preference
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


<body class="bg-slate-50 antialiased selection:bg-blue-200 selection:text-blue-900 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Content -->
        <main class="flex-1 p-8 animate-fade-in-up">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Approvals</h3>
                    <p class="text-3xl font-bold text-red-500 mt-2"><?php echo $pending_count; ?></p>
                </div>
                <!-- Card 2 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Doctors</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">
                        <?php echo $conn->query("SELECT COUNT(*) FROM users WHERE role='Doctor' AND status='Approved'")->fetchColumn(); ?>
                    </p>
                </div>
                <!-- Card 3 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Patients</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">
                        <?php echo $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn(); ?>
                    </p>
                </div>
                <!-- Card 4 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Today's Appointments</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">
                        <?php echo $conn->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn(); ?>
                    </p>
                </div>
            </div>
            
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-emerald-500">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Revenue Distribution
                        </div>
                        <span class="text-sm font-bold text-gray-500">Total: ৳<?php echo number_format($total_revenue, 2); ?></span>
                    </h3>
                    <div class="relative w-full flex justify-center mt-6 h-48">
                        <canvas id="dashboardRevenueChart"></canvas>
                    </div>
                </div>

                <!-- Pending Approvals Widget -->
                <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-orange-500">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Pending Approvals
                        </h3>
                        <a href="pending_approvals.php" class="text-sm text-orange-500 hover:underline font-semibold">Review All</a>
                    </div>
                    <div class="space-y-3">
                        <?php if(empty($pending_users)): ?>
                            <p class="text-emerald-500 font-medium text-sm">✅ All users are approved.</p>
                        <?php else: ?>
                            <?php foreach($pending_users as $pu): ?>
                                <div class="bg-orange-50 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800 p-3 rounded-lg flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-800 text-orange-700 dark:text-orange-200 flex justify-center items-center font-bold mr-3 text-xs">
                                            <?php echo strtoupper(substr($pu['role'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-orange-800 dark:text-orange-100 text-sm"><?php echo htmlspecialchars($pu['username']); ?></p>
                                            <p class="text-xs text-orange-600 dark:text-orange-300"><?php echo htmlspecialchars($pu['role']); ?> • <?php echo date('M d', strtotime($pu['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <a href="pending_approvals.php" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">Review</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activities Widget -->
                <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-blue-500">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Recent System Activities
                        </h3>
                        <a href="audit_logs.php" class="text-sm text-blue-500 hover:underline">View All</a>
                    </div>
                    <div class="space-y-4">
                        <?php foreach($recent_logs as $log): ?>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xs">
                                    <?php echo strtoupper(substr($log['role'], 0, 1)); ?>
                                </div>
                                <div class="ml-3 w-full">
                                    <div class="flex justify-between items-baseline">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($log['action']); ?></p>
                                        <span class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($log['created_at'])); ?></span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5"><?php echo htmlspecialchars($log['details']); ?></p>
                                    <p class="text-[10px] text-gray-400 mt-1">By: <?php echo htmlspecialchars($log['username']); ?> (<?php echo htmlspecialchars($log['role']); ?>)</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($recent_logs)): ?>
                            <p class="text-sm text-gray-500">No recent activities found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#e2e8f0' : '#475569';
            Chart.defaults.color = textColor;
            Chart.defaults.font.family = 'Outfit';

            const ctx = document.getElementById('dashboardRevenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Consultations', 'Pharmacy'],
                    datasets: [{
                        data: [<?php echo $total_billing; ?>, <?php echo $total_pharmacy; ?>],
                        backgroundColor: ['#3b82f6', '#10b981'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'right' }
                    }
                }
            });
        });
    </script>
</body>
</html>
