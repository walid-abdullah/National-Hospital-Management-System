<div align="center">

  <h1>NHIMS — National Health Information Management System</h1>
  <p><strong>A Centralized Multi-Branch Healthcare Information & Clinical Operations Platform</strong></p>
  <p>Software Engineering Capstone Project • <strong>World University of Bangladesh</strong></p>
  <p>Architected & Developed by <a href="https://github.com/walid-abdullah"><strong>Md. Abdullah Walid</strong></a> (Lead Full-Stack Engineer)</p>

  <p>
    <a href="https://nhms.site.je/"><img src="https://img.shields.io/badge/Production_Live-nhms.site.je-0f172a?style=flat-square" alt="Production Live" /></a>
    <img src="https://img.shields.io/badge/Backend-PHP_8.x_with_PDO-334155?style=flat-square" alt="PHP 8.x PDO" />
    <img src="https://img.shields.io/badge/Database-MySQL_8.0_InnoDB-1e293b?style=flat-square" alt="MySQL 8.0" />
    <img src="https://img.shields.io/badge/Frontend-Tailwind_CSS_v3-475569?style=flat-square" alt="Tailwind CSS" />
    <img src="https://img.shields.io/badge/Analytics-Chart.js_v4-64748b?style=flat-square" alt="Chart.js" />
    <img src="https://img.shields.io/badge/Security-RBAC_%2F_Bcrypt-0f172a?style=flat-square" alt="Security" />
  </p>

  <p>
    <code>[ OVERVIEW ]</code> ── 
    <code>[ ARCHITECTURE ]</code> ── 
    <code>[ RBAC MATRIX ]</code> ── 
    <code>[ WORKFLOWS ]</code> ── 
    <code>[ DATABASE ]</code> ── 
    <code>[ VERIFICATION ]</code> ── 
    <code>[ LIVE DEMO ]</code>
  </p>

</div>

---

> [!IMPORTANT]
> **Production Live System:** NHIMS is fully deployed and accessible in production at **[https://nhms.site.je/](https://nhms.site.je/)**. All cross-portal workflows—including automated Queue Token QR generation, digital prescription authoring, and four-stage diagnostic report compilation—are fully operational.

> [!NOTE]
> **Academic Standard:** Formulated under IEEE Std 830-1998 Software Requirements Specification guidelines for **CSE 06133248 / CSE 06133249: Software Engineering Lab**, Department of Computer Science and Engineering, World University of Bangladesh.

---

## 1. Executive Summary & Problem Domain

### 1.1 Academic & Technical Context
The **National Health Information Management System (NHIMS)** is a three-tier, multi-tenant capable healthcare information platform designed to digitize, centralize, and synchronize clinical, diagnostic, and administrative workflows across distributed hospital facilities. 

Supervised by **Dr. Md. Amran Hossen** (Assistant Professor, Dept. of CSE), the project addresses systemic failures in traditional paper-based healthcare environments by delivering a unified, role-isolated digital platform.

### 1.2 The Healthcare Fragmentation Bottleneck
Traditional hospital management setups exhibit several operational failures:
- **Isolated Health Records (EHR):** Clinical histories are distributed across disconnected systems; records cannot be securely queried across departments or branches.
- **Paper Workflow Vulnerabilities:** Physical prescriptions and diagnostic slips suffer from loss, illegibility, forgery, and redundant test orders.
- **Unmanaged Outpatient Queues:** Manual appointment scheduling creates physical bottlenecks without real-time queue visibility.
- **Diagnostic Pipeline Latency:** Laboratory results require days to manually reach consulting physicians and patients.
- **Blind Emergency Resource Allocation:** Emergency desks lack real-time telemetry on ICU/CCU bed vacancies, blood bank supplies, and available ambulance fleets.
- **Disjointed Pharmacy Inventory:** Stockouts and expired pharmaceuticals occur due to lack of automated stock deduction upon point-of-sale transactions.

### 1.3 The Technical Solution
NHIMS introduces an integrated architecture spanning **6 distinct role-based sub-systems**, **22 relational database entities**, and **30 foreign key constraints**. It establishes a centralized EHR repository, real-time appointment booking, automated QR queue-token synthesis, structured JSON electronic prescriptions, hospital-letterhead PDF export engines, 4-stage laboratory diagnostic pipelining, real-time inventory tracking, and full administrative audit logging.

---

## 2. System Architecture & High-Level Design

NHIMS is engineered following a clean **Three-Tier Client-Server Architecture** designed for high throughput, session isolation, and strict role-based access control (RBAC).

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                       CLIENT / PRESENTATION TIER                                         │
│  - Responsive Viewport Layer (HTML5, Tailwind CSS v3, Glassmorphism, Dark/Light Mode Engine)             │
│  - Asynchronous Client Utilities (Vanilla JavaScript ES6, Dynamic DOM Reactivity, Modal Controllers)     │
│  - Telemetry Visualizers (Chart.js Line, Bar & Doughnut Visualizations)                                  │
└────────────────────────────────────────────────────┬─────────────────────────────────────────────────────┘
                                                     │ HTTPS / Session Cookies
                                                     ▼
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                     APPLICATION / BUSINESS LOGIC TIER                                    │
│  - Modular Role Dispatchers (/admin, /doctor, /patient, /receptionist, /lab, /pharmacy)                  │
│  - Session Authentication & RBAC Guard (Bcrypt Verification, State & Privilege Enforcement)              │
│  - Domain Processing Engines:                                                                            │
│      * Prescription Engine (Structured Multi-Drug JSON Serialization)                                    │
│      * Queue Token Engine (Dynamic Serial Allocation & QR Code Matrix Synthesizer)                       │
│      * Laboratory Workflow Engine (4-Stage State Transition Pipeline)                                    │
│      * Pharmacy Point of Sale (POS) Engine (Stock Validation & Atomic Decrementation)                    │
│      * HR Payroll Engine (Formulaic Calculation: Net = Base + Bonus - Deductions)                        │
│  - Secure Persistence Gateway (PHP Data Objects - PDO with Parameterized Prepared Statements)            │
└────────────────────────────────────────────────────┬─────────────────────────────────────────────────────┘
                                                     │ PDO Driver / Parameterized SQL
                                                     ▼
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                       DATA / PERSISTENCE TIER                                            │
│  - Relational Database Engine (MySQL 8.0+ / MariaDB InnoDB Engine with ACID Guarantees)                  │
│  - 22 Normalized Tables with 30 Foreign Key Constraints & Referential Cascade Rules                      │
│  - File System Storage (/uploads/documents/ for Secure Attachments & Verification Records)               │
└──────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 2.1 Subsystem Interaction Topology
```
                              ┌───────────────────────────────┐
                              │     Public Client Layer       │
                              │         (index.php)           │
                              └───────────────┬───────────────┘
                                              │
                     ┌────────────────────────┼────────────────────────┐
                     │                        │                        │
                     ▼                        ▼                        ▼
        ┌────────────────────────┐┌───────────────────────┐┌────────────────────────┐
        │  Authentication Gate   ││   Resource Monitor    ││  Online Booking Engine │
        │   (login_action.php)   ││   (Live Beds/Blood)   ││   (book_online.php)    │
        └────────────┬───────────┘└───────────────────────┘└───────────┬────────────┘
                     │                                                 │
                     ▼                                                 │
        ┌────────────────────────┐                                     │
        │ Role-Based Subsystems  │                                     │
        │ ├─ /admin              │                                     │
        │ ├─ /doctor             │                                     │
        │ ├─ /patient            │                                     │
        │ ├─ /receptionist       │                                     │
        │ ├─ /lab                │                                     │
        │ └─ /pharmacy           │                                     │
        └────────────┬───────────┘                                     │
                     │                                                 │
                     └────────────────────────┬────────────────────────┘
                                              │
                                              ▼
                                 ┌─────────────────────────┐
                                 │   MySQL Database Core   │
                                 │       (22 Tables)       │
                                 └─────────────────────────┘
```

---

## 3. Role-Based Access Control (RBAC) Privilege Matrix

Access to protected resources is strictly gated by session role validation at the controller level:

| System Resource / Entity | Admin | Doctor | Patient | Receptionist | Lab Staff | Pharmacist |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| `users` (Account Credentials) | `MANAGE` | `READ [Self]` | `READ [Self]` | `READ [Self]` | `READ [Self]` | `READ [Self]` |
| `appointments` | `FULL` | `UPDATE/VIEW` | `CREATE/READ` | `MANAGE/CONFIRM` | `READ` | `NONE` |
| `prescriptions` | `READ` | `CREATE/EDIT` | `READ [PDF]` | `NONE` | `READ` | `READ` |
| `medical_records` (EHR) | `FULL` | `CREATE/EDIT` | `READ [Self]` | `NONE` | `NONE` | `NONE` |
| `laboratory_tests` | `FULL` | `ORDER` | `READ [PDF]` | `NONE` | `MANAGE/EXEC` | `NONE` |
| `pharmacy_inventory` | `FULL` | `NONE` | `NONE` | `NONE` | `NONE` | `MANAGE/STOCK`|
| `pharmacy_sales` (POS) | `FULL` | `NONE` | `NONE` | `NONE` | `NONE` | `EXECUTE/SALE`|
| `billing` & Invoices | `FULL` | `NONE` | `READ [Self]` | `CREATE/VIEW` | `NONE` | `NONE` |
| `wards` / `beds` / Admissions | `FULL` | `READ` | `NONE` | `ASSIGN` | `NONE` | `NONE` |
| `blood_bank` & Donors | `FULL` | `READ` | `READ [Public]`| `READ` | `UPDATE` | `NONE` |
| `ambulances` Fleet | `FULL` | `READ` | `READ [Public]`| `DISPATCH` | `NONE` | `NONE` |
| `payrolls` & Compensation | `FULL` | `READ [Self]` | `NONE` | `READ [Self]` | `READ [Self]` | `READ [Self]` |
| `system_logs` (Audit Trail) | `AUDIT ONLY` | `NONE` | `NONE` | `NONE` | `NONE` | `NONE` |

---

## 4. Software Development Life Cycle (SDLC)

NHIMS was engineered following the **Incremental Development Model (Agile-Oriented)** across five phases:

```
  Phase 1: Requirements Engineering & Formal SRS
    ├── Multi-stakeholder requirements elicitation (Patient, Doctor, Admin, Lab, Pharmacy, Front Desk)
    ├── Drafting IEEE Std 830-1998 compliant Software Requirements Specification (SRS v2.0)
    └── Formulation of 72 Functional Requirements across 8 functional modules
         │
         ▼
  Phase 2: Architectural Modeling & System Design
    ├── Formalization of 11 UML, Behavioral, State, and Data Flow Diagrams
    ├── 3-NF database schema normalization (22 entities, 30 foreign key constraints)
    └── Formulation of the Tailwind CSS Glassmorphic design system
         │
         ▼
  Phase 3: Implementation & Subsystem Engineering
    ├── Implementation of secure authentication gateway with role-isolated session state
    ├── Construction of the clinical prescription engine with structured JSON serialization
    ├── Engineering printable PDF hospital pads via CSS `@media print` drivers
    ├── Development of dynamic QR vector generation for physical outpatient queue tokens
    └── Implementation of Pharmacy POS with atomic inventory decrement logic
         │
         ▼
  Phase 4: Quality Assurance, Verification & Hardening
    ├── Execution of 12 formal test cases (TC-01 through TC-12) covering Auth, Booking, Clinical, Lab & POS
    ├── SQL injection fuzzing and strict enforcement of PDO parameterization across all endpoints
    ├── Elimination of schema-key naming mismatches across cross-table JOIN queries
    └── Responsive layout verification across desktop, tablet, and mobile browsers
         │
         ▼
  Phase 5: Cloud Deployment & Maintenance
    ├── Configuration of live production virtual host environment
    ├── Database schema migration and loading of deterministic multi-role demonstration datasets
    └── Verification of public SSL/TLS accessibility and real-time user workflows
```

---

## 5. Technical Specifications & Stack Matrix

| Architectural Layer | Technology / Tool | Version / Spec | Functional Scope & Architectural Rationale |
| :--- | :--- | :--- | :--- |
| **Backend Core** | PHP (Hypertext Preprocessor) | `8.x` | Server-side execution, procedural-modular business logic, session validation |
| **Persistence Engine** | MySQL / MariaDB | `8.0+` | ACID-compliant relational persistence, InnoDB engine, foreign key cascade rules |
| **DB Abstraction** | PDO (PHP Data Objects) | Native | Database abstraction layer with strictly enforced parameter binding against SQLi |
| **Frontend Styling** | Tailwind CSS CDN | `v3.x` | Utility-first responsive design, modern glassmorphism tokens, native Dark Mode |
| **Client Scripting** | JavaScript (Vanilla) | `ES6+` | DOM reactivity, async toast notifications, dynamic client-side filtering, chatbot engine |
| **Data Visualization**| Chart.js | `v4.x` | Client-side HTML5 Canvas rendering of financial trends & revenue distribution |
| **Barcode/QR Engine** | QR Server REST API | Native | Dynamic SVG/PNG QR generation for outpatient queue verification |
| **Typography** | Google Fonts (Outfit) | WebFont | Modern, highly legible geometric sans-serif typeface designed for clinical clarity |
| **Local Environment** | Apache (XAMPP Suite) | `2.4.x` | Local cross-platform development server environment |
| **Cloud Deployment** | Apache Cloud Virtual Host | Production | Publicly deployed live environment on cloud infrastructure (`nhms.site.je`) |

---

## 6. System Performance & Security Benchmark Card

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                            NHIMS TECHNICAL SPECIFICATIONS MATRIX                            │
├───────────────────────────────┬───────────────────────────────┬─────────────────────────────┤
│ Evaluation Parameter          │ Standard Target               │ Measured System Capacity    │
├───────────────────────────────┼───────────────────────────────┼─────────────────────────────┤
│ Authentication Encryption     │ One-way Cryptographic Hash    │ Bcrypt (Cost Factor: 10)    │
│ SQL Injection Vulnerability   │ Zero Vulnerability            │ 100% Parameterized PDO      │
│ Cross-Site Scripting (XSS)    │ Strict Output Escaping        │ htmlspecialchars(ENT_QUOTES)│
│ Session Security              │ Server-Side Session Guards    │ Role Validation on Every URI│
│ DOM Initialization Time       │ Sub-second Standard           │ ~850ms FCP on Broadband     │
│ Relational Schema Integrity   │ 3rd Normal Form (3NF)         │ 22 Tables • 30 Foreign Keys │
│ Printable Vector Output       │ Native Vector Rendering       │ Zero External PDF Lib Dep   │
│ Outpatient Queue Verification │ Machine-Readable QR Matrix    │ Dynamic 100x100 Matrix Code │
└───────────────────────────────┴───────────────────────────────┴─────────────────────────────┘
```

---

## 7. System Modeling & UML Specification Index

The system's structural, behavioral, and data layers are modeled through **11 formal UML and engineering diagrams**:

| Figure | Diagram Title | Standard Notation | Primary Domain Scope |
| :---: | :--- | :--- | :--- |
| **Fig. 1** | Component Diagram — System Architecture | UML 2.5 | 3-Tier Layering, Subsystems & Data Persistence Boundary |
| **Fig. 2** | NHIMS Use Case Diagram | UML 2.5 | 6 Primary Actors, 32 Core System Use Cases |
| **Fig. 3** | Registration & Approval Activity Diagram | UML 2.5 | Dual-Track User Provisioning Workflow (Auto vs Pending) |
| **Fig. 4** | Laboratory Test Processing Activity Diagram | UML 2.5 | 4-Stage Diagnostic Execution State Machine |
| **Fig. 5** | Online Appointment Booking Sequence Diagram | UML 2.5 | Asynchronous Message Flow (Client ➔ Controller ➔ DB) |
| **Fig. 6** | Appointment Lifecycle State Chart | UML 2.5 | `Pending` ➔ `Confirmed` ➔ `Completed` / `Cancelled` |
| **Fig. 7** | User Account Lifecycle State Chart | UML 2.5 | `Pending` ➔ `Approved` ➔ `Active` / `Suspended` / `Rejected` |
| **Fig. 8** | Context Diagram — DFD Level 0 | Gane-Sarson | System Boundary & External Stakeholder Data Exchange |
| **Fig. 9** | Detailed Level-1 Data Flow Diagram | Gane-Sarson | 8 Decomposed Functional Sub-Processes |
| **Fig. 10** | NHIMS Entity Relationship Diagram (ERD) | Crow's Foot | 22 Normalized Entities & 30 Relational Foreign Keys |
| **Fig. 11** | NHIMS Domain Class Diagram | UML 2.5 | 22 Domain Entities with Attributes, Operations & Multiplicities |

---

## 8. Core Domain Subsystems & Portals

### 8.1 Public Healthcare Portal (`/`)
- **Hospital Directory & Branch Filtering:** Dynamic real-time lookup across nationwide hospital branches.
- **Doctor Specialist Directory (`public_doctors.php`):** Filter specialist physicians by clinical specialty, consultation fees, and hospital branch.
- **Live Resource Telemetry:** Real-time visibility into ward bed occupancy (General, ICU, CCU, VIP Cabin), Blood Bank bag quantities by ABO/Rh group, and emergency ambulance standby statuses with direct driver dispatch numbers.
- **Virtual Assistant Chatbot:** Interactive assistant capable of triaging general inquiries, guiding booking flows, and directing emergency requests.

### 8.2 Patient Portal (`/patient`)
- **Appointment Management:** Review physical and virtual appointments with real-time status tracking.
- **Digital Queue Token with QR Code (`print_token.php`):** Generates a dedicated printable outpatient token featuring serial numbers, doctor room details, and verifiable QR codes.
- **Electronic Prescription Viewer (`print_prescription.php`):** Renders prescriptions on official hospital letterheads with structured medicine regimens and advised investigations, exportable to vector PDF.
- **Laboratory Report Access (`print_lab_report.php`):** View, verify, and print completed clinical laboratory reports.
- **Patient Treatment Journey Tracker (`tracking.php`):** Visual timeline showing the end-to-end medical progression from initial consultation to final billing.

### 8.3 Doctor Clinical Workstation (`/doctor`)
- **Clinical Queue Management:** View today's appointments categorized by consultation status.
- **EHR & Patient History Review (`medical_records.php`):** Access previous diagnoses, past visits, and medical treatment chronologies.
- **Structured Prescription Authoring (`add_prescription.php`):** Digital prescription writing interface allowing dynamic dosage scheduling, dietary instructions, and investigative lab test orders with auto-patient population.

### 8.4 Receptionist & Front-Desk Subsystem (`/receptionist`)
- **Walk-in Patient Onboarding (`patients.php`):** Rapid registration and record lookup for outpatient visitors.
- **Appointment Triage & Approval Desk (`appointments.php`):** Accept, confirm, reschedule, or cancel pending patient bookings with real-time queue allocation.

### 8.5 Diagnostic Laboratory Suite (`/lab`)
- **Diagnostic Order Queue (`dashboard.php`):** Real-time queue of doctor-prescribed and patient-ordered investigations.
- **4-Stage Workflow Manager (`update_test.php`):** State transitions tracking: `Pending` ➔ `Sample Collected` ➔ `Processing` ➔ `Completed`.
- **Findings & Report Compiler:** Input qualitative and quantitative diagnostic readings with optional file attachment capabilities.

### 8.6 Pharmacy Inventory & Point of Sale (`/pharmacy`)
- **Real-Time Stock Inventory (`inventory.php`):** Drug catalog tracking unit prices, batch numbers, stock levels, and automatic low-stock alerts.
- **Interactive POS Terminal (`pos.php`):** Cash register interface with instant stock deduction upon transaction confirmation.

### 8.7 Central Administrator Control Plane (`/admin`)
- **Executive Analytics Dashboard (`analytics.php`):** Real-time financial telemetry powered by Chart.js (consultation revenue vs. pharmacy sales, 6-month financial trajectory).
- **Hospital Resource Configuration:** Manage Wards, Beds, Blood Bank donations, and Ambulance dispatch fleets.
- **HR & Payroll Processing (`hr.php`):** Automated salary computer applying formulas: `Net Salary = Base Salary + Bonus - Deductions`.
- **System Audit Logger (`audit_logs.php`):** Traceable log capturing `user_id`, `action`, `details`, `ip_address`, and ISO timestamps.

---

## 9. Relational Database Architecture (22 Entities, 30 Foreign Keys)

The persistence tier is fully normalized (3-NF) and implemented on the MySQL InnoDB storage engine:

| Entity Category | Database Tables | Functional Domain Scope |
| :--- | :--- | :--- |
| **Authentication** | `users` | RBAC credentials, approval states, role assignments |
| **Stakeholder Profiles** | `patients`, `doctors` | Clinical & demographic actor profiles |
| **Organization** | `hospitals`, `departments` | Branch infrastructure & department trees |
| **Consultations** | `appointments`, `prescriptions` | Bookings, queue tokens, JSON drug data |
| **Diagnostics** | `lab_services`, `laboratory_tests` | Test catalogs, 4-stage lab status pipeline |
| **Electronic Health** | `medical_records` | Historic diagnoses, visit treatments |
| **Financial Ledger** | `billing` | Invoices, multi-channel payment records |
| **Pharmacy Engine** | `pharmacy_inventory`, `pharmacy_sales` | Stock counts, batches, POS sales receipts |
| **Ward & Inpatient** | `wards`, `beds`, `admissions` | Inpatient admission, ward pricing, bed tracking |
| **Emergency Stocks** | `blood_bank`, `blood_donors` | ABO/Rh inventory counts, donor registries |
| **Transport Fleet** | `ambulances` | Emergency vehicles, driver contact routes |
| **Governance & Audit** | `payrolls`, `patient_feedback`, `system_logs` | Staff payroll calculation, ratings, immutable audit logging |

```sql
-- Schema Reference: Clinical Consultation & Prescription Engine
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_type` enum('Physical','Telemedicine') NOT NULL DEFAULT 'Physical',
  `status` enum('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `queue_token` int(11) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medicines` text DEFAULT NULL, -- Structured JSON: [{"name":"...","dosage":"..."}]
  `tests` text DEFAULT NULL,     -- Investigation requests
  `date_issued` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 10. End-to-End System Workflows

```
  [ PATIENT ]                      [ RECEPTIONIST ]                 [ DOCTOR ]                   [ LAB STAFF ]
       │                                  │                              │                             │
       │─── 1. Book Appointment ─────────>│                              │                             │
       │    (Physical / Telemed)          │                              │                             │
       │                                  │─── 2. Confirm Booking ──────>│                             │
       │<── 3. Issue Queue Token (QR) ────│                              │                             │
       │                                                                 │                             │
       │═══════════════════════════ 4. Clinical Consultation ═══════════>│                             │
       │                                                                 │─── 5. Order Lab Tests ─────>│
       │<── 6. View Prescription Pad (PDF) ──────────────────────────────│                             │
       │                                                                                               │
       │                                                                 │<── 7. Update Test Result ───│
       │<── 8. Download Completed Lab Report (Letterhead PDF) ─────────────────────────────────────────│
```

1. **Patient Booking:** The patient navigates to `book_online.php`, selects a hospital branch, specialist doctor, date, and consultation type (Physical / Telemedicine).
2. **Receptionist Confirmation:** Receptionist inspects incoming requests at `receptionist/appointments.php` and changes status from `Pending` to `Confirmed`.
3. **Queue Token Issuance:** Patient accesses `patient/print_token.php` to generate an official hospital Queue Ticket containing serial numbers and a verification QR code.
4. **Clinical Consultation:** Doctor reviews previous EHR entries and initiates the examination via `doctor/appointments.php`.
5. **Electronic Prescribing:** Doctor authors medications and lab requests via `doctor/add_prescription.php`. Medications serialize into JSON; lab orders register for diagnostic fulfillment.
6. **Diagnostic Fulfillment:** Lab technician reviews tests in `lab/dashboard.php`, updates progress through the 4-stage pipeline, inputs findings, and marks status as `Completed`.
7. **Patient Delivery:** Patient logs into `patient/dashboard.php`, downloading both the branded **Prescription Pad PDF** and **Diagnostic Laboratory Report** directly from their browser.

---

## 11. Verification, Quality Assurance & Test Case Matrix

The platform was verified through structured unit, integration, and security test suites:

| Test ID | Subsystem | Test Scenario | Execution Vector | Expected Behavior | Verification Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | Authentication | Valid Login | Valid username & bcrypt password | Redirect to role-specific dashboard | `PASSED` |
| **TC-02** | Authentication | Invalid Password | Valid username with corrupted password | Display error; reject session generation | `PASSED` |
| **TC-03** | Registration | Patient Auto-Provision | Complete registration as Patient | Account set to `Approved`; direct login | `PASSED` |
| **TC-04** | Registration | Staff Onboarding | Complete registration as Doctor/Lab | Account set to `Pending`; awaits Admin approval | `PASSED` |
| **TC-05** | Booking Engine | Online Booking | Valid doctor, branch, and future date | Appointment created; Token & Invoice linked | `PASSED` |
| **TC-06** | Clinical Station | Record Storage | Insert clinical history & diagnosis | Stored in `medical_records`; linked to patient | `PASSED` |
| **TC-07** | Clinical Station | JSON Prescribing | Author multi-drug regimen & lab tests | Serializes valid JSON array into DB | `PASSED` |
| **TC-08** | Diagnostic Lab | 4-Stage State Pipeline | Cycle status to `Completed` with findings | Findings updated; PDF unlocked for patient | `PASSED` |
| **TC-09** | Pharmacy POS | In-Stock Sale | Process medicine sale with quantity `n` | Invoice generated; stock deducted by `n` | `PASSED` |
| **TC-10** | Pharmacy POS | Over-Stock Sale | Request quantity exceeding current stock | Transaction rejected; alert displayed | `PASSED` |
| **TC-11** | Ward Operations | Inpatient Admission | Assign patient to available bed | Admission created; bed state set to `Occupied` | `PASSED` |
| **TC-12** | Security / RBAC | Privilege Escalation | Patient attempts access to `/admin` URL | Session guard intercepts; redirects to login | `PASSED` |

---

## 12. Repository Directory Structure

```
National-Hospital-Management-System/
├── admin/                      # Central Administrator Control Plane
│   ├── includes/               # Admin Sidebar, Navbar & Header partials
│   ├── add_doctor.php          # Staff Provisioning Interface
│   ├── analytics.php           # Chart.js Financial & Operational Telemetry
│   ├── appointments.php        # Master Appointment Management
│   ├── audit_logs.php          # Immutable Security & Activity Log Viewer
│   ├── billing.php             # Master Invoicing & Revenue Ledger
│   ├── blood_bank.php          # Blood Inventory & Donor Management
│   ├── dashboard.php           # Executive Administrative KPI Overview
│   ├── hr.php                  # Automated Payroll & Compensation Engine
│   └── wards.php               # Ward, Bed & Inpatient Admission Subsystem
├── assets/                     # Static Assets & Design Libraries
│   ├── css/                    # Custom CSS & Glassmorphism Rules
│   ├── js/                     # Client Utilities & DOM Handlers
│   └── images/                 # Hospital Graphics & Brand Assets
├── config/                     # Core Configuration Layer
│   └── db.php                  # PDO Database Persistence Gateway
├── database/                   # Relational Schema & Seeding Engines
│   └── nhms_live_update.sql    # Complete Normalized Database Schema Dump
├── doctor/                     # Clinical Physician Workstation
│   ├── add_prescription.php    # JSON Structured Prescription Authoring
│   ├── appointments.php        # Daily Consultation Queue
│   ├── dashboard.php           # Doctor Workstation KPI Overview
│   ├── medical_records.php     # Longitudinal EHR History Engine
│   └── prescriptions.php       # Issued Prescription Archive
├── includes/                   # Shared Public & Application Layout Components
│   ├── header_public.php       # Navigation Bar & Tailwind Initialization
│   └── footer_public.php       # Global Footer, Script Drivers & Chatbot UI
├── lab/                        # Diagnostic Laboratory Suite
│   ├── dashboard.php           # Diagnostic Test Work Order Queue
│   └── update_test.php         # 4-Stage Status State Machine & Result Compiler
├── patient/                    # Patient Self-Service Portal
│   ├── appointments.php        # Outpatient Appointment Registry
│   ├── billing.php             # Patient Invoices & Payment Gateway View
│   ├── dashboard.php           # Patient EHR Hub & Quick Actions
│   ├── feedback.php            # 5-Star Clinical Experience Review System
│   ├── lab_tests.php           # Diagnostic Investigation Results
│   ├── prescriptions.php       # Prescription Records
│   ├── print_lab_report.php    # Printable Diagnostic Letterhead PDF View
│   ├── print_prescription.php  # Printable Hospital Pad PDF View
│   ├── print_token.php         # Printable Outpatient Queue Token with QR
│   ├── records.php             # Medical History & Diagnosis Archive
│   └── tracking.php            # Visual Patient Journey Timeline
├── pharmacy/                   # Pharmacy Inventory & Point of Sale
│   ├── dashboard.php           # Pharmacy Operations Overview
│   ├── inventory.php           # Pharmaceutical Stock & Expiry Manager
│   └── pos.php                 # Interactive POS Terminal & Cash Register
├── receptionist/               # Front-Desk Outpatient Triage
│   ├── appointments.php        # Queue Triage, Confirmation & Scheduling Desk
│   ├── dashboard.php           # Reception Overview & Queue Metrics
│   └── patients.php            # Walk-in Patient Registration Engine
├── book_lab.php                # Direct Public Diagnostic Booking Gateway
├── book_online.php             # Public Outpatient Appointment Booking Engine
├── expert_doctors.php          # Specialist Physician Public Directory
├── hospitals.php               # Multi-Branch Hospital Network Explorer
├── index.php                   # Public Homepage & Live Telemetry Engine
├── infrastructure.php          # Hospital Facilities & Technology Overview
├── login.php                   # Unified Secure Multi-Role Authentication Portal
├── login_action.php            # Session Creation & RBAC Routing Controller
├── logout.php                  # Session Termination Handler
├── public_doctors.php          # Doctor Search & Filtering Interface
├── public_lab.php              # Diagnostic Test Catalog Explorer
├── register.php                # Multi-Role Registration Engine
└── README.md                   # Comprehensive Engineering Specification
```

---

## 13. Local Engineering & Installation Guide

### 13.1 Prerequisites
- **Web Server:** Apache (via XAMPP / LAMP / WAMP stack)
- **PHP Version:** PHP `8.0` or higher (with `pdo_mysql` and `json` extensions enabled)
- **Database Server:** MySQL Server `8.0+` or MariaDB `10.4+`
- **Web Browser:** Modern browser with ES6 & CSS Grid support (Chrome, Firefox, Safari, Edge)

### 13.2 Installation Commands

```bash
# 1. Navigate to your local Apache web root directory
cd /Applications/XAMPP/xamppfiles/htdocs/

# 2. Clone the repository
git clone https://github.com/walid-abdullah/National-Hospital-Management-System.git nhms

# 3. Enter the project directory
cd nhms
```

### 13.3 Database Initialization
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open `http://localhost/phpmyadmin/` in your browser.
3. Create a new database named `nhms` with collation `utf8mb4_general_ci`.
4. Import the SQL file located at:
   ```
   nhms/database/nhms_live_update.sql
   ```
5. Configure `config/db.php` with your local database credentials:

```php
<?php
$host     = "localhost";
$dbname   = "nhms";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failure: " . $e->getMessage());
}
?>
```

---

## 14. Live Production Deployment & Demonstration Credentials

The production deployment of NHIMS is accessible online:

**Live Production URL:** [https://nhms.site.je/](https://nhms.site.je/)

### Demonstration Credentials Matrix

| Role / Portal | Username / ID | Password | Target Entry URI | Operational Scope |
| :--- | :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `123456` | `/login.php` ➔ `/admin` | Executive analytics, HR payroll, wards, blood bank, audits |
| **Receptionist** | `01744444444` | `123456` | `/login.php` ➔ `/receptionist` | Patient registration, appointment triage & approval |
| **Doctor** | `01711111111` | `123456` | `/login.php` ➔ `/doctor` | Consultation queue, patient EHR, prescription authoring |
| **Patient** | `01722222222` | `123456` | `/login.php` ➔ `/patient` | Booking, Queue Token QR view, PDF Prescription download |
| **Laboratory Staff** | `01733333333` | `123456` | `/login.php` ➔ `/lab` | 4-stage diagnostic pipelining, lab report generation |
| **Pharmacist** | `017888888888` | `123456` | `/login.php` ➔ `/pharmacy` | Drug inventory, Point-of-Sale cart & stock reduction |

---

## 15. Security, Integrity & Audit Protocols

- **Role-Based Access Control (RBAC):** Strict session validation gates access to protected subdirectories (`/admin`, `/doctor`, `/patient`, `/receptionist`, `/lab`, `/pharmacy`). Unauthorized requests are intercepted and redirected.
- **SQL Injection Defense:** All database transactions execute exclusively through **PDO Prepared Statements** with explicit parameter binding.
- **Password Cryptography:** User passwords are encrypted using PHP's native `password_hash()` implementing the **Bcrypt** cryptographic algorithm.
- **Cross-Site Scripting (XSS) Mitigation:** All dynamic database outputs rendered to HTML are escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **System Audit Logging:** Administrative events, sensitive clinical modifications, and staff actions are recorded in the `system_logs` table with network IP signatures and timestamps.

---

## 16. Project Team & Academic Attribution

```
========================================================================================
                          WORLD UNIVERSITY OF BANGLADESH
                     Department of Computer Science & Engineering
========================================================================================
 Course Title : Software Engineering Lab
 Course Code  : CSE 06133248 / CSE 06133249
 Academic Year: 3rd Year, 8th Semester (Batch 66/A)
 Supervisor   : Dr. Md. Amran Hossen, Assistant Professor, Dept. of CSE
 Submission   : August 2026
========================================================================================
```

### Engineering Team & Individual Contributions

| Member Name | Student ID | Primary Role | Detailed Scope of Contribution |
| :--- | :---: | :--- | :--- |
| **Md. Abdullah Walid** | **4006** | **Lead Full-Stack Developer & Software Architect** | Complete architectural design, backend PHP development across all 6 portals, database schema design (22 tables / 30 FKs), clinical prescription engine, QR queue token generator, security hardening, and live cloud deployment. |
| **Sakira Sheherin Chhowa** | **4007** | **UI/UX & Presentation Designer** | Visual design system, Tailwind CSS component layouts, slide deck organization, content presentation, and interface styling. |
| **Asmaul Husna** | **3993** | **Documentation & Requirements Analyst** | Software Requirements Specification (SRS v2.0), comprehensive UML diagram modeling (Use Case, Activity, Sequence, State, DFD, Class, ERD), and formal report documentation. |
| **Md. Hasibuzzaman Mahi** | **4010** | **Quality Assurance & Verification Engineer** | Test case design and execution (TC-01 through TC-12), cross-module integration verification, defect tracking, and submission QA. |

---

## 17. License & Intellectual Property

```
Copyright (c) 2026 Md. Abdullah Walid & World University of Bangladesh (WUB).
All rights reserved.

This software, database schema, and architectural specification were developed as an 
academic Software Engineering capstone project. Unauthorized commercial distribution, 
plagiarism, or uncredited reproduction is strictly prohibited.
```

<div align="center">
  <sub>Architected with Enterprise Standards by <strong><a href="https://github.com/walid-abdullah">Md. Abdullah Walid</a></strong> (ID: 4006) • Dept. of CSE, World University of Bangladesh</sub>
</div>
