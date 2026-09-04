<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$current_month = date('F Y');

// Handle Salary Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_salary'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $user_id = $_POST['user_id'];
    $base = $_POST['base_salary'];
    $bonus = $_POST['bonus'];
    $deduction = $_POST['deduction'];
    $net = $base + $bonus - $deduction;
    
    // Check if already paid to prevent double submit
    $check_stmt = $conn->prepare("SELECT id FROM payrolls WHERE user_id = ? AND month_year = ?");
    $check_stmt->execute([$user_id, $current_month]);
    if ($check_stmt->rowCount() == 0) {
        $pay_stmt = $conn->prepare("INSERT INTO payrolls (user_id, month_year, base_salary, bonus, deductions, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, 'Paid')");
        $pay_stmt->execute([$user_id, $current_month, $base, $bonus, $deduction, $net]);
        $success = "Salary paid successfully for $current_month!";
    } else {
        $error = "Salary already paid for this month.";
    }
}

// Fetch all staff for all hospitals (excluding Patients and Admins)
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.role, u.identification_number, u.status, h.name as hospital_name 
    FROM users u 
    LEFT JOIN hospitals h ON u.hospital_id = h.id
    WHERE u.role NOT IN ('Patient', 'Admin')
    ORDER BY h.name, u.role, u.username
");
$stmt->execute();
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch paid user IDs for this month
$paid_stmt = $conn->prepare("SELECT user_id, net_salary FROM payrolls WHERE month_year = ?");
$paid_stmt->execute([$current_month]);
$paid_records = $paid_stmt->fetchAll(PDO::FETCH_ASSOC);

$paid_users = [];
$total_payroll_this_month = 0;
foreach($paid_records as $r) {
    $paid_users[$r['user_id']] = true;
    $total_payroll_this_month += $r['net_salary'];
}

$total_staff = count($staff);
$pending_salaries = $total_staff - count($paid_users);

// Role Distribution for Chart
$role_counts = [];
foreach($staff as $s) {
    $r = $s['role'];
    if(!isset($role_counts[$r])) $role_counts[$r] = 0;
    $role_counts[$r]++;
}

// Fetch recent payrolls
$pay_stmt = $conn->prepare("
    SELECT p.*, u.username, u.role, h.name as hospital_name 
    FROM payrolls p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN hospitals h ON u.hospital_id = h.id
    ORDER BY p.id DESC LIMIT 20
");
$pay_stmt->execute();
$payrolls = $pay_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR & Payroll - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
</head>
<body class="bg-slate-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col min-h-screen transition-colors duration-300">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto h-[calc(100vh-73px)]">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-8">HR & Payroll Dashboard</h2>
            
            <?php if(isset($success)): ?>
                <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 rounded-md mb-8 shadow-sm">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 rounded-md mb-8 shadow-sm">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 flex items-center space-x-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-blue-100 dark:bg-blue-900/40 rounded-xl text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Staff</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white"><?= number_format($total_staff) ?></h3>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 flex items-center space-x-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl text-emerald-600 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Payroll This Month</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white">৳<?= number_format($total_payroll_this_month) ?></h3>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 flex items-center space-x-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-amber-100 dark:bg-amber-900/40 rounded-xl text-amber-600 dark:text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Salaries</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white"><?= number_format($pending_salaries) ?></h3>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Staff Distribution Pie Chart -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Staff Distribution</h2>
                    <div class="relative h-64 w-full">
                        <canvas id="staffChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Payments List (Takes up 2 columns) -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-6 lg:col-span-2 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Recent Payments</h2>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last 20 transactions</span>
                    </div>
                    <div class="overflow-y-auto flex-1 rounded-xl border border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/30">
                        <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                            <?php if(empty($payrolls)): ?>
                                <li class="p-8 text-center text-gray-500 dark:text-gray-400 font-medium">No payments processed yet.</li>
                            <?php endif; ?>
                            <?php foreach($payrolls as $p): ?>
                            <li class="p-4 hover:bg-white dark:hover:bg-slate-800 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400 font-bold">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                <?= htmlspecialchars($p['username']) ?> <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(<?= $p['role'] ?>)</span>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                <?= htmlspecialchars($p['hospital_name'] ?? 'Global') ?> &bull; <?= $p['month_year'] ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                            +৳<?= number_format($p['net_salary'], 2) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Paid</div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Global Staff Directory -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden flex flex-col mb-8">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/30 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Global Staff Directory</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and pay salaries for staff across all branches.</p>
                    </div>
                    <!-- Future: Filter dropdown can go here -->
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-white dark:bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff Details</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hospital Branch</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role & ID</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status (<?= $current_month ?>)</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700/50 bg-white dark:bg-slate-800">
                            <?php if(empty($staff)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No staff members found in the system.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach($staff as $s): ?>
                            <?php $is_paid = isset($paid_users[$s['id']]); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center font-bold text-lg">
                                            <?= strtoupper(substr($s['username'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($s['username']) ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($s['status']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200 font-medium"><?= htmlspecialchars($s['hospital_name'] ?? 'Global / Unassigned') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-md bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800"><?= htmlspecialchars($s['role']) ?></span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-mono">ID: <?= htmlspecialchars($s['identification_number'] ?? 'N/A') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if($is_paid): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Unpaid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if(!$is_paid): ?>
                                        <button onclick="openPayModal(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['username'])) ?>', '<?= htmlspecialchars(addslashes($s['role'])) ?>')" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            Pay Salary
                                        </button>
                                    <?php else: ?>
                                        <button disabled class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-gray-400 bg-gray-100 dark:bg-slate-800 dark:text-gray-600 cursor-not-allowed">
                                            Processed
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Pay Salary Modal -->
    <div id="payModal" class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity">
        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-md transform transition-all border border-gray-100 dark:border-slate-700">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Process Payment</h3>
                <button onclick="closePayModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm">Processing salary for <span id="modalStaffName" class="font-bold text-gray-900 dark:text-white"></span> for <span class="font-semibold text-indigo-600 dark:text-indigo-400"><?= $current_month ?></span>.</p>
            
            <form method="POST" id="payrollForm">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" id="modalUserId">
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Base Salary (৳)</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">৳</span>
                            </div>
                            <input type="number" name="base_salary" id="baseSalaryInput" class="pl-7 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-slate-900 dark:border-slate-600 dark:text-white sm:text-sm py-2.5 transition-colors" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Bonus (৳)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-emerald-500 sm:text-sm">+</span>
                                </div>
                                <input type="number" name="bonus" id="bonusInput" value="0" class="pl-7 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:bg-slate-900 dark:border-slate-600 dark:text-white sm:text-sm py-2 transition-colors text-emerald-600 dark:text-emerald-400 font-semibold" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Deductions (৳)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-red-500 sm:text-sm">-</span>
                                </div>
                                <input type="number" name="deduction" id="deductionInput" value="0" class="pl-7 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-slate-900 dark:border-slate-600 dark:text-white sm:text-sm py-2 transition-colors text-red-600 dark:text-red-400 font-semibold" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-slate-700 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900 dark:text-white">Net Payable:</span>
                            <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400" id="netPayableText">৳0</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" onclick="closePayModal()" class="px-5 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" name="pay_salary" class="px-5 py-2.5 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors flex items-center">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openPayModal(userId, name, role) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('modalStaffName').innerText = name + ' (' + role + ')';
        
        let base = 25000;
        if(role === 'Doctor') base = 80000;
        if(role === 'Pharmacist') base = 35000;
        if(role === 'Laboratory Staff') base = 30000;
        
        document.getElementById('baseSalaryInput').value = base;
        document.getElementById('bonusInput').value = 0;
        document.getElementById('deductionInput').value = 0;
        
        updateNetPayable();
        
        document.getElementById('payModal').classList.remove('hidden');
        document.getElementById('payModal').classList.add('flex');
    }

    function closePayModal() {
        document.getElementById('payModal').classList.add('hidden');
        document.getElementById('payModal').classList.remove('flex');
    }
    
    function updateNetPayable() {
        const base = parseFloat(document.getElementById('baseSalaryInput').value) || 0;
        const bonus = parseFloat(document.getElementById('bonusInput').value) || 0;
        const deduction = parseFloat(document.getElementById('deductionInput').value) || 0;
        const net = base + bonus - deduction;
        document.getElementById('netPayableText').innerText = '৳' + net.toLocaleString();
    }
    
    document.getElementById('baseSalaryInput').addEventListener('input', updateNetPayable);
    document.getElementById('bonusInput').addEventListener('input', updateNetPayable);
    document.getElementById('deductionInput').addEventListener('input', updateNetPayable);

    // Initialize Chart.js Pie Chart
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('staffChart');
        if(ctx) {
            const roleData = <?= json_encode($role_counts) ?>;
            const labels = Object.keys(roleData);
            const data = Object.values(roleData);
            
            // Generate some colors
            const bgColors = [
                '#3b82f6', // blue-500
                '#10b981', // emerald-500
                '#f59e0b', // amber-500
                '#6366f1', // indigo-500
                '#ec4899', // pink-500
            ];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#475569',
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
    </script>

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
