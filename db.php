<?php
// db.php - PDO Database Connection
$host = 'localhost';
$dbname = 'fleetline_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Smart Allocation Conflict Checker
function checkAllocationConflict($pdo, $driverId, $vehicleId, $date, $time, $excludeId = null) {
    $sql = "SELECT id, 
                   (driver_id = :driver_id) AS driver_conflict,
                   (vehicle_id = :vehicle_id) AS vehicle_conflict
            FROM allocations 
            WHERE allocation_date = :date 
              AND allocation_time = :time
              AND (driver_id = :driver_id2 OR vehicle_id = :vehicle_id2)";
    if ($excludeId) {
        $sql .= " AND id != :exclude_id";
    }
    $stmt = $pdo->prepare($sql);
    $params = [
        ':driver_id' => $driverId,
        ':vehicle_id' => $vehicleId,
        ':driver_id2' => $driverId,
        ':vehicle_id2' => $vehicleId,
        ':date' => $date,
        ':time' => $time,
    ];
    if ($excludeId) $params[':exclude_id'] = $excludeId;
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $conflicts = [];
    foreach ($rows as $r) {
        if ($r['driver_conflict']) $conflicts[] = 'driver';
        if ($r['vehicle_conflict']) $conflicts[] = 'vehicle';
    }
    return array_unique($conflicts); // [] = no conflict
}

function requireRole($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header('Location: ' . ($role === 'admin' ? '../login.php' : '../login.php'));
        exit;
    }
}

/* ---------------------------------------------------------------
   Driver Registration & Approval helpers
   --------------------------------------------------------------- */

// Bootstrap colour for a driver approval status
function statusBadgeClass($status) {
    switch ($status) {
        case 'Approved': return 'bg-success';
        case 'Rejected': return 'bg-danger';
        case 'Pending':
        default:         return 'bg-warning text-dark';
    }
}

// Only Approved + active drivers may be allocated
function getAllocatableDrivers($pdo) {
    return $pdo->query("
        SELECT d.id, u.full_name, d.license_no
        FROM drivers d
        JOIN users u ON d.user_id = u.id
        WHERE d.status = 'Approved' AND d.is_active = 1
        ORDER BY u.full_name
    ")->fetchAll();
}

// Driver approval status counts for the dashboard cards
function getDriverCounts($pdo) {
    $row = $pdo->query("
        SELECT
          COUNT(*) AS total,
          SUM(status='Pending')  AS pending,
          SUM(status='Approved') AS approved,
          SUM(status='Rejected') AS rejected
        FROM drivers
    ")->fetch();
    return [
        'total'    => (int)($row['total'] ?? 0),
        'pending'  => (int)($row['pending'] ?? 0),
        'approved' => (int)($row['approved'] ?? 0),
        'rejected' => (int)($row['rejected'] ?? 0),
    ];
}

// Flash message helpers (kept compatible with existing $_SESSION['flash'] usage)
function setFlash($msg, $type = 'success') {
    if ($type === 'success') $_SESSION['flash'] = $msg;
    else                     $_SESSION['flash_err'] = $msg;
}

function takeFlash() {
    $f = ['ok' => $_SESSION['flash'] ?? '', 'err' => $_SESSION['flash_err'] ?? ''];
    unset($_SESSION['flash'], $_SESSION['flash_err']);
    return $f;
}
