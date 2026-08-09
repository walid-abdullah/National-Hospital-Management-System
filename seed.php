<?php
require_once 'config/db.php';

echo "<h1>Starting NHIMS 5.0 Data Seeding...</h1>";

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction();

    echo "Generating new massive dummy data...<br><br>";

    // Execute schema first
    echo "0. Dropping existing tables and recreating schema...<br>";
    $sql = file_get_contents('database/nhms.sql');
    $conn->exec($sql);
    echo "Schema recreated successfully.<br>";

    $pass = password_hash('123456', PASSWORD_DEFAULT); // Upgraded from md5 to standard bcrypt

    // 1. Insert Hospitals
    echo "1. Seeding Hospitals...<br>";
    $hospitals = [
        ['Dhaka Central Hospital', 'Dhanmondi, Dhaka', '01711000001', 'dhaka@nhms.com'],
        ['Chattogram General Hospital', 'GEC Circle, Chattogram', '01811000002', 'chattogram@nhms.com'],
        ['Sylhet Care Clinic', 'Zindabazar, Sylhet', '01911000003', 'sylhet@nhms.com'],
        ['Rajshahi Medical Center', 'Saheb Bazar, Rajshahi', '01722000004', 'rajshahi@nhms.com'],
        ['Khulna City Hospital', 'Shib Bari, Khulna', '01822000005', 'khulna@nhms.com'],
        ['Barishal Health Care', 'Sadar Road, Barishal', '01922000006', 'barishal@nhms.com'],
        ['Rangpur Specialist Hospital', 'Dhap, Rangpur', '01733000007', 'rangpur@nhms.com'],
        ['Mymensingh Life Care', 'Charpara, Mymensingh', '01833000008', 'mymensingh@nhms.com']
    ];
    
    $h_ids = [];
    $stmt = $conn->prepare("INSERT INTO hospitals (name, location, contact_number, email) VALUES (?, ?, ?, ?)");
    foreach ($hospitals as $h) {
        $stmt->execute($h);
        $h_ids[] = $conn->lastInsertId();
    }

    // 2. Insert Global Admin (User 1, no specific hospital)
    echo "2. Seeding Users (Admin, Receptionists, Pharmacists, Lab Techs)...<br>";
    $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (NULL, 'admin', ?, 'Admin', 'Approved', 'ADMIN-001')")->execute([$pass]);

    $receptionist_uids = [];
    $pharmacist_uids = [];
    $labstaff_uids = [];
    
    foreach ($h_ids as $hid) {
        // Receptionist per hospital
        $status_rec = ($hid % 2 == 0) ? 'Pending' : 'Approved'; // Just to have some pending
        $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (?, ?, ?, 'Receptionist', ?, ?)")->execute([$hid, 'r'.$hid, $pass, $status_rec, "REC-$hid"]);
        $receptionist_uids[$hid] = $conn->lastInsertId();

        // Pharmacist per hospital
        $status_ph = ($hid % 3 == 0) ? 'Pending' : 'Approved';
        $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (?, ?, ?, 'Pharmacist', ?, ?)")->execute([$hid, 'ph'.$hid, $pass, $status_ph, "PHM-$hid"]);
        $pharmacist_uids[$hid] = $conn->lastInsertId();

        // Lab Staff per hospital
        $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (?, ?, ?, 'Laboratory Staff', 'Approved', ?)")->execute([$hid, 'lab'.$hid, $pass, "LAB-$hid"]);
        $labstaff_uids[$hid] = $conn->lastInsertId();
    }

    // 2.5 Lab Services
    echo "2.5 Seeding Lab Services...<br>";
    $lab_services = [
        ['name' => 'Complete Blood Count (CBC)', 'dept' => 'Pathology', 'price' => 500],
        ['name' => 'Dengue NS1 Antigen', 'dept' => 'Pathology', 'price' => 800],
        ['name' => 'Lipid Profile', 'dept' => 'Biochemistry', 'price' => 1200],
        ['name' => 'HbA1c', 'dept' => 'Biochemistry', 'price' => 900],
        ['name' => 'Chest X-Ray', 'dept' => 'Radiology', 'price' => 600],
        ['name' => 'MRI Scan (Brain)', 'dept' => 'Radiology', 'price' => 8000]
    ];
    $ls_ids = [];
    $stmt_ls = $conn->prepare("INSERT INTO lab_services (hospital_id, service_name, department, price) VALUES (?, ?, ?, ?)");
    foreach ($h_ids as $hid) {
        foreach ($lab_services as $ls) {
            $stmt_ls->execute([$hid, $ls['name'], $ls['dept'], $ls['price']]);
            $ls_ids[$hid][] = $conn->lastInsertId();
        }
    }

    // 3. Departments
    echo "3. Seeding Departments...<br>";
    $dept_names = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'Pathology'];
    $dept_ids = [];
    $stmt = $conn->prepare("INSERT INTO departments (hospital_id, name, type) VALUES (?, ?, ?)");
    foreach ($h_ids as $hid) {
        foreach ($dept_names as $dn) {
            $type = ($dn == 'Pathology') ? 'Laboratory' : 'Medical';
            $stmt->execute([$hid, $dn, $type]);
            $dept_ids[$hid][] = $conn->lastInsertId();
        }
    }

    // 4. Doctors (5 per hospital)
    echo "4. Seeding 15 Doctors...<br>";
    $doctor_ids = [];
    $stmt_u = $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (?, ?, ?, 'Doctor', 'Approved', ?)");
    $stmt_d = $conn->prepare("INSERT INTO doctors (user_id, hospital_id, department_id, name, specialization, phone, consultation_fee) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $doc_names = ['Dr. Smith', 'Dr. Rahman', 'Dr. Sarah', 'Dr. Ali', 'Dr. Hossain', 'Dr. Lee', 'Dr. Fatema', 'Dr. Khan', 'Dr. Emily', 'Dr. Hasan', 'Dr. Kabir', 'Dr. Riya', 'Dr. Alex', 'Dr. Zara', 'Dr. Mahbub'];
    
    $d_idx = 0;
    foreach ($h_ids as $hid) {
        for ($i=0; $i<5; $i++) {
            $username = 'd' . ($d_idx + 1);
            $stmt_u->execute([$hid, $username, $pass, "BMDC-$hid-$d_idx"]);
            $uid = $conn->lastInsertId();
            
            $d_name = $doc_names[$d_idx % count($doc_names)] . " " . $d_idx;
            $spec = $dept_names[$i]; // Match specialization with department
            $did = $dept_ids[$hid][$i]; // Get department id
            
            $stmt_d->execute([$uid, $hid, $did, $d_name, $spec, '017000000' . str_pad($d_idx, 2, '0', STR_PAD_LEFT), 1000]);
            $doctor_ids[$hid][] = $conn->lastInsertId();
            $d_idx++;
        }
    }

    // 4.5 Seeding Payrolls for Staff
    echo "4.5 Seeding Payrolls...<br>";
    $pay_stmt = $conn->prepare("INSERT INTO payrolls (user_id, month_year, base_salary, bonus, deductions, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, 'Paid')");
    $months = [date('F Y', strtotime('-2 months')), date('F Y', strtotime('-1 months')), date('F Y')];
    
    foreach ($h_ids as $hid) {
        // Staff arrays: Receptionists, Pharmacists, Lab, Doctors
        $staff_to_pay = [
            ['id' => $receptionist_uids[$hid], 'base' => 25000],
            ['id' => $pharmacist_uids[$hid], 'base' => 35000],
            ['id' => $labstaff_uids[$hid], 'base' => 30000]
        ];
        
        foreach($staff_to_pay as $stp) {
            foreach($months as $m) {
                $pay_stmt->execute([$stp['id'], $m, $stp['base'], 1000, 500, $stp['base'] + 500]);
            }
        }
    }

    // 5. Patients (30 per hospital = 90 total)
    echo "5. Seeding 90 Patients...<br>";
    $patient_ids = [];
    $stmt_u = $conn->prepare("INSERT INTO users (hospital_id, username, password, role, status, identification_number) VALUES (?, ?, ?, 'Patient', 'Approved', ?)");
    $stmt_p = $conn->prepare("INSERT INTO patients (user_id, hospital_id, name, age, gender, phone, address, blood_group) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $b_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    $p_idx = 1;
    foreach ($h_ids as $hid) {
        for ($i=0; $i<30; $i++) {
            $phone = '0180' . str_pad($p_idx, 6, '0', STR_PAD_LEFT);
            $stmt_u->execute([$hid, $phone, $pass, "NID-1990" . str_pad($p_idx, 6, '0', STR_PAD_LEFT)]); // Phone is username
            $uid = $conn->lastInsertId();
            
            $gender = ($i % 2 == 0) ? 'Male' : 'Female';
            $bg = $b_groups[array_rand($b_groups)];
            $stmt_p->execute([$uid, $hid, "Patient $p_idx", rand(10, 80), $gender, $phone, "Address $p_idx", $bg]);
            $patient_ids[$hid][] = $conn->lastInsertId();
            $p_idx++;
        }
    }

    // 6. Blood Bank Inventory (per hospital)
    echo "6. Seeding Blood Bank Stock...<br>";
    $stmt = $conn->prepare("INSERT INTO blood_bank (hospital_id, blood_group, bags_available) VALUES (?, ?, ?)");
    foreach ($h_ids as $hid) {
        foreach ($b_groups as $bg) {
            // Randomly set some blood groups very low (e.g. < 5 bags) to trigger alerts
            $stock = (rand(1, 10) > 8) ? rand(0, 4) : rand(10, 50);
            $stmt->execute([$hid, $bg, $stock]);
        }
    }

    // 7. Wards & Beds
    echo "7. Seeding Wards and Beds...<br>";
    $ward_types = ['General' => 1500, 'ICU' => 10000, 'CCU' => 8000, 'VIP Cabin' => 5000];
    $stmt_w = $conn->prepare("INSERT INTO wards (hospital_id, name, type, price_per_day) VALUES (?, ?, ?, ?)");
    $stmt_b = $conn->prepare("INSERT INTO beds (ward_id, bed_number, status) VALUES (?, ?, ?)");
    
    foreach ($h_ids as $hid) {
        foreach ($ward_types as $wt => $price) {
            $stmt_w->execute([$hid, "$wt Ward", $wt, $price]);
            $wid = $conn->lastInsertId();
            
            // 5 beds per ward
            for ($i=1; $i<=5; $i++) {
                $status = (rand(1, 10) > 7) ? 'Occupied' : 'Available';
                $stmt_b->execute([$wid, substr($wt, 0, 1) . "-$i", $status]);
            }
        }
    }

    // 8. Pharmacy Inventory (200 medicines)
    echo "8. Seeding Pharmacy Inventory (200 Medicines)...<br>";
    $stmt = $conn->prepare("INSERT INTO pharmacy_inventory (hospital_id, medicine_name, category, stock_quantity, unit_price, expiry_date, manufacturer) VALUES (?, ?, ?, ?, ?, ?, ?)");
    for ($i=1; $i<=200; $i++) {
        foreach ($h_ids as $hid) {
            $date = date('Y-m-d', strtotime('+' . rand(100, 1000) . ' days'));
            // Randomly set some stock < 20 to trigger low stock alerts
            $stock = (rand(1, 20) > 18) ? rand(1, 15) : rand(50, 500);
            $stmt->execute([$hid, "Medicine $i", "Category " . rand(1,5), $stock, rand(10, 500), $date, "PharmaCo $i"]);
        }
    }

    // 9. Appointments & Medical Records (Past 300 appointments per hospital for charting)
    echo "9. Seeding 900+ Past Appointments and Records...<br>";
    $stmt_a = $conn->prepare("INSERT INTO appointments (hospital_id, patient_id, doctor_id, appointment_date, appointment_type, status) VALUES (?, ?, ?, ?, ?, 'Completed')");
    $stmt_b = $conn->prepare("INSERT INTO billing (hospital_id, patient_id, invoice_number, total_amount, payment_method, status, bill_date, details) VALUES (?, ?, ?, ?, 'Online', 'Paid', ?, '{\"consultation\": 1000}')");
    
    // New statements for interconnected data
    $stmt_m = $conn->prepare("INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, visit_date) VALUES (?, ?, ?, ?, ?)");
    $stmt_l = $conn->prepare("INSERT INTO laboratory_tests (hospital_id, patient_id, service_id, status, result_text, test_date) VALUES (?, ?, ?, 'Completed', ?, ?)");

    $diagnoses = ['Viral Fever', 'Migraine', 'Gastritis', 'Hypertension', 'Type 2 Diabetes', 'Bronchitis', 'Asthma', 'Dengue'];
    $treatments = ['Rest and Paracetamol', 'Avoid triggers, take Naproxen', 'Antacids and bland diet', 'Amlodipine 5mg', 'Metformin 500mg', 'Antibiotics for 5 days', 'Inhaler prescribed', 'Fluid replacement'];
    
    $inv_idx = 1000;
    foreach ($h_ids as $hid) {
        for ($i=0; $i<300; $i++) {
            $pid = $patient_ids[$hid][array_rand($patient_ids[$hid])];
            $did = $doctor_ids[$hid][array_rand($doctor_ids[$hid])];
            $days_ago = rand(1, 360);
            $date = date('Y-m-d', strtotime("-$days_ago days"));
            $type = (rand(1, 10) > 8) ? 'Telemedicine' : 'Physical';
            
            // Appointment
            $stmt_a->execute([$hid, $pid, $did, $date, $type]);
            // Billing
            $stmt_b->execute([$hid, $pid, "INV-$inv_idx", 1000, "$date 10:00:00"]);
            
            // 70% chance they get a medical record
            if (rand(1, 10) > 3) {
                $idx = array_rand($diagnoses);
                $stmt_m->execute([$pid, $did, $diagnoses[$idx], $treatments[$idx], $date]);
            }
            
            // 40% chance they did a lab test
            if (rand(1, 10) > 6) {
                $lid = $ls_ids[$hid][array_rand($ls_ids[$hid])];
                $res = "Test results are normal. Slight elevation in RBC count.";
                $stmt_l->execute([$hid, $pid, $lid, $res, $date]);
            }
            
            $inv_idx++;
        }
    }
    
    // 10. Ambulances
    echo "10. Seeding Ambulances...<br>";
    $stmt = $conn->prepare("INSERT INTO ambulances (hospital_id, vehicle_number, driver_name, driver_phone, status) VALUES (?, ?, ?, ?, ?)");
    foreach ($h_ids as $hid) {
        $stmt->execute([$hid, "DHA-11-2233", "Driver Rahim", "01900000001", "Available"]);
        $stmt->execute([$hid, "DHA-11-4455", "Driver Karim", "01900000002", "Available"]);
    }
    
    // 11. Patient Feedback (Reviews for the homepage)
    echo "11. Seeding Patient Feedback...<br>";
    $stmt = $conn->prepare("INSERT INTO patient_feedback (hospital_id, patient_id, doctor_id, rating, review) VALUES (?, ?, ?, ?, ?)");
    $reviews = [
        "The doctors here are world-class. I was treated with utmost care and the facilities are outstanding.",
        "Very clean and modern hospital. The staff was incredibly helpful during my entire stay.",
        "Booking an appointment online was so easy, and I got my lab reports quickly through the portal. Excellent service!",
        "The emergency ambulance service was very prompt. Truly grateful for the quick response and expert doctors.",
        "Highly recommended! Best healthcare experience I've ever had. Professional and compassionate team."
    ];
    
    foreach ($h_ids as $hid) {
        for ($i=0; $i<3; $i++) {
            $pid = $patient_ids[$hid][array_rand($patient_ids[$hid])];
            $did = $doctor_ids[$hid][array_rand($doctor_ids[$hid])];
            $review = $reviews[array_rand($reviews)];
            $stmt->execute([$hid, $pid, $did, 5, $review]);
        }
    }

    // 12. System Audit Logs
    echo "12. Seeding System Audit Logs...<br>";
    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, '192.168.1.1', ?)");
    $actions = [
        ['action' => 'User Login', 'details' => 'Successfully logged into the system dashboard.'],
        ['action' => 'Appointment Booked', 'details' => 'New appointment created for upcoming week.'],
        ['action' => 'Prescription Issued', 'details' => 'Prescribed Napa Extra and Seclo 20mg.'],
        ['action' => 'Invoice Generated', 'details' => 'Generated invoice for Lab Tests and Consultation.'],
        ['action' => 'Lab Report Uploaded', 'details' => 'Uploaded CBC and Dengue NS1 results.'],
        ['action' => 'Patient Admitted', 'details' => 'Assigned patient to General Ward Bed G-2.'],
        ['action' => 'Blood Unit Dispatched', 'details' => 'Dispatched 2 bags of O+ blood to ICU.']
    ];
    
    // We get some random user_ids from existing users
    $all_users_stmt = $conn->query("SELECT id FROM users");
    $all_user_ids = $all_users_stmt->fetchAll(PDO::FETCH_COLUMN);

    if(!empty($all_user_ids)) {
        for($i=0; $i<50; $i++) {
            $random_user = $all_user_ids[array_rand($all_user_ids)];
            $random_action = $actions[array_rand($actions)];
            // Make some logs from recent times
            $minutes_ago = rand(1, 1440); // Within last 24h
            $date = date('Y-m-d H:i:s', strtotime("-$minutes_ago minutes"));
            
            $stmt->execute([$random_user, $random_action['action'], $random_action['details'], $date]);
        }
    }

    $conn->commit();
    echo "<h2>✅ Success! The entire NHIMS ERP database is seeded and ready for action!</h2>";

} catch (Exception $e) {
    if(isset($conn)) $conn->rollBack();
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
