<?php
require_once '../includes/security.php';
init_secure_session();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Patient') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/db.php';

$bill_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$bill_id) {
    http_response_code(400);
    exit('Invalid receipt.');
}

$stmt = $conn->prepare(
    "SELECT b.id, b.invoice_number, b.total_amount, b.status, b.bill_date, b.details,
            b.payment_method, p.name AS patient_name, p.phone, p.address,
            h.name AS hospital_name, h.location AS hospital_address, h.contact_number AS hospital_contact
     FROM billing b
     JOIN patients p ON b.patient_id = p.id
     JOIN hospitals h ON b.hospital_id = h.id
     WHERE b.id = :bill_id AND p.user_id = :user_id
     LIMIT 1"
);
$stmt->execute([
    ':bill_id' => $bill_id,
    ':user_id' => $_SESSION['user_id'],
]);
$bill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bill) {
    http_response_code(404);
    exit('Receipt not found.');
}

$details = json_decode($bill['details'] ?? '', true);
$line_items = [];
if (is_array($details)) {
    foreach ($details as $key => $item) {
        if (is_array($item)) {
            $description = $item['name'] ?? $item['item'] ?? 'Service';
            $amount = (float) ($item['cost'] ?? $item['price'] ?? 0);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($bill['invoice_number']); ?></title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f7; color: #172033; font-family: Arial, sans-serif; }
        .receipt { position: relative; max-width: 820px; margin: 40px auto; padding: 48px; background: #fff; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); }
        .header { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 28px; border-bottom: 3px solid #0f766e; }
        .brand { color: #0f766e; font-size: 30px; font-weight: 800; letter-spacing: .08em; }
        .muted { color: #64748b; font-size: 13px; line-height: 1.6; }
        h1 { margin: 4px 0 10px; font-size: 28px; }
        h2 { margin: 0 0 8px; font-size: 14px; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
        .meta { text-align: right; }
        .customer { display: flex; justify-content: space-between; gap: 24px; padding: 28px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
        .amount { text-align: right; font-variant-numeric: tabular-nums; }
        .total { display: flex; justify-content: flex-end; margin-top: 20px; font-size: 21px; font-weight: 800; color: #0f766e; }
        .stamp { position: absolute; top: 170px; right: 48px; padding: 10px 18px; border: 3px solid #16a34a; color: #16a34a; font-size: 24px; font-weight: 900; letter-spacing: .12em; transform: rotate(-10deg); }
        .footer { margin-top: 48px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; }
        .print-button { display: block; margin: 26px auto 0; padding: 11px 22px; border: 0; border-radius: 7px; background: #0f766e; color: #fff; cursor: pointer; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .receipt { max-width: none; margin: 0; padding: 20px; box-shadow: none; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <main class="receipt">
        <?php if ($bill['status'] === 'Paid'): ?><div class="stamp">PAID</div><?php endif; ?>
        <header class="header">
            <div>
                <div class="brand">NHIMS</div>
                <h1><?php echo e($bill['hospital_name']); ?></h1>
                <div class="muted"><?php echo e($bill['hospital_address']); ?><br>Phone: <?php echo e($bill['hospital_contact']); ?></div>
            </div>
            <div class="meta">
                <h1>Payment Receipt</h1>
                <div class="muted">Invoice #: <strong><?php echo e($bill['invoice_number']); ?></strong><br>
                    Date: <?php echo e(date('M d, Y', strtotime($bill['bill_date']))); ?><br>
                    Method: <?php echo e($bill['payment_method'] ?? 'N/A'); ?>
                </div>
            </div>
        </header>

        <section class="customer">
            <div>
                <h2>Received From</h2>
                <strong><?php echo e($bill['patient_name']); ?></strong>
                <div class="muted"><?php echo e($bill['phone']); ?><br><?php echo e($bill['address']); ?></div>
            </div>
            <div class="meta">
                <h2>Status</h2>
                <strong><?php echo e($bill['status']); ?></strong>
            </div>
        </section>

        <table>
            <thead><tr><th>Description</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                <?php foreach ($line_items as $item): ?>
                    <tr>
                        <td><?php echo e((string) $item['description']); ?></td>
                        <td class="amount">৳<?php echo number_format($item['amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">Total Paid: ৳<?php echo number_format((float) $bill['total_amount'], 2); ?></div>
        <footer class="footer muted">Thank you for choosing <?php echo e($bill['hospital_name']); ?>.<br>This receipt was generated electronically by NHIMS.</footer>
        <button type="button" class="print-button" onclick="window.print()">Print / Save as PDF</button>
    </main>
</body>
</html>
