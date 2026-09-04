<?php
require_once '../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$bill_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("
    SELECT b.*, p.name as patient_name, p.phone, p.address, h.name as hospital_name, h.location as hospital_address, h.contact_number as hospital_contact 
    FROM billing b
    JOIN patients p ON b.patient_id = p.id
    JOIN hospitals h ON b.hospital_id = h.id
    WHERE b.id = :id
      AND ( :role = 'Admin' OR p.user_id = :user_id )
");
$stmt->execute([':id' => $bill_id, ':role' => $_SESSION['role'] ?? '', ':user_id' => $_SESSION['user_id']]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    die("Invoice not found.");
}

$details = json_decode($bill['details'], true);
if(!$details) {
    $details = ['General Consultation' => $bill['total_amount']];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo e($bill['invoice_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f1f5f9; color: #1e293b; }
        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; }
            .invoice-box { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="flex justify-between items-start mb-8 border-b pb-6">
            <div>
                <h1 class="text-3xl font-black text-blue-600 tracking-tight">NHIMS</h1>
                <div class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($bill['hospital_name']); ?></div>
                <div class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($bill['hospital_address']); ?></div>
                <div class="text-sm text-gray-500">Phone: <?php echo htmlspecialchars($bill['hospital_contact']); ?></div>
            </div>
            <div class="text-right">
                <h2 class="text-4xl font-black text-gray-200 mb-2 uppercase">Invoice</h2>
                <div class="font-bold text-gray-800 text-lg">#<?php echo htmlspecialchars($bill['invoice_number']); ?></div>
                <div class="text-sm text-gray-500">Date: <?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></div>
                <div class="mt-2 text-sm">
                    Status: 
                    <?php if($bill['status'] == 'Paid'): ?>
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded font-bold">PAID</span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded font-bold">UNPAID</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mb-10 flex justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To</h3>
                <div class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($bill['patient_name']); ?></div>
                <div class="text-sm text-gray-600 mt-1">Phone: <?php echo htmlspecialchars($bill['phone']); ?></div>
                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($bill['address']); ?></div>
            </div>
            <div class="text-right">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Method</h3>
                <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($bill['payment_method'] ?? 'N/A'); ?></div>
            </div>
        </div>

        <table class="w-full text-left border-collapse mb-8">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                    <th class="p-3 border-y border-gray-200 font-semibold">Description</th>
                    <th class="p-3 border-y border-gray-200 font-semibold text-right w-32">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($details as $desc => $amt): ?>
                <tr>
                    <td class="p-4 border-b border-gray-100 text-gray-800 font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $desc))); ?></td>
                    <td class="p-4 border-b border-gray-100 text-gray-800 font-mono text-right font-bold">৳<?php echo number_format($amt, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="flex justify-end mb-12">
            <div class="w-64">
                <div class="flex justify-between py-2 text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-mono font-bold">৳<?php echo number_format($bill['total_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between py-2 text-sm text-gray-600 border-b border-gray-200">
                    <span>Tax (0%)</span>
                    <span class="font-mono font-bold">৳0.00</span>
                </div>
                <div class="flex justify-between py-3 text-xl font-black text-blue-600">
                    <span>Total</span>
                    <span class="font-mono">৳<?php echo number_format($bill['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500 border-t border-gray-200 pt-8 mt-12">
            <p class="font-bold text-gray-600 mb-1">Thank you for choosing National Hospital Information Management System.</p>
            <p>If you have any questions concerning this invoice, contact the hospital administration.</p>
        </div>
        
        <div class="mt-8 text-center no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-lg transition">🖨️ Print Invoice</button>
        </div>
    </div>
</body>
</html>
