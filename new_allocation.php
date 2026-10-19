<?php
require '../db.php';
requireRole('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driverId  = (int)($_POST['driver_id'] ?? 0);
    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    $routeId   = (int)($_POST['route_id'] ?? 0);
    $date      = $_POST['allocation_date'] ?? '';
    $time      = $_POST['allocation_time'] ?? '';
    $notes     = trim($_POST['notes'] ?? '');

    if (!$driverId || !$vehicleId || !$routeId || !$date || !$time) {
        $error = 'All fields except notes are required.';
    } else {
        $conflicts = checkAllocationConflict($pdo, $driverId, $vehicleId, $date, $time);
        if (!empty($conflicts)) {
            $msg = [];
            if (in_array('driver', $conflicts))  $msg[] = 'the selected <strong>driver</strong> is already assigned';
            if (in_array('vehicle', $conflicts)) $msg[] = 'the selected <strong>vehicle</strong> is already assigned';
            $error = 'Conflict detected: ' . implode(' and ', $msg) . " on $date at " . substr($time,0,5) . '.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO allocations (driver_id,vehicle_id,route_id,allocation_date,allocation_time,notes) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$driverId,$vehicleId,$routeId,$date,$time,$notes]);
            $_SESSION['flash'] = 'Allocation created successfully.';
            header('Location: all_allocations.php');
            exit;
        }
    }
}

// Only Approved (and active) drivers can be allocated
$drivers  = getAllocatableDrivers($pdo);
$vehicles = $pdo->query("SELECT id, plate_no, model FROM vehicles WHERE status='active' ORDER BY plate_no")->fetchAll();
$routes   = $pdo->query("SELECT id, route_name, origin, destination FROM routes ORDER BY route_name")->fetchAll();

$pageTitle = 'New Allocation';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-plus-circle me-2"></i>New Allocation</h5>
      <a href="all_allocations.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list me-1"></i>View All</a>
    </div>
    <div class="content-area">
      <div class="panel" style="max-width:760px">
        <h5>Assign Driver & Vehicle to a Route</h5>

        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-triangle-exclamation me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Driver</label>
              <select name="driver_id" class="form-select" required>
                <option value="">-- Select Driver --</option>
                <?php if (!$drivers): ?><option value="" disabled>No approved drivers available</option><?php endif; ?>
                <?php foreach ($drivers as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?> (<?= htmlspecialchars($d['license_no']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Vehicle</label>
              <select name="vehicle_id" class="form-select" required>
                <option value="">-- Select Vehicle --</option>
                <?php foreach ($vehicles as $v): ?>
                  <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['plate_no']) ?> &mdash; <?= htmlspecialchars($v['model']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Route</label>
              <select name="route_id" class="form-select" required>
                <option value="">-- Select Route --</option>
                <?php foreach ($routes as $r): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['route_name']) ?> (<?= htmlspecialchars($r['origin']) ?> &rarr; <?= htmlspecialchars($r['destination']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date</label>
              <input type="date" name="allocation_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Time</label>
              <input type="time" name="allocation_time" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
          <div class="mt-4">
            <button class="btn-primary-fl"><i class="fas fa-save me-2"></i>Save Allocation</button>
            <a href="all_allocations.php" class="btn btn-link">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
