<div align="center">

  <h1>National Health Information Management System (NHIMS)</h1>
  <p><strong>Centralized Multi-Branch Healthcare Management & Distributed Clinical Operations Platform</strong></p>
  <p>Architected & Developed by <a href="https://github.com/walid-abdullah"><strong>Md. Abdullah Walid</strong></a></p>

  <p>
    <a href="https://nhms.site.je/"><img src="https://img.shields.io/badge/Production_Live-nhms.site.je-2563EB?style=flat-square" alt="Live URL" /></a>
    <img src="https://img.shields.io/badge/Backend-PHP_8.x_%E2%80%A2_PDO-4F46E5?style=flat-square" alt="PHP 8.x" />
    <img src="https://img.shields.io/badge/Database-MySQL_8.0_%E2%80%A2_InnoDB-0284C7?style=flat-square" alt="MySQL 8.0" />
    <img src="https://img.shields.io/badge/Frontend-TailwindCSS_v3_%E2%80%A2_JS_ES6-06B6D4?style=flat-square" alt="Tailwind CSS" />
    <img src="https://img.shields.io/badge/Analytics-Chart.js-E11D48?style=flat-square" alt="Chart.js" />
    <img src="https://img.shields.io/badge/Security-RBAC_%E2%80%A2_Bcrypt-059669?style=flat-square" alt="Security" />
    <img src="https://img.shields.io/badge/Security_%26_Vulnerability_Hardening-CSRF_%E2%80%A2_IDOR_%E2%80%A2_XSS-DC2626?style=flat-square" alt="Security and Vulnerability Hardening" />
  </p>

</div>

---

## 1. Executive Summary & Problem Domain

### 1.1 Context & Scope
The **National Health Information Management System (NHIMS)** is a modern, web-based hospital information ecosystem designed to unify clinical, diagnostic, and administrative workflows across distributed healthcare facilities.

### 1.2 The Core Problem
Traditional hospital infrastructures suffer from severe operational bottlenecks:
- **Fragmented Medical Records:** Clinical histories are trapped in departmental silos, preventing cross-facility patient lookup.
- **Paper-Based Inefficiencies:** Manual prescriptions and physical diagnostic sheets cause record loss, delays, and prescription errors.
- **Unmanaged Outpatient Queues:** Lack of real-time queue visibility leads to overcrowding and appointment conflicts.
- **Disconnected Operations:** Pharmacy stockouts, manual billing errors, and blind emergency resource tracking (ICU beds, blood bank).

### 1.3 The Solution
NHIMS provides a unified platform connecting **6 role-based portals** backed by **22 relational database tables** and **30 foreign key constraints**. It delivers real-time appointment booking, automated QR queue-token generation, structured JSON electronic prescriptions, hospital-letterhead PDF export, a 4-stage laboratory diagnostic pipeline, and pharmacy POS inventory synchronization.

---

## 2. System Architecture

```
+---------------------------------------------------------------------------------------+
|                                  PRESENTATION LAYER                                   |
|  - HTML5, Tailwind CSS v3 with Glassmorphic Design System & Dark/Light Mode Engine    |
|  - Vanilla JavaScript ES6 for DOM Reactivity, Dynamic Filters & Toast Notifications   |
|  - Chart.js for Administrative & Financial Telemetry Visualization                    |
+-------------------------------------------+-------------------------------------------+
                                            |
                                            v
+---------------------------------------------------------------------------------------+
|                                 APPLICATION LOGIC LAYER                               |
|  - Session-Based Authentication & Role-Based Access Control (RBAC) Guard             |
|  - Digital Prescription Engine (JSON Serialization for Multi-Drug Regimens)          |
|  - Outpatient Queue Token & QR Matrix Generator                                       |
|  - 4-Stage Laboratory Diagnostic Workflow State Machine                               |
|  - Pharmacy POS with Atomic Stock Validation & Inventory Deduction                    |
|  - PDO (PHP Data Objects) Persistence Layer with Parameterized SQL Statements         |
+---------------------+---------------------+---------------------+---------------------+
                      |                     |                     |
                      v                     v                     v
+---------------------------+ +---------------------------+ +---------------------------+
|    CLINICAL OPERATIONS    | |    DIAGNOSTICS & PHARMACY | |    HOSPITAL TELEMETRY     |
|  - Appointments & Queue   | |  - 4-Stage Lab Pipeline   | |  - Live Bed Availability  |
|  - Electronic Prescribing | |  - Letterhead PDF Reports | |  - Blood Bank Stock       |
|  - Longitudinal EHR       | |  - POS Inventory Tracking | |  - Ambulance Fleet Standby|
+---------------------------+ +---------------------------+ +---------------------------+
                                            |
                                            v
+---------------------------------------------------------------------------------------+
|                                    PERSISTENCE LAYER                                  |
|  - MySQL 8.0+ / MariaDB InnoDB Engine with ACID Transaction Guarantees                |
|  - 22 Normalized Tables with 30 Foreign Key Constraints & Cascade Integrity           |
+---------------------------------------------------------------------------------------+
```

---

## 3. Core Role-Based Portals

| Portal | Primary Scope & Key Features |
| :--- | :--- |
| **Public Portal (`/`)** | Hospital branch explorer, doctor specialty directory, real-time bed/blood/ambulance telemetry, and interactive virtual assistant chatbot. |
| **Patient Portal (`/patient`)** | Appointment booking (Physical/Telemedicine), digital queue tokens with QR code, electronic prescription viewer, lab report access, and treatment timeline tracking. |
| **Doctor Station (`/doctor`)** | Daily appointment queue, patient longitudinal EHR history, and structured digital prescription authoring with dosage instructions. |
| **Receptionist Desk (`/receptionist`)** | Walk-in patient registration, appointment queue triage, confirmation, and scheduling management. |
| **Laboratory Suite (`/lab`)** | Diagnostic order queue, 4-stage test state management (`Pending` -> `Sample Collected` -> `Processing` -> `Completed`), and clinical report compilation. |
| **Pharmacy POS (`/pharmacy`)** | Pharmaceutical inventory management, low-stock alerts, expiry monitoring, and point-of-sale terminal with instant stock deduction. |
| **Admin Control Plane (`/admin`)** | Executive analytics, staff & doctor provisioning, ward/bed configuration, blood bank management, ambulance dispatch, HR payroll, and immutable audit logs. |

---

## 4. Technical Specifications & Stack Matrix

| Layer | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Backend Engine** | PHP (Raw PHP) | `8.x` | Server-side execution, procedural-modular business logic, session validation |
| **Persistence** | MySQL / MariaDB | `8.0+` | Relational database, InnoDB storage engine, foreign key cascade integrity |
| **DB Abstraction** | PDO (PHP Data Objects) | Native | Secure database access with strictly enforced parameterized prepared statements |
| **UI & Styling** | Tailwind CSS CDN | `v3.x` | Utility-first responsive design, glassmorphic layout tokens, native dark mode |
| **Client Scripting** | JavaScript (Vanilla) | `ES6+` | DOM reactivity, async toast notifications, dynamic client-side filtering |
| **Visualization** | Chart.js | `v4.x` | Client-side HTML5 Canvas rendering of revenue distribution and financial metrics |
| **QR Engine** | QR Server REST API | Native | Dynamic SVG/PNG QR matrix generation for outpatient queue tokens |
| **Hosting** | Apache Cloud Host | Live | Production deployment at [nhms.site.je](https://nhms.site.je/) |

---

## 5. End-to-End Workflow

```
  1. Patient Books Appointment (Physical or Telemedicine)
          |
          v
  2. Receptionist Reviews & Confirms Appointment
          |
          v
  3. Patient Receives Digital Queue Token (with Verifiable QR Code)
          |
          v
  4. Doctor Conducts Consultation & Writes Digital Prescription (Medicines + Lab Tests)
          |
          v
  5. Laboratory Staff Processes Tests (Pending -> Sample Collected -> Processing -> Completed)
          |
          v
  6. Patient Downloads Hospital-Pad Prescription & Diagnostic Report PDFs
```

---

## 6. Database Schema Overview (22 Tables)

The database is structured in 3rd Normal Form (3NF) across 22 normalized entities:

- **Core & Authentication:** `users`, `patients`, `doctors`, `hospitals`, `departments`
- **Clinical & Consultations:** `appointments`, `prescriptions`, `medical_records`
- **Diagnostics:** `lab_services`, `laboratory_tests`
- **Pharmacy & Operations:** `pharmacy_inventory`, `pharmacy_sales`, `billing`
- **Hospital Resources:** `wards`, `beds`, `admissions`, `blood_bank`, `blood_donors`, `ambulances`
- **Administration & Audit:** `payrolls`, `patient_feedback`, `system_logs`

---

## 7. Local Installation & Development Setup

### Prerequisites
- **Web Server:** Apache (via XAMPP / LAMP / WAMP stack)
- **PHP:** `8.0` or higher (with `pdo_mysql` and `json` enabled)
- **Database:** MySQL `8.0+` or MariaDB `10.4+`

### Quick Start

```bash
# 1. Clone the repository into your local web root
cd /Applications/XAMPP/xamppfiles/htdocs/
git clone https://github.com/walid-abdullah/National-Hospital-Management-System.git nhms

# 2. Enter project directory
cd nhms
```

### Database Setup
1. Start **Apache** and **MySQL** in XAMPP.
2. Navigate to `http://localhost/phpmyadmin/` and create a database named `nhms`.
3. Import the database schema from:
   ```
   nhms/database/nhms_live_update.sql
   ```
4. Verify database credentials in `config/db.php`:
   ```php
   $host     = "localhost";
   $dbname   = "nhms";
   $username = "root";
   $password = "";
   ```
5. Access the application in your browser: `http://localhost/nhms`

---

## 8. Live Production Access & Demo Credentials

**Production URL:** [https://nhms.site.je/](https://nhms.site.je/)

| Portal | Role | Username | Password | Key Action to Test |
| :--- | :--- | :--- | :--- | :--- |
| **Receptionist** | Front Desk | `01744444444` | `123456` | Confirm pending appointments |
| **Doctor** | Physician | `01711111111` | `123456` | Write digital prescription with lab tests |
| **Laboratory** | Diagnostic Staff | `01733333333` | `123456` | Update test results (4-stage status) |
| **Patient** | Healthcare User | `01722222222` | `123456` | View Queue Token (QR) & download PDF Prescription |
| **Pharmacist** | Pharmacy Staff | `017888888888` | `123456` | Check inventory and POS cash register |
| **Admin** | Administrator | `admin` | `123456` | View financial analytics, wards & HR payroll |

---

## 9. Security & System Integrity

- **Role-Based Access Control (RBAC):** Gated session verification on all protected endpoints (`/admin`, `/doctor`, `/patient`, `/receptionist`, `/lab`, `/pharmacy`).
- **SQL Injection Prevention:** 100% of database queries execute through **PDO Prepared Statements** with bound parameters.
- **Cryptographic Security:** Passwords hashed with one-way **Bcrypt** algorithm via `password_hash()`.
- **XSS Defense:** Dynamic output escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **CSRF Protection:** CSRF token validation is enforced across all state-changing forms and actions.
- **Session Fixation Protection:** Successful authentication regenerates the session identifier with `session_regenerate_id(true)`.
- **IDOR Prevention:** Clinical prescriptions and invoices enforce server-side ownership and role authorization checks.
- **Stored XSS Sanitization:** User-controlled values are escaped before HTML output using the shared `e()` helper.
- **Role Registration Allowlisting:** Public registration accepts only approved non-administrative roles; Admin registration is rejected.
- **System Audit Trail:** Administrative activities logged in `system_logs` with IP signatures and timestamps.

### Security Change Deployment

Use these commands to stage all modified files, create a descriptive commit, and push directly to `origin main`:

```bash
git add -A
git commit -m "fix: harden PHP security controls"
git push origin main
```

---

## 10. Academic Context & Attribution

This project was developed for **CSE 06133248 / CSE 06133249: Software Engineering Lab**, Department of Computer Science and Engineering, **World University of Bangladesh (WUB)**, under the academic supervision of **Dr. Md. Amran Hossen** (Assistant Professor, Dept. of CSE).

---

## 11. License & Intellectual Property

```
Copyright (c) 2026 Md. Abdullah Walid. All rights reserved.
Developed as a Software Engineering Capstone Project at World University of Bangladesh.
```

<div align="center">
  <sub>Architected with Precision by <strong><a href="https://github.com/walid-abdullah">Md. Abdullah Walid</a></strong></sub>
</div>
