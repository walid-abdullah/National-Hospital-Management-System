<?php
require_once 'config/db.php';

try {
    // Fetch all patients and doctors to map random IDs
    $stmt_p = $conn->query("SELECT patient_id FROM patients");
    $patients = $stmt_p->fetchAll(PDO::FETCH_COLUMN);

    $stmt_d = $conn->query("SELECT doctor_id FROM doctors");
    $doctors = $stmt_d->fetchAll(PDO::FETCH_COLUMN);

    if (empty($patients) || empty($doctors)) {
        die("Please ensure you have patients and doctors seeded first.");
    }

    // Function to get random item from array
    function rand_item($arr) {
        return $arr[array_rand($arr)];
    }

    // --- 1. Seed Medical Records ---
    $diagnoses = ['Viral Fever', 'Hypertension', 'Type 2 Diabetes', 'Migraine', 'Osteoarthritis', 'Asthma', 'Gastritis', 'Anemia', 'Pneumonia', 'Allergic Rhinitis'];
    $treatments = ['Rest and fluids', 'Prescribed anti-hypertensive drugs', 'Diet control and Metformin', 'Painkillers and rest', 'Physiotherapy', 'Inhaler prescribed', 'Antacids', 'Iron supplements', 'Antibiotics course', 'Antihistamines'];
    
    $stmt = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, visit_date) VALUES (?, ?, ?, ?, ?)");
    for ($i = 0; $i < 20; $i++) {
        $p_id = rand_item($patients);
        $d_id = rand_item($doctors);
        $diag = rand_item($diagnoses);
        $treat = rand_item($treatments);
        $date = date('Y-m-d', strtotime('-' . rand(1, 30) . ' days'));
        $stmt->execute([$p_id, $d_id, $diag, $treat, $date]);
    }
    echo "Seeded 20 Medical Records.<br>";

    // --- 2. Seed Prescriptions ---
    $medicines = ['Paracetamol 500mg', 'Amlodipine 5mg', 'Metformin 500mg', 'Ibuprofen 400mg', 'Amoxicillin 250mg', 'Omeprazole 20mg', 'Cetirizine 10mg', 'Salbutamol Inhaler', 'Vitamin C', 'Iron Syrup'];
    $dosages = ['1-1-1 (After meal)', '1-0-1 (After meal)', '0-0-1 (Before sleep)', '1-1-0', '1-0-0 (Morning)', 'Take when pain occurs', '2 puffs twice daily', '1-1-1 for 7 days', '1-0-1 for 5 days', '0-1-0 (After lunch)'];
    
    $stmt = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, medicine, dosage) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < 25; $i++) {
        $p_id = rand_item($patients);
        $d_id = rand_item($doctors);
        $med = rand_item($medicines);
        $dos = rand_item($dosages);
        $stmt->execute([$p_id, $d_id, $med, $dos]);
    }
    echo "Seeded 25 Prescriptions.<br>";

    // --- 3. Seed Laboratory Tests ---
    $test_names = ['Complete Blood Count (CBC)', 'MRI Scan', 'Digital X-Ray', 'Lipid Profile', 'ECG / EKG', 'Thyroid Test (TSH)', 'Blood Glucose (Fasting)', 'Urine Routine', 'Liver Function Test', 'Kidney Function Test'];
    $test_results = ['Normal', 'Slightly Elevated', 'Pending (Hospital Visit)', 'Pending (Home Collection)', 'Abnormal', 'Clear', 'Positive', 'Negative', 'Review Needed', 'Completed - See Attached Report'];
    
    $stmt = $conn->prepare("INSERT INTO laboratory_tests (patient_id, test_name, test_result, test_date) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < 20; $i++) {
        $p_id = rand_item($patients);
        $t_name = rand_item($test_names);
        $t_res = rand_item($test_results);
        // Mix of past and future dates
        $date = date('Y-m-d', strtotime((rand(0,1) ? '-' : '+') . rand(1, 15) . ' days'));
        $stmt->execute([$p_id, $t_name, $t_res, $date]);
    }
    echo "Seeded 20 Laboratory Tests.<br>";

    // --- 4. Seed Billing ---
    $statuses = ['Paid', 'Paid', 'Unpaid', 'Unpaid', 'Pending'];
    
    $stmt = $conn->prepare("INSERT INTO billing (patient_id, amount, payment_status, bill_date) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < 20; $i++) {
        $p_id = rand_item($patients);
        $amt = rand(500, 15000); // Amount in BDT
        $status = rand_item($statuses);
        $date = date('Y-m-d', strtotime('-' . rand(0, 10) . ' days'));
        $stmt->execute([$p_id, $amt, $status, $date]);
    }
    echo "Seeded 20 Billing Records.<br>";

    echo "<br><b>All missing demo data has been successfully injected!</b>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
