# FleetLine — Drivers Line Allocation Management System

A modular PHP + MySQL system for managing driver and vehicle allocations to routes, with role-based access.

## Stack
- **PHP 7.4+** (PDO)
- **MySQL 5.7+**
- **Bootstrap 5.3** + Font Awesome 6
- Vanilla JavaScript

## Local Setup (XAMPP / WAMP / Laragon)

1. **Copy** the entire `fleetline` folder into your web server root:
   - XAMPP: `C:\xampp\htdocs\fleetline`
   - WAMP:  `C:\wamp64\www\fleetline`

2. **Start** Apache + MySQL from your control panel.

3. **Import the database**:
   - Open <http://localhost/phpmyadmin>
   - Click **Import** → choose `database.sql` → **Go**.
   - This creates the `fleetline_db` database with all tables and seed data.

4. **Set the test passwords** (one-time):
   - Visit <http://localhost/fleetline/setup_passwords.php>
   - You'll see confirmations like *"Updated password for admin@system.com"*.
   - **Delete** `setup_passwords.php` afterwards.

5. **Open the app**: <http://localhost/fleetline/>

## Test Credentials

| Role   | Email                | Password      |
|--------|----------------------|---------------|
| Admin  | admin@system.com     | Admin@12345   |
| Driver | driver@system.com    | Driver@12345  |

## File Structure

```
fleetline/
├── index.php                  Landing page
├── login.php                  Unified login (role-based redirect)
├── logout.php
├── db.php                     PDO connection + helpers
├── setup_passwords.php        One-time password initializer
├── database.sql               Full schema + seed data
├── admin/
│   ├── dashboard.php
│   ├── drivers.php
│   ├── vehicles.php
│   ├── routes.php
│   ├── new_allocation.php     Assignment form (separate page)
│   ├── all_allocations.php    Records table (separate page)
│   └── pending_drivers.php    Driver registration approvals
├── driver/
│   ├── register.php           Public driver self-registration
│   ├── dashboard.php          Driver dashboard
│   ├── schedule.php           Driver-only view of assigned routes
│   └── profile.php            Update phone / address / password
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php            Admin sidebar
│   ├── driver_sidebar.php     Driver sidebar
│   ├── driver_actions.php     Approve / Reject / Deactivate / Delete
│   └── driver_modals.php      View + Reject modals
└── assets/
    └── css/style.css
```

## Key Features

- **Smart Allocation Conflict Check** (`checkAllocationConflict()` in `db.php`) — blocks double-booking of either the driver or the vehicle on the same date/time.
- **Separate physical pages** for `new_allocation.php` and `all_allocations.php` (no tabs / no hidden divs).
- **Role-based redirects** — admins → `admin/dashboard.php`, drivers → `driver/schedule.php`.
- **Driver portal** queries `allocations` filtered by `$_SESSION['driver_id']`.

## Driver Registration & Approval Workflow

1. A driver registers at `driver/register.php` (name, email, phone, license no, address, password).
   Email and license number must be unique; passwords are hashed with `password_hash()`.
2. The record is stored in `drivers` with `status = 'Pending'`. The driver is **not** logged in.
3. Login is blocked while pending (*"Your registration is currently awaiting administrator approval."*)
   and while rejected (*"Your registration has not been approved."* plus the reason, if any).
4. The admin reviews submissions in **Pending Approvals** (`admin/pending_drivers.php`):
   - **Approve** → `status = 'Approved'`, `approved_at`, `approved_by` set.
   - **Reject** → modal asks for a reason, stored in `rejection_reason`.
5. Approved drivers appear automatically in the allocation dropdown
   (`getAllocatableDrivers()` filters `status = 'Approved' AND is_active = 1`).
6. **Drivers** (`admin/drivers.php`) lists everyone with status badges
   (Pending = orange, Approved = green, Rejected = red) and View / Approve / Reject / Deactivate / Delete actions.

## Upgrading an existing database

If you already have `fleetline_db` and don't want to lose data, import `upgrade.sql`
instead of `database.sql`. It adds `address`, `is_active`, `created_at`, `approved_at`,
`approved_by`, `rejection_reason` and converts the old `active`/`inactive` status values
to the new `Pending`/`Approved`/`Rejected` enum.

---
© 2026 FleetLine | Designed by Bin Hafiz
