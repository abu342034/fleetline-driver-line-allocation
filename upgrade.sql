-- FleetLine Upgrade Script
-- Run this ONLY if you already have an older fleetline_db and do not want to
-- re-import database.sql (which drops all tables).
USE fleetline_db;

ALTER TABLE drivers ADD COLUMN address TEXT NULL AFTER phone;
ALTER TABLE drivers ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE drivers ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE drivers ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE drivers ADD COLUMN approved_by INT NULL DEFAULT NULL;
ALTER TABLE drivers ADD COLUMN rejection_reason TEXT NULL DEFAULT NULL;

-- Widen the enum first so old values survive, then convert them.
ALTER TABLE drivers MODIFY status ENUM('active','inactive','Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending';
UPDATE drivers SET status = 'Approved', approved_at = CURRENT_TIMESTAMP WHERE status = 'active';
UPDATE drivers SET status = 'Approved', is_active = 0 WHERE status = 'inactive';
ALTER TABLE drivers MODIFY status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending';

ALTER TABLE drivers ADD UNIQUE KEY uniq_license (license_no);
