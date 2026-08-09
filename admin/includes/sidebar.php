<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="w-64 bg-white dark:bg-slate-800 h-[calc(100vh-73px)] shadow-lg hidden md:flex flex-col border-r border-gray-200 dark:border-slate-700 overflow-y-auto sticky top-[73px]">
    <ul class="p-4 space-y-2 flex-1">
        <li><a href="dashboard.php" class="block p-3 <?= $current_page == 'dashboard.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition font-semibold">Dashboard</a></li>
        
        <li><a href="audit_logs.php" class="block p-3 <?= $current_page == 'audit_logs.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition font-semibold">🛡️ Audit Logs</a></li>
        
        <li><a href="hr.php" class="block p-3 <?= $current_page == 'hr.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition font-semibold text-emerald-600 dark:text-emerald-400">👥 HR & Payroll</a></li>
        
        <li><a href="analytics.php" class="block p-3 <?= $current_page == 'analytics.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition font-semibold text-emerald-600 dark:text-emerald-400">📈 Financial Analytics</a></li>
        
        <li><a href="pending_approvals.php" class="block p-3 <?= $current_page == 'pending_approvals.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition relative">Pending Approvals 
            <?php
                if(isset($conn)) {
                    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'Pending'");
                    $pending_count = $stmt->fetchColumn();
                    if($pending_count > 0) echo "<span class='absolute right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full'>$pending_count</span>";
                }
            ?>
        </a></li>
        
        <li><a href="doctors.php" class="block p-3 <?= $current_page == 'doctors.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Manage Doctors</a></li>
        
        <li><a href="patients.php" class="block p-3 <?= $current_page == 'patients.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Manage Patients</a></li>
        
        <li><a href="appointments.php" class="block p-3 <?= $current_page == 'appointments.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Appointments</a></li>
        
        <li><a href="medical_records.php" class="block p-3 <?= $current_page == 'medical_records.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Medical Records</a></li>
        
        <li><a href="lab_tests.php" class="block p-3 <?= $current_page == 'lab_tests.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Lab Tests</a></li>
        
        <li><a href="billing.php" class="block p-3 <?= $current_page == 'billing.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Billing</a></li>
        
        <li class="pt-4 pb-2"><span class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-3">Resources</span></li>
        
        <li><a href="wards.php" class="block p-3 <?= $current_page == 'wards.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Bed / Ward Management</a></li>
        
        <li><a href="blood_bank.php" class="block p-3 <?= $current_page == 'blood_bank.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Blood Bank</a></li>
        
        <li><a href="ambulances.php" class="block p-3 <?= $current_page == 'ambulances.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Ambulance Dispatch</a></li>
        
        <li><a href="feedback.php" class="block p-3 <?= $current_page == 'feedback.php' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-l-4 border-blue-500' : 'text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700' ?> rounded-lg transition">Patient Feedback</a></li>
    </ul>
</aside>
