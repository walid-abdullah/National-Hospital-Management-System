<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$hospitals = $conn->query("SELECT id, name FROM hospitals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$selected_hospital = $_GET['hospital_id'] ?? 'all';

// Setup params
$params = [];
$hospital_filter = "";
$hospital_filter_where = "";
if ($selected_hospital !== 'all') {
    $hospital_filter = " AND hospital_id = :h_id ";
    $hospital_filter_where = " WHERE hospital_id = :h_id ";
    $params[':h_id'] = $selected_hospital;
}

// Total Revenue (Consultation + Pharmacy Sales)
$q_bill = "SELECT SUM(total_amount) as total FROM billing WHERE status = 'Paid' $hospital_filter";
$stmt = $conn->prepare($q_bill);
$stmt->execute($params);
$total_billing = $stmt->fetchColumn() ?: 0;

$q_pharm = "SELECT SUM(total_amount) as total FROM pharmacy_sales WHERE 1=1 $hospital_filter";
$stmt = $conn->prepare($q_pharm);
$stmt->execute($params);
$total_pharmacy = $stmt->fetchColumn() ?: 0;

$total_revenue = $total_billing + $total_pharmacy;

// Revenue by Month (Last 6 Months)
// We'll approximate this by generating 6 months data in PHP if the DB lacks historical data.
// But we actually seeded billing with past 300 days.
$q_monthly = "
    SELECT DATE_FORMAT(bill_date, '%b %Y') as month, SUM(total_amount) as revenue 
    FROM billing 
    WHERE status = 'Paid' $hospital_filter 
    GROUP BY DATE_FORMAT(bill_date, '%b %Y'), YEAR(bill_date), MONTH(bill_date)
    ORDER BY YEAR(bill_date) DESC, MONTH(bill_date) DESC 
    LIMIT 6
";
$stmt = $conn->prepare($q_monthly);
$stmt->execute($params);
$monthly_data = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

$months = [];
$revenues = [];
foreach($monthly_data as $row) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
}

// Count breakdown
$q_appts = "SELECT COUNT(*) FROM appointments $hospital_filter_where";
$stmt = $conn->prepare($q_appts);
$stmt->execute($params);
$total_appts = $stmt->fetchColumn();

$q_patients = "SELECT COUNT(*) FROM patients $hospital_filter_where";
$stmt = $conn->prepare($q_patients);
$stmt->execute($params);
$total_patients = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Analytics - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">📈 Financial Analytics</h2>
            
            <form method="GET" class="flex items-center gap-3">
                <label class="font-medium text-gray-600 dark:text-gray-300">Filter Branch:</label>
                <select name="hospital_id" onchange="this.form.submit()" class="p-2 border rounded-lg dark:bg-gray-800 dark:border-gray-600 outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                    <option value="all">All Branches</option>
                    <?php foreach($hospitals as $h): ?>
                        <option value="<?php echo $h['id']; ?>" <?php echo $h['id']==$selected_hospital ? 'selected':''; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-emerald-500">
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Total Revenue</div>
                <div class="text-3xl font-black text-emerald-600">৳<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            
            <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-blue-500">
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Consultation Revenue</div>
                <div class="text-3xl font-black text-blue-600">৳<?php echo number_format($total_billing, 2); ?></div>
            </div>

            <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-purple-500">
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Total Appointments</div>
                <div class="text-3xl font-black text-purple-600"><?php echo number_format($total_appts); ?></div>
            </div>

            <div class="glass p-6 rounded-2xl shadow-lg border-t-4 border-pink-500">
                <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Total Patients</div>
                <div class="text-3xl font-black text-pink-600"><?php echo number_format($total_patients); ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Trend Line Chart -->
            <div class="glass p-6 rounded-2xl shadow-lg">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Revenue Trend (Last 6 Months)</h3>
                <div class="relative w-full h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <!-- Revenue Distribution Doughnut Chart -->
            <div class="glass p-6 rounded-2xl shadow-lg flex flex-col justify-center items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 w-full text-left">Revenue Distribution</h3>
                <div class="relative w-full h-64 flex justify-center">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart Configs
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#e2e8f0' : '#475569';
        const gridColor = isDark ? '#334155' : '#e2e8f0';

        Chart.defaults.color = textColor;
        Chart.defaults.font.family = 'Outfit';

        // 1. Revenue Line Chart
        const ctxRev = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Consultation Revenue (৳)',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: gridColor, drawBorder: false } },
                    y: { grid: { color: gridColor, drawBorder: false }, beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 2. Revenue Distribution
        const ctxDist = document.getElementById('distributionChart').getContext('2d');
        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: ['Consultations/Services', 'Pharmacy Sales'],
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
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
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
