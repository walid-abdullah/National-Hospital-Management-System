<div align="center">

  <h1>🏥 NHIMS — National Health Information Management System</h1>
  <p><strong>Enterprise Multi-Branch Clinical Operations, Centralized EHR & Distributed Healthcare Management Platform</strong></p>
  <p>Software Engineering Capstone Project • <strong>World University of Bangladesh (WUB)</strong></p>
  <p>Architected & Developed by <a href="https://github.com/walid-abdullah"><strong>Md. Abdullah Walid</strong></a> (Lead Full-Stack Engineer)</p>

  <p>
    <a href="https://nhms.site.je/"><img src="https://img.shields.io/badge/Production_Live-nhms.site.je-2563EB?style=for-the-badge&logo=google-chrome&logoColor=white" alt="Live URL" /></a>
    <img src="https://img.shields.io/badge/Backend-PHP_8.x_%E2%80%A2_PDO-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.x" />
    <img src="https://img.shields.io/badge/Database-MySQL_8.0_%E2%80%A2_InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0" />
    <img src="https://img.shields.io/badge/Frontend-TailwindCSS_v3_%E2%80%A2_JS_ES6-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS" />
    <img src="https://img.shields.io/badge/Analytics-Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js" />
    <img src="https://img.shields.io/badge/Security-RBAC_%E2%80%A2_Bcrypt_%E2%80%A2_Prepared_Statements-059669?style=for-the-badge&logo=shield&logoColor=white" alt="Security" />
  </p>

</div>

---

## 📑 Table of Contents
1. [Executive Summary & Problem Domain](#1-executive-summary--problem-domain)
2. [Software Architecture & System Design](#2-software-architecture--system-design)
3. [Comprehensive SDLC & Engineering Procedures](#3-comprehensive-sdlc--engineering-procedures)
4. [Technical Specifications & Stack Matrix](#4-technical-specifications--stack-matrix)
5. [System Modeling & UML Specification Index](#5-system-modeling--uml-specification-index)
6. [Core Domain Subsystems & Role-Based Portals](#6-core-domain-subsystems--role-based-portals)
7. [Relational Database Architecture (22 Entities, 30 Foreign Keys)](#7-relational-database-architecture-22-entities-30-foreign-keys)
8. [End-to-End System Workflows](#8-end-to-end-system-workflows)
9. [Verification, Quality Assurance & Test Case Matrix](#9-verification-quality-assurance--test-case-matrix)
10. [Repository Directory Structure](#10-repository-directory-structure)
11. [Local Engineering & Deployment Guide](#11-local-engineering--deployment-guide)
12. [Live Production Deployment & Role Credentials](#12-live-production-deployment--role-credentials)
13. [Security, Integrity & Audit Protocols](#13-security-integrity--audit-protocols)
14. [Project Team & Academic Attribution](#14-project-team--academic-attribution)
15. [License & Intellectual Property](#15-license--intellectual-property)

---

## 1. Executive Summary & Problem Domain

### 1.1 Academic & Engineering Context
The **National Health Information Management System (NHIMS)** is a multi-tier, centralized web-based healthcare ecosystem engineered as the final laboratory capstone project for **Software Engineering Lab (Course Code: CSE 06133248 / CSE 06133249)** in the Department of Computer Science and Engineering at the **World University of Bangladesh (WUB)**, under the academic supervision of **Dr. Md. Amran Hossen**, Assistant Professor, Dept. of CSE.

### 1.2 The Healthcare Fragmentation Bottleneck
Traditional hospital infrastructures in developing countries suffer from severe structural and operational inefficiencies:
- **Fragmented Electronic Health Records (EHR):** Clinical histories are distributed across disconnected silos; patient records cannot be seamlessly accessed across facilities.
- **Paper-Based Vulnerabilities:** Paper prescriptions and physical diagnostic sheets suffer from loss, illegibility, forgery, and data duplication.
- **Scheduling Conflicts & Blind Queues:** Manual appointment scheduling creates long physical waiting lines with zero real-time queue visibility.
- **Delayed Diagnostic Pipelines:** Laboratory reports take days to manually reach consulting physicians and patients.
- **Blind Resource Allocation:** Emergency responders and triage desks lack real-time visibility into ICU/CCU bed availability, blood bank stocks, and active ambulance fleets.
- **Disjointed Pharmacy Inventory:** Stockouts and expired pharmaceuticals occur due to lack of automated inventory deduction upon point-of-sale checkout.

### 1.3 The NHIMS Architectural Solution
NHIMS provides a unified, three-tier, multi-tenant capable architecture spanning **6 distinct role-based sub-systems**, **22 relational database entities**, and **30 foreign key constraints**. It eliminates friction by integrating real-time appointment booking, automated QR queue-token synthesis, structured JSON electronic prescription authoring, hospital-letterhead PDF generation, 4-stage laboratory diagnostic pipelining, real-time inventory tracking, and full administrative audit logging into one single cloud-accessible platform.

---

## 2. Software Architecture & System Design

NHIMS is engineered following a clean **Three-Tier Client-Server Architecture** designed for high throughput, session isolation, and strict role-based access control (RBAC).

```
+----------------------------------------------------------------------------------------------------+
|                                      CLIENT / PRESENTATION LAYER                                    |
|  - Modern Responsive Web UI (HTML5, Tailwind CSS v3, Glassmorphism, Dark/Light Mode Engine)       |
|  - Asynchronous Client Utilities (Vanilla JavaScript ES6, Dynamic Fetch, Modal Dialogs)           |
|  - Data Visualizations & Real-Time Telemetry (Chart.js Line, Bar & Doughnut Visualizers)          |
+-------------------------------------------------+--------------------------------------------------+
                                                  |
                                                  | HTTPS Requests / Session Cookies
                                                  v
+----------------------------------------------------------------------------------------------------+
|                                    APPLICATION / BUSINESS LOGIC LAYER                              |
|  - Modular Role Dispatchers (/admin, /doctor, /patient, /receptionist, /lab, /pharmacy)           |
|  - Session Authentication & RBAC Enforcer (Bcrypt verification, Privilege validation)             |
|  - Clinical Engines:                                                                               |
|      * Digital Prescription Builder (Structured JSON Serialization Engine)                         |
|      * Automated Dynamic Queue Token & QR Synthesizer Engine                                       |
|      * 4-Stage Laboratory Diagnostic Workflow State Machine                                        |
|      * Pharmacy Point of Sale (POS) Atomic Transaction & Stock Deductor                            |
|      * Automated HR Payroll Computer (Net = Base + Bonus - Deductions)                             |
|  - Secure Persistence Gateway (PHP Data Objects - PDO with Prepared Statements)                    |
+-------------------------------------------------+--------------------------------------------------+
                                                  |
                                                  | Native PDO Protocol (Prepared SQL)
                                                  v
+----------------------------------------------------------------------------------------------------+
|                                      PERSISTENCE / DATA STORAGE LAYER                              |
|  - Relational Database Engine (MySQL 8.0+ / MariaDB InnoDB with ACID Transactional Integrity)      |
|  - 22 Normalized Tables with 30 Indexed Foreign Key Cascades & Strict Referential Rules            |
|  - Server File Storage (/uploads/documents/ for Secure Medical & Identity Attachments)             |
+----------------------------------------------------------------------------------------------------+
```

### Component Breakdown
```
                                        +-------------------+
                                        |   Web Browser     |
                                        +---------+---------+
                                                  |
                                        +---------v---------+
                                        |   Public Entry    |
                                        |   (index.php)     |
                                        +----+----+----+----+
                                             |    |    |
                   +-------------------------+    |    +-------------------------+
                   |                              |                              |
         +---------v---------+          +---------v---------+          +---------v---------+
         |  Authentication   |          |  Resource Tracker |          |  Online Booking   |
         | (login_action.php)|          | (Live Beds/Blood) |          | (book_online.php) |
         +---------+---------+          +-------------------+          +---------+---------+
                   |                                                             |
                   +---------------------+---------------+                       |
                                         |               |                       |
        +--------------------------------v-------+ +-----v-----------------------v----+
        |   Protected Role Portals (RBAC Guard)  | |  Transaction / Booking Engine    |
        | - Admin Panel (/admin)                 | |  - Creates Pending Appointment   |
        | - Doctor Station (/doctor)             | |  - Generates Queue Token & QR    |
        | - Patient Portal (/patient)            | |  - Generates Initial Invoice     |
        | - Receptionist Desk (/receptionist)    | +------------------+---------------+
        | - Laboratory Suite (/lab)              |                    |
        | - Pharmacy POS (/pharmacy)             |                    |
        +----------------+-----------------------+                    |
                         |                                            |
                         +-------------------+------------------------+
                                             |
                                   +---------v---------+
                                   |   MySQL Database  |
                                   |     (22 Tables)   |
                                   +-------------------+
```

---

## 3. Comprehensive SDLC & Engineering Procedures

The project was engineered using the **Incremental Development Model (Agile-Oriented)** across five systematic software engineering phases:

```
  Phase 1: Requirements Engineering & Domain Analysis
    ├── Elicit multi-stakeholder clinical and administrative requirements
    ├── Formulate IEEE Std 830-1998 compliant Software Requirements Specification (SRS v2.0)
    └── Define 72 distinct Functional Requirements (FR-PUB, FR-AUTH, FR-ADM, FR-DOC, FR-PAT, FR-REC, FR-LAB, FR-PHA)
         │
         ▼
  Phase 2: Architectural Modeling & System Design
    ├── Formalize 11 comprehensive UML & Structural Diagrams (Use Case, Activity, Sequence, State, DFD, ERD, Class)
    ├── Design 3-NF normalized relational database schema (22 tables, 30 foreign keys)
    └── Formulate Glassmorphic visual design system using Tailwind CSS design tokens
         │
         ▼
  Phase 3: Implementation & Subsystem Engineering
    ├── Construct secure authentication gateway with role-based session isolation
    ├── Implement clinical prescription engine with JSON structured multi-drug serialization
    ├── Develop printable hospital-pad styling with pure CSS `@media print` drivers
    ├── Program dynamic QR code vector generation for physical outpatient queue tokens
    └── Build Pharmacy POS with automatic stock validation and instant inventory decrementation
         │
         ▼
  Phase 4: Verification, Quality Assurance & Security Hardening
    ├── Execute 12 core formal test cases (TC-01 through TC-12) covering Auth, Booking, Clinical, Lab & POS
    ├── Perform SQL injection fuzzing and enforce PDO parameterization across all endpoints
    ├── Audit data-type mismatches (`id` vs legacy `patient_id`) across cross-module JOIN queries
    └── Test multi-viewport UI responsiveness on Desktop, Tablet, and Mobile browsers
         │
         ▼
  Phase 5: Cloud Deployment & Live Maintenance
    ├── Configure production web-server environment on cloud infrastructure
    ├── Migrate schema and load deterministic multi-role demonstration seed datasets
    └── Verify public SSL/TLS accessibility and real-time end-to-end user workflows
```

---

## 4. Technical Specifications & Stack Matrix

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

## 5. System Modeling & UML Specification Index

The system's structural, behavioral, and data layers are modeled through **11 formal UML and engineering diagrams**:

```
+--------------------------------------------------------------------------------------------------------+
|                                    NHIMS FORMAL DIAGRAM SUITE                                          |
+----+-----------------------------------------------+-----------+---------------------------------------+
| No | Diagram Title                                 | Standard  | Core Domain Scope                     |
+----+-----------------------------------------------+-----------+---------------------------------------+
| 01 | Component Diagram — System Architecture       | UML 2.5   | 3-Tier Layering & Subsystem Isolation |
| 02 | NHIMS Use Case Diagram                        | UML 2.5   | 6 Actors, 32 Core System Use Cases    |
| 03 | Registration & Approval Activity Diagram      | UML 2.5   | Dual-Track User Provisioning Workflow |
| 04 | Laboratory Test Processing Activity Diagram   | UML 2.5   | 4-Stage Diagnostic Execution Pipeline |
| 05 | Online Appointment Booking Sequence Diagram   | UML 2.5   | Message-Passing Sequence (Client->DB) |
| 06 | Appointment Lifecycle State Chart             | UML 2.5   | Pending -> Confirmed -> Completed     |
| 07 | User Account Lifecycle State Chart            | UML 2.5   | Pending -> Approved -> Active/Blocked |
| 08 | Context Diagram — DFD Level 0                 | Gane-Sars | High-Level System Boundary & Entities |
| 09 | Detailed Level-1 Data Flow Diagram            | Gane-Sars | 8 Functional Decomposed Processes     |
| 10 | NHIMS Entity Relationship Diagram (ERD)       | Chen/Crow | 22 Entities, 30 Foreign Key Relations |
| 11 | NHIMS Class Diagram                           | UML 2.5   | 22 Domain Classes with Attributes/Ops |
+----+-----------------------------------------------+-----------+---------------------------------------+
```

---

## 6. Core Domain Subsystems & Role-Based Portals

```
                                  +----------------------------+
                                  |    NHIMS Central Gateway   |
                                  +--------------+-------------+
                                                 |
         +---------------+---------------+-------+-------+---------------+---------------+
         |               |               |               |               |               |
         v               v               v               v               v               v
  +--------------+ +-----------+ +---------------+ +-----------+ +---------------+ +-----------+
  |    Public    | |  Patient  | | Receptionist  | |  Doctor   | |  Laboratory   | | Pharmacy  |
  |    Portal    | |  Portal   | |     Desk      | |  Station  | |     Suite     | |    POS    |
  +--------------+ +-----------+ +---------------+ +-----------+ +---------------+ +-----------+
```

### 6.1 Public Healthcare Portal (`/`)
- **Hospital Directory & Branch Filtering:** Dynamic real-time lookup across nationwide hospital locations and departments.
- **Doctor Specialist Finder (`public_doctors.php`):** Filter specialist physicians by clinical specialty, consultation fees, and hospital branch.
- **Live Resource Telemetry:** Real-time visibility into ward bed occupancy (General, ICU, CCU, VIP Cabin), Blood Bank bag quantities by ABO/Rh group, and emergency ambulance standby statuses with direct driver dispatch numbers.
- **Rule-Based Virtual Assistant Chatbot:** Floating interactive client assistant capable of triaging inquiries, guiding booking flows, and directing emergency requests.

### 6.2 Patient Portal (`/patient`)
- **Appointment Management & Telemedicine:** Review physical and virtual appointments with status tracking.
- **Digital Queue Token with QR Code (`print_token.php`):** Generates a dedicated printable outpatient token featuring serial numbers, doctor room details, and verifiable QR codes.
- **Electronic Prescription Viewer (`print_prescription.php`):** Renders prescriptions on official hospital letterheads with structured medicine regimens and advised investigations, exportable to vector PDF.
- **Laboratory Report Access (`print_lab_report.php`):** View, verify, and print completed clinical laboratory reports.
- **Patient Treatment Journey Tracker (`tracking.php`):** Visual timeline showing the end-to-end medical progression from initial consultation to final billing.

### 6.3 Doctor Clinical Workstation (`/doctor`)
- **Clinical Queue Management:** View today's appointments categorized by consultation status.
- **EHR & Patient History Review (`medical_records.php`):** Access previous diagnoses, past visits, and medical treatment chronologies.
- **Structured Prescription Authoring (`add_prescription.php`):** Digital prescription writing interface allowing dynamic dosage scheduling, dietary instructions, and investigative lab test orders with auto-patient population.

### 6.4 Receptionist & Front-Desk Subsystem (`/receptionist`)
- **Walk-in Patient Onboarding (`patients.php`):** Rapid registration and record lookup for outpatient visitors.
- **Appointment Triage & Approval Desk (`appointments.php`):** Accept, confirm, reschedule, or cancel pending patient bookings with real-time queue allocation.

### 6.5 Diagnostic Laboratory Suite (`/lab`)
- **Diagnostic Order Queue (`dashboard.php`):** Real-time queue of doctor-prescribed and patient-ordered investigations.
- **4-Stage Workflow Manager (`update_test.php`):** State transitions tracking: `Pending` ➔ `Sample Collected` ➔ `Processing` ➔ `Completed`.
- **Findings & Report Compiler:** Input qualitative and quantitative diagnostic readings with optional file attachment capabilities.

### 6.6 Pharmacy Inventory & Point of Sale (`/pharmacy`)
- **Real-Time Stock Inventory (`inventory.php`):** Drug catalog tracking unit prices, batch numbers, stock levels, and automatic low-stock alerts.
- **Interactive POS Terminal (`pos.php`):** Fast cash register interface with instant stock deduction upon transaction confirmation.

### 6.7 Central Administrator Control Plane (`/admin`)
- **Executive Analytics Dashboard (`analytics.php`):** Real-time financial telemetry powered by Chart.js (consultation revenue vs. pharmacy sales, 6-month financial trajectory).
- **Hospital Resource Configuration:** Manage Wards, Beds, Blood Bank donations, and Ambulance dispatch fleets.
- **HR & Payroll Processing (`hr.php`):** Automated salary computer applying formulas: `Net Salary = Base Salary + Bonus - Deductions`.
- **System Audit Logger (`audit_logs.php`):** Traceable log capturing `user_id`, `action`, `details`, `ip_address`, and ISO timestamps.

---

## 7. Relational Database Architecture (22 Entities, 30 Foreign Keys)

The persistence tier is fully normalized (3-NF) and implemented on the MySQL InnoDB storage engine:

```
+---------------------------------------------------------------------------------------------------+
|                                     NHIMS DATABASE ENTITY MAP                                     |
+-------------------+-----------------------------------+-------------------------------------------+
| Entity Category   | Database Tables                   | Primary Scope                             |
+-------------------+-----------------------------------+-------------------------------------------+
| Authentication    | users                             | RBAC credentials, approval states, roles  |
| Stakeholder Profiles| patients, doctors               | Clinical & demographic actor profiles     |
| Organization      | hospitals, departments            | Branch infrastructure & department trees  |
| Consultations     | appointments, prescriptions       | Bookings, queue tokens, JSON drug data    |
| Diagnostics       | lab_services, laboratory_tests    | Test catalogs, 4-stage lab status pipeline|
| Electronic Health | medical_records                   | Historic diagnoses, visit treatments      |
| Financial Ledger  | billing                           | Invoices, multi-channel payment records   |
| Pharmacy Engine   | pharmacy_inventory, pharmacy_sales| Stock counts, batches, POS sales receipts |
| Ward & Inpatient  | wards, beds, admissions           | Inpatient admission, ward pricing, beds   |
| Emergency Stocks  | blood_bank, blood_donors          | ABO/Rh inventory counts, donor registries |
| Transport Fleet   | ambulances                        | Emergency vehicles, driver contact routes |
| Governance & Audit| payrolls, patient_feedback,       | Staff payroll calculation, 5-star ratings,|
|                   | system_logs                       | immutable activity logging & security logs|
+-------------------+-----------------------------------+-------------------------------------------+
```

```sql
-- Schema Highlight: Clinical Consultation & Prescription Engine
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
  `medicines` text DEFAULT NULL, -- Structured JSON Array: [{"name":"...","dosage":"..."}]
  `tests` text DEFAULT NULL,     -- Comma-separated or newline-delimited lab orders
  `date_issued` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 8. End-to-End System Workflows

```
  [ PATIENT ]                      [ RECEPTIONIST ]                 [ DOCTOR ]                   [ LAB STAFF ]
       |                                  |                              |                             |
       |--- 1. Book Appointment --------->|                              |                             |
       |    (Physical / Telemed)          |                              |                             |
       |                                  |--- 2. Confirm Booking ------>|                             |
       |<-- 3. Issue Queue Token (QR) ----|                              |                             |
       |                                                                 |                             |
       |=========================== 4. Clinical Consultation ===========>|                             |
       |                                                                 |--- 5. Order Lab Tests ----->|
       |<-- 6. View Prescription Pad (PDF) <-----------------------------|                             |
       |                                                                                               |
       |                                                                 |<-- 7. Update Test Result ---|
       |<-- 8. Download Completed Lab Report (Letterhead PDF) <----------------------------------------|
```

1. **Patient Booking:** The patient navigates to `book_online.php`, selects a hospital branch, specialist doctor, date, and consultation type (Physical / Telemedicine).
2. **Receptionist Confirmation:** Receptionist inspects incoming requests at `receptionist/appointments.php` and changes status from `Pending` to `Confirmed`.
3. **Queue Token Issuance:** Patient accesses `patient/print_token.php` to generate an official hospital Queue Ticket containing serial numbers and a verification QR code.
4. **Clinical Consultation:** Doctor reviews previous EHR entries and initiates the examination via `doctor/appointments.php`.
5. **Electronic Prescribing:** Doctor authors medications and lab requests via `doctor/add_prescription.php`. Medications serialize into JSON; lab orders register for diagnostic fulfillment.
6. **Diagnostic Fulfillment:** Lab technician reviews tests in `lab/dashboard.php`, updates progress through the 4-stage pipeline, inputs findings, and marks status as `Completed`.
7. **Patient Delivery:** Patient logs into `patient/dashboard.php`, downloading both the branded **Prescription Pad PDF** and **Diagnostic Laboratory Report** directly from their browser.

---

## 9. Verification, Quality Assurance & Test Case Matrix

The platform was verified through structured unit, integration, and security test suites:

| Test ID | Subsystem | Test Scenario | Execution Vector | Expected Behavior | Verification Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | Authentication | Valid Login | Valid username & bcrypt password | Redirect to role-specific dashboard | ✅ **PASSED** |
| **TC-02** | Authentication | Invalid Password | Valid username with corrupted password | Display error; reject session generation | ✅ **PASSED** |
| **TC-03** | Registration | Patient Auto-Provision | Complete registration as Patient | Account set to `Approved`; direct login | ✅ **PASSED** |
| **TC-04** | Registration | Staff Onboarding | Complete registration as Doctor/Lab | Account set to `Pending`; awaits Admin approval | ✅ **PASSED** |
| **TC-05** | Booking Engine | Online Booking | Valid doctor, branch, and future date | Appointment created; Token & Invoice linked | ✅ **PASSED** |
| **TC-06** | Clinical Station | Record Storage | Insert clinical history & diagnosis | Stored in `medical_records`; linked to patient | ✅ **PASSED** |
| **TC-07** | Clinical Station | JSON Prescribing | Author multi-drug regimen & lab tests | Serializes valid JSON array into DB | ✅ **PASSED** |
| **TC-08** | Diagnostic Lab | 4-Stage State Pipeline | Cycle status to `Completed` with findings | Findings updated; PDF unlocked for patient | ✅ **PASSED** |
| **TC-09** | Pharmacy POS | In-Stock Sale | Process medicine sale with quantity `n` | Invoice generated; stock deducted by `n` | ✅ **PASSED** |
| **TC-10** | Pharmacy POS | Over-Stock Sale | Request quantity exceeding current stock | Transaction rejected; alert displayed | ✅ **PASSED** |
| **TC-11** | Ward Operations | Inpatient Admission | Assign patient to available bed | Admission created; bed state set to `Occupied` | ✅ **PASSED** |
| **TC-12** | Security / RBAC | Privilege Escalation | Patient attempts access to `/admin` URL | Session guard intercepts; redirects to login | ✅ **PASSED** |

---

## 10. Repository Directory Structure

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

## 11. Local Engineering & Deployment Guide

### 11.1 Prerequisites
- **Web Server:** Apache (via XAMPP / LAMP / WAMP stack)
- **PHP Version:** PHP `8.0` or higher (with `pdo_mysql` and `json` extensions enabled)
- **Database Server:** MySQL Server `8.0+` or MariaDB `10.4+`
- **Web Browser:** Modern browser with ES6 & CSS Grid support (Chrome, Firefox, Safari, Edge)

### 11.2 Installation Procedure

```bash
# 1. Navigate to your local Apache web root directory
cd /Applications/XAMPP/xamppfiles/htdocs/

# 2. Clone the production repository
git clone https://github.com/walid-abdullah/National-Hospital-Management-System.git nhms

# 3. Enter the project directory
cd nhms
```

### 11.3 Database Provisioning
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open your browser and navigate to `http://localhost/phpmyadmin/`.
3. Create a new database named `nhms` with collation `utf8mb4_general_ci`.
4. Click **Import** and select the database schema file located at:
   ```
   nhms/database/nhms_live_update.sql
   ```
5. Execute the import to populate all 22 tables and demonstration seed datasets.

### 11.4 Environment Configuration
Edit `config/db.php` to match your local database credentials:

```php
<?php
$host     = "localhost";
$dbname   = "nhms";
$username = "root";
$password = ""; // Default is empty in local XAMPP

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failure: " . $e->getMessage());
}
?>
```

### 11.5 Access the Local Application
Open your browser and navigate to:
```
http://localhost/nhms
```

---

## 12. Live Production Deployment & Role Credentials

The production build of NHIMS is deployed and live on cloud hosting:

🌐 **Live Production URL:** [https://nhms.site.je/](https://nhms.site.je/)

### 🔐 Master Demonstration Credentials

| Role / Portal | Username / ID | Password | Portal Entry Point | Operational Responsibility |
| :--- | :--- | :--- | :--- | :--- |
| 👨‍💼 **Administrator** | `admin` | `123456` | `/login.php` ➔ `/admin` | System governance, analytics, HR payroll, wards & audit |
| 👩‍💼 **Receptionist** | `01744444444` | `123456` | `/login.php` ➔ `/receptionist` | Patient check-in, appointment queue triage & approval |
| 👨‍⚕️ **Doctor** | `01711111111` | `123456` | `/login.php` ➔ `/doctor` | Consultation queue, patient EHR, prescription authoring |
| 🤒 **Patient** | `01722222222` | `123456` | `/login.php` ➔ `/patient` | Appointment booking, QR Queue Token, PDF Prescription download |
| 🔬 **Laboratory Staff**| `01733333333` | `123456` | `/login.php` ➔ `/lab` | 4-stage diagnostic pipelining, lab report compilation |
| 💊 **Pharmacist** | `017888888888`| `123456` | `/login.php` ➔ `/pharmacy` | Drug inventory monitoring, POS cart sales & stock reduction |

---

## 13. Security, Integrity & Audit Protocols

- **Role-Based Access Control (RBAC):** Strict session validation gates access to protected subdirectories (`/admin`, `/doctor`, `/patient`, `/receptionist`, `/lab`, `/pharmacy`). Unauthenticated or unauthorized requests are immediately intercepted and redirected.
- **SQL Injection Defense:** All database transactions execute exclusively through **PDO Prepared Statements** with explicit parameter binding.
- **Password Cryptography:** User passwords are encrypted using PHP's native `password_hash()` implementing the one-way **Bcrypt** cryptographic algorithm.
- **Cross-Site Scripting (XSS) Mitigation:** All dynamic database outputs rendered to HTML are escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **System Audit Logging:** Administrative events, sensitive clinical modifications, and staff actions are recorded in the `system_logs` table with network IP signatures and timestamps.

---

## 14. Project Team & Academic Attribution

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

### 👥 Engineering Team & Individual Contributions

| Member Name | Student ID | Primary Engineering Role | Detailed Scope of Contribution |
| :--- | :---: | :--- | :--- |
| **Md. Abdullah Walid** | **4006** | **Lead Full-Stack Developer & Software Architect** | Complete architectural design, backend PHP development across all 6 portals, database schema design (22 tables / 30 FKs), clinical prescription engine, QR queue token generator, security hardening, and live cloud deployment. |
| **Sakira Sheherin Chhowa**| **4007** | **UI/UX & Presentation Designer** | Visual design system, Tailwind CSS component layouts, slide deck organization, content presentation, and interface styling. |
| **Asmaul Husna** | **3993** | **Documentation & Requirements Analyst** | Software Requirements Specification (SRS v2.0), comprehensive UML diagram modeling (Use Case, Activity, Sequence, State, DFD, Class, ERD), and formal report documentation. |
| **Md. Hasibuzzaman Mahi** | **4010** | **Quality Assurance & Verification Engineer** | Test case design and execution (TC-01 through TC-12), cross-module integration verification, defect tracking, and submission QA. |

---

## 15. License & Intellectual Property

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
