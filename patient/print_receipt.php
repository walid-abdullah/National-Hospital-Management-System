<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['Patient', 'Admin'], true)) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

$bill_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bill_id) {
    http_response_code(400);
    exit('Invalid receipt.');
}

$stmt = $conn->prepare(
    "SELECT b.id, b.invoice_number, b.total_amount, b.status, b.bill_date, b.details,
            b.payment_method, p.name AS patient_name, p.age, p.phone, p.address,
            h.name AS hospital_name, h.location AS hospital_address, h.contact_number AS hospital_contact
     FROM billing b
     JOIN patients p ON b.patient_id = p.id
     JOIN hospitals h ON b.hospital_id = h.id
     WHERE b.id = :bill_id
       AND (:role = 'Admin' OR p.user_id = :user_id)
     LIMIT 1"
);
$stmt->execute([
    ':bill_id' => $bill_id,
    ':role' => $_SESSION['role'],
    ':user_id' => $_SESSION['user_id'],
]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    http_response_code(404);
    exit('Receipt not found or access denied.');
}

$details = json_decode($bill['details'] ?? '', true);
$line_items = [];
if (is_array($details)) {
    foreach ($details as $key => $item) {
        if (is_array($item)) {
            $description = $item['name'] ?? $item['item'] ?? 'Service';
            $amount = (float) ($item['cost'] ?? $item['price'] ?? $item['amount'] ?? 0);
        } else {
            $description = is_string($key) ? $key : 'Service';
            $amount = (float) $item;
        }
        $line_items[] = ['description' => $description, 'amount' => $amount];
    }
}
if (!$line_items) {
    $line_items[] = ['description' => 'General Consultation', 'amount' => (float) $bill['total_amount']];
}

$payment_method = in_array($bill['payment_method'], ['Cash', 'Card', 'Online'], true)
    ? $bill['payment_method']
    : 'Cash';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($bill['invoice_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e2e8f0; }
        .pad-container {
            background: #ffffff;
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-height: 1050px;
            position: relative;
        }
        .header-border { border-bottom: 2px solid #0f766e; }
        .footer-border { border-top: 2px solid #0f766e; }
        .paid-stamp {
            border: 3px solid #16a34a;
            color: #16a34a;
            font-weight: 800;
            letter-spacing: .15em;
            transform: rotate(-10deg);
        }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #ffffff; margin: 0; padding: 0; }
            .pad-container { box-shadow: none; margin: 0; padding: 20px; width: 100%; min-height: 100vh; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded shadow-lg mr-4">Print / Save as PDF</button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded shadow-lg">Close</button>
    </div>

    <main class="pad-container flex flex-col">
        <div class="flex justify-between items-center header-border pb-4 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-emerald-700"><?php echo e($bill['hospital_name']); ?></h1>
                <p class="text-gray-600 text-sm mt-1">National Hospital Information Management System</p>
                <p class="text-gray-600 text-sm"><?php echo e($bill['hospital_address']); ?></p>
                <p class="text-gray-600 text-sm">Contact: <?php echo e($bill['hospital_contact']); ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-gray-800">Payment Receipt</h2>
                <p class="text-emerald-600 font-semibold">Official Document</p>
            </div>
        </div>

        <div class="flex justify-between text-sm bg-emerald-50 p-4 rounded-lg mb-8">
            <div>
                <p><span class="font-semibold">Patient Name:</span> <?php echo e($bill['patient_name']); ?></p>
                <p><span class="font-semibold">Age:</span> <?php echo e($bill['age']); ?></p>
                <p><span class="font-semibold">Phone:</span> <?php echo e($bill['phone']); ?></p>
            </div>
            <div class="text-right">
                <p><span class="font-semibold">Invoice Number:</span> <?php echo e($bill['invoice_number']); ?></p>
                <p><span class="font-semibold">Date/Time:</span> <?php echo e(date('d M Y, h:i A', strtotime($bill['bill_date']))); ?></p>
                <p><span class="font-semibold">Payment Method:</span> <?php echo e($payment_method); ?></p>
            </div>
        </div>

        <div class="flex-grow">
            <div class="flex justify-between items-center border-b-2 border-gray-200 pb-2 mb-4">
                <h3 class="text-xl font-bold text-gray-800">Breakdown of Fees</h3>
                <?php if ($bill['status'] === 'Paid'): ?>
                    <span class="paid-stamp px-3 py-1 text-lg rounded">PAID</span>
                <?php endif; ?>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm uppercase">
                        <th class="p-3 border-y border-gray-200">Description</th>
                        <th class="p-3 border-y border-gray-200 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($line_items as $item): ?>
                        <tr>
                            <td class="p-3 border-b border-gray-100 text-gray-800"><?php echo e((string) $item['description']); ?></td>
                            <td class="p-3 border-b border-gray-100 text-gray-800 text-right font-semibold">৳<?php echo number_format($item['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="flex justify-end mt-6">
                <div class="w-64 border-t-2 border-emerald-600 pt-3">
                    <div class="flex justify-between text-xl font-black text-emerald-700">
                        <span>Total Amount</span>
                        <span>৳<?php echo number_format((float) $bill['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-border pt-4 mt-8 flex justify-between items-end">
            <div class="text-xs text-gray-500">
                <p>Received with thanks. This is a computer-generated receipt from NHIMS.</p>
                <p>For billing queries, please contact the hospital administration.</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-800 w-40 mx-auto mb-1"></div>
                <p class="text-sm font-semibold text-gray-800">Authorized Signature</p>
            </div>
        </div>
    </main>
</body>
</html>
