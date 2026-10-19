<?php
require '../db.php';
requireRole('driver');

$driverId = $_SESSION['driver_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT a.*, v.plate_no, v.model, r.route_name, r.origin, r.destination, r.distance_km
  FROM allocations a
  JOIN vehicles v ON a.vehicle_id=v.id
  JOIN routes r ON a.route_id=r.id
  WHERE a.driver_id = ?
  ORDER BY a.allocation_date DESC, a.allocation_time DESC
");
$stmt->execute([$driverId]);
$rows = $stmt->fetchAll();

$today = date('Y-m-d');
$upcoming = array_filter($rows, fn($r) => $r['allocation_date'] >= $today);

$pageTitle = 'My Schedule';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/driver_sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-calendar-days me-2"></i>My Schedule</h5>
      <div>Driver: <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></div>
    </div>
    <div class="content-area">
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="label">Total Routes</div><div class="num"><?= count($rows) ?></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="label">Upcoming</div><div class="num"><?= count($upcoming) ?></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="label">Today's Date</div><div class="num" style="font-size:1.2rem"><?= date('M d, Y') ?></div></div></div>
      </div>

      <div class="panel">
        <h5>Assigned Routes</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr><th>#</th><th>Route</th><th>From → To</th><th>Vehicle</th><th>Date</th><th>Time</th><th>Notes</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No routes assigned yet. Contact your administrator.</td></tr>
              <?php else: foreach ($rows as $i => $r):
                $isPast = $r['allocation_date'] < $today;
                $isToday = $r['allocation_date'] === $today;
                $badge = $isPast ? 'secondary' : ($isToday ? 'warning' : 'success');
                $label = $isPast ? 'Completed' : ($isToday ? 'Today' : 'Upcoming');
              ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><strong><?= htmlspecialchars($r['route_name']) ?></strong><br><small class="text-muted"><?= $r['distance_km'] ?> km</small></td>
                  <td><?= htmlspecialchars($r['origin']) ?> &rarr; <?= htmlspecialchars($r['destination']) ?></td>
                  <td><?= htmlspecialchars($r['plate_no']) ?><br><small class="text-muted"><?= htmlspecialchars($r['model']) ?></small></td>
                  <td><?= htmlspecialchars($r['allocation_date']) ?></td>
                  <td><?= htmlspecialchars(substr($r['allocation_time'],0,5)) ?></td>
                  <td><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
                  <td><span class="badge bg-<?= $badge ?>"><?= $label ?></span></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
