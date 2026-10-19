# 8J Cellphone Accessories Integrated Management System (IMS)

> **Thesis Capstone Project**  
> **Institution:** Tanauan Institute, Inc., Tanauan City, Batangas  
> **Degree:** Bachelor of Science in Computer Science (BSCS)  
> **Researchers:** Johanne Gabriel & Gigi B. Genil  

---

## 📱 System Overview

The **8J Cellphone Accessories Integrated Management System** is an end-to-end web-based platform tailored for retail cellphone shops. It integrates point-of-sale cashiering, IMEI-tracked smartphone inventory, automated second-hand phone appraisal, trade-in/swap balance computation, direct unit buyouts with Anti-Fencing Law (P.D. 1612) compliance, online pick-up reservations, and repair job order ticketing with service warranty tracking.

---

## 🛠️ Technology Stack

- **Server-Side Back-End:** PHP 8.x (Native PDO, Object-Oriented Database Architecture)
- **Database Management System:** MySQL 8.x / MariaDB (InnoDB, ACID compliant, Foreign Key Constraints)
- **Client-Side Front-End:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, FontAwesome 6
- **Local Web Server Environment:** Apache HTTP Server via XAMPP

---

## 🚀 Quick Start / How to Run

1. Open **XAMPP Control Panel**.
2. Click **Start** for **Apache** and **MySQL**.
3. Open your web browser and navigate to:
   ```
   http://localhost/8j_ims/
   ```
   *(The junction link has already been established between your workspace and `C:\xampp\htdocs\8j_ims`)*
4. Run the automated database seed to load demo accounts, inventory, and appraisal matrices:
   ```
   http://localhost/8j_ims/database/seed.php
   ```

---

## 🔑 Default Accounts for Thesis Defense

All accounts use the password: `password123`

| Role | Username | Password | Purpose / Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator / Owner** | `admin` | `password123` | Full access: Reports, User Accounts, POS, Inventory, Trades |
| **Store Manager** | `manager` | `password123` | Operational oversight & audits |
| **Cashier / Staff** | `staff` | `password123` | POS sales, store pick-up claim verification, buyouts |
| **Repair Technician** | `tech` | `password123` | Diagnostic ticketing, repair stages, warranty tagging |
| **Customer** | `customer` | `password123` | Online catalog, pick-up reservation vouchers, repair booking |

*Tip: The login page includes 1-click **Quick Demo Login buttons** for seamless panelist demonstrations.*

---

## 🧩 The 8 Core Modules Explained

### 1. Sales & POS Billing (`admin/pos.php`)
- Real-time cashier terminal for both retail accessories and serialized phones.
- Barcode/SKU lookup, dynamic discount calculations (Senior/PWD 20%, Regular promo), cash/GCash/card payment handling, change due computation, and instant receipt generation.

### 2. Dual Inventory Management (`admin/inventory.php`)
- **Accessories & Parts:** Batch tracking with quantity thresholds and low-stock reorder triggers.
- **Smartphones & Handhelds:** Unit-by-unit tracking by hardware **IMEI 1, IMEI 2 / Serial Number**, cosmetic condition grading, and battery health percentage.

### 3. Smartphone Appraisal Engine (`admin/appraisal.php`)
Algorithmic valuation based on hardware benchmarks:
$$\text{Graded Value} = \text{Base Market Value} \times \text{Condition Multiplier (A: 100\%, B: 80\%, C: 65\%)}$$
$$\text{Final Valuation} = \text{Graded Value} - (\text{Screen Deduction} + \text{Battery Deduction} + \text{Hardware Faults})$$
- Mandatory check for Clean iCloud / Google Account lock to prevent receiving bricked units.

### 4. Cellphone Trading / Swap Module (`admin/trading.php`)
- Computes valuation difference between trade-in unit and target store phone:
$$\text{Top-Up Balance} = \text{Target Shop Phone Price} - \text{Appraised Trade-in Value}$$
- Automatically sets outgoing phone to `Traded` and ingests the customer's phone into inventory as `Pre-Owned` ready for resale.

### 5. Unit Buying & Buyout Module (`admin/buying.php`)
- Complies with **Philippine Anti-Fencing Law (P.D. No. 1612)**.
- Captures seller personal information, government-issued valid ID type and ID number, and digital ownership declaration before cash disbursement.

### 6. Online Catalog & Ordering (`catalog.php`)
- Responsive storefront allowing customers to view live stock of accessories and certified secondhand phones from any device.

### 7. In-Store Pick-Up System (`cart.php` & `admin/pickups.php`)
- Checkout generates a unique **Claim Verification Code** (e.g., `8J-7492`).
- Staff verifies the code at the counter before releasing items and collecting payment.

### 8. Repair Job Orders & Warranty Tracking (`book_repair.php` & `admin/repairs.php`)
- Step-by-step diagnostic workflow: `Scheduled` ➔ `Received` ➔ `Diagnosing` ➔ `Waiting Parts` ➔ `Repairing` ➔ `Ready for Pick-Up` ➔ `Completed`.
- Completing a repair automatically generates an active **30-day Service Warranty**.
- Customers can track live progress at any time via `track_repair.php`.

---

## 📊 Analytics & Reports (`admin/reports.php`)
- Date-filtered revenue reporting covering retail POS sales, repair service earnings, and capital disbursed for unit acquisitions.
- Printable summary for business audits.
