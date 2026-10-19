<?php
require '../db.php';
requireRole('driver');

$driverId = $_SESSION['driver_id'] ?? 0;
$today = date('Y-m-d');

$stmt = $pdo->prepare("
  SELECT a.*, v.plate_no, r.route_name, r.origin, r.destination
  FROM allocations a
  JOIN vehicles v ON a.vehicle_id=v.id
  JOIN routes r ON a.route_id=r.id
  WHERE a.driver_id = ?
  ORDER BY a.allocation_date ASC, a.allocation_time ASC
");
$stmt->execute([$driverId]);
$rows = $stmt->fetchAll();

$total    = count($rows);
$upcoming = count(array_filter($rows, fn($r) => $r['allocation_date'] > $today));
$todayRows = array_values(array_filter($rows, fn($r) => $r['allocation_date'] === $today));

$pageTitle = 'Driver Dashboard';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/driver_sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-gauge-high me-2"></i>Dashboard</h5>
      <div>Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></div>
    </div>
    <div class="content-area">
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="label">Total Routes</div><div class="num"><?= $total ?></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="label">Upcoming</div><div class="num"><?= $upcoming ?></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="label">Today</div><div class="num"><?= count($todayRows) ?></div></div></div>
      </div>

      <div class="panel">
        <h5>Today's Assignments</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead><tr><th>Route</th><th>From &rarr; To</th><th>Vehicle</th><th>Time</th></tr></thead>
            <tbody>
              <?php if (!$todayRows): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No assignments scheduled for today.</td></tr>
              <?php else: foreach ($todayRows as $r): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($r['route_name']) ?></strong></td>
                  <td><?= htmlspecialchars($r['origin']) ?> &rarr; <?= htmlspecialchars($r['destination']) ?></td>
                  <td><?= htmlspecialchars($r['plate_no']) ?></td>
                  <td><?= htmlspecialchars(substr($r['allocation_time'],0,5)) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <a href="schedule.php" class="btn-primary-fl"><i class="fas fa-calendar-days me-2"></i>View Full Schedule</a>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
