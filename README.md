# 🏥 NHIMS — National Hospital Information Management System

A comprehensive, role-based web platform that digitizes and automates the complete hospital workflow — from patient registration to final lab report delivery.

🌐 **Live Demo:** [https://nhms.site.je/](https://nhms.site.je/)

---

## 📌 Project Overview

NHIMS is a **Software Engineering** course project that connects 6 types of users through dedicated portals on a single platform in real-time. It eliminates paper-based hospital workflows by providing digital prescriptions, queue tokens, lab reports, billing, and much more.

---

## ✨ Key Features

- 🔐 Role-based authentication (6 roles)
- 📅 Online appointment booking (Physical & Telemedicine)
- 🎟️ Digital Queue Token with QR Code
- 📄 Professional Hospital Prescription Pad (PDF)
- 🔬 Lab Report generation on hospital letterhead
- 💰 Billing & Invoice management (Cash / Card / Online)
- 🗺️ Treatment Journey Tracking (Timeline)
- 🩸 Live Blood Bank, 🛏️ Bed Availability, 🚑 Ambulance tracking
- 📊 Admin Analytics Dashboard
- 💊 Pharmacy Inventory & Point-of-Sale
- 👥 HR & Payroll management
- 🌙 Dark Mode support

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, Tailwind CSS, JavaScript |
| Backend | PHP 8.x (Raw PHP, no framework) |
| Database | MySQL (22 Tables) |
| Security | PDO Prepared Statements, Password Hashing |
| Hosting | InfinityFree (Live Cloud Server) |
| Dev Tools | XAMPP, phpMyAdmin, VS Code |

---

## 👥 User Portals & Demo Credentials

| Portal | Username | Password |
|--------|----------|----------|
| 🤒 Patient | `01722222222` | `123456` |
| 👩‍💼 Receptionist | `01744444444` | `123456` |
| 👨‍⚕️ Doctor | `01711111111` | `123456` |
| 🔬 Lab Staff | `01733333333` | `123456` |
| 💊 Pharmacist | `017888888888` | `123456` |
| 🔑 Admin | `admin` | `123456` |

---

## 🔄 System Workflow

```
Patient Books Appointment
        ↓
Receptionist Confirms → Queue Token Generated (QR Code)
        ↓
Doctor Writes Digital Prescription (Medicines + Lab Tests)
        ↓
Lab Staff Updates Test Results
        ↓
Patient Downloads Prescription PDF & Lab Report
```

---

## 🗄️ Database Schema (22 Tables)

**Core:** `users` · `patients` · `doctors` · `appointments` · `prescriptions`

**Clinical:** `laboratory_tests` · `lab_services` · `medical_records`

**Finance:** `billing` · `pharmacy_inventory` · `pharmacy_sales`

**Hospital:** `wards` · `beds` · `admissions` · `blood_bank` · `blood_donors` · `ambulances`

**Other:** `hospitals` · `departments` · `payrolls` · `patient_feedback` · `system_logs`

---

## 🚀 Local Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/walid-abdullah/National-Hospital-Management-System.git
   ```

2. **Move to XAMPP htdocs**
   ```bash
   mv National-Hospital-Management-System /Applications/XAMPP/xamppfiles/htdocs/nhms
   ```

3. **Import the database**
   - Open `phpMyAdmin`
   - Create a database named `nhms`
   - Import the SQL file from the `database/` folder

4. **Configure database connection**
   - Open `config/db.php`
   - Replace placeholder values with your credentials:
   ```php
   $host     = "localhost";
   $dbname   = "nhms";
   $username = "root";
   $password = "";
   ```

5. **Start XAMPP** (Apache + MySQL) and visit:
   ```
   http://localhost/nhms
   ```

---

## 👨‍💻 Team

| Name | Contribution |
|------|-------------|
| Walid Abdullah | Full-stack development — complete website coding, backend, database design & deployment |
| [Member 2] | Presentation slides — design, layout & content |
| [Member 3] | Project report — full documentation & UML diagrams |
| [Member 4] | Testing & QA — bug identification & corrections |

---

## 📚 Course Info

**Course:** Software Engineering Lab Final  
**Semester:** 8th Semester

---

## 📄 License

This project is developed for academic purposes only.
