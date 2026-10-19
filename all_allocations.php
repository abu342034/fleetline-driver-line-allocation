<?php
require '../db.php';
requireRole('admin');

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM allocations WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $_SESSION['flash'] = 'Allocation deleted.';
    header('Location: all_allocations.php');
    exit;
}

$rows = $pdo->query("
  SELECT a.*, u.full_name AS driver_name, v.plate_no, v.model, r.route_name, r.origin, r.destination
  FROM allocations a
  JOIN drivers d ON a.driver_id=d.id
  JOIN users u ON d.user_id=u.id
  JOIN vehicles v ON a.vehicle_id=v.id
  JOIN routes r ON a.route_id=r.id
  ORDER BY a.allocation_date DESC, a.allocation_time DESC
")->fetchAll();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$pageTitle = 'All Allocations';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-list-check me-2"></i>All Allocations</h5>
      <a href="new_allocation.php" class="btn-primary-fl"><i class="fas fa-plus me-2"></i>New Allocation</a>
    </div>
    <div class="content-area">
      <?php if ($flash): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash) ?></div>
      <?php endif; ?>

      <div class="panel">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>#</th><th>Driver</th><th>Vehicle</th><th>Route</th>
                <th>Date</th><th>Time</th><th>Notes</th><th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No allocations found. <a href="new_allocation.php">Create one</a>.</td></tr>
              <?php else: foreach ($rows as $i => $r): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($r['driver_name']) ?></td>
                  <td><?= htmlspecialchars($r['plate_no']) ?> <small class="text-muted">(<?= htmlspecialchars($r['model']) ?>)</small></td>
                  <td><?= htmlspecialchars($r['route_name']) ?> <small class="text-muted">(<?= htmlspecialchars($r['origin']) ?> &rarr; <?= htmlspecialchars($r['destination']) ?>)</small></td>
                  <td><?= htmlspecialchars($r['allocation_date']) ?></td>
                  <td><?= htmlspecialchars(substr($r['allocation_time'],0,5)) ?></td>
                  <td><?= htmlspecialchars($r['notes'] ?: '—') ?></td>
                  <td>
                    <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this allocation?')"><i class="fas fa-trash"></i></a>
                  </td>
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
