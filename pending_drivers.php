<?php
require '../db.php';
requireRole('admin');

$redirectTo = 'pending_drivers.php';
require '../includes/driver_actions.php';

$rows = $pdo->query("
  SELECT d.*, u.full_name, u.email, au.full_name AS approver_name
  FROM drivers d
  JOIN users u ON d.user_id = u.id
  LEFT JOIN users au ON d.approved_by = au.id
  WHERE d.status = 'Pending'
  ORDER BY d.created_at DESC
")->fetchAll();

$flash = takeFlash();
$pageTitle = 'Pending Approvals';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-user-clock me-2"></i>Pending Driver Approvals</h5>
      <a href="drivers.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-id-card me-1"></i>All Drivers</a>
    </div>
    <div class="content-area">
      <?php if ($flash['ok']): ?><div class="alert alert-success"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($flash['ok']) ?></div><?php endif; ?>
      <?php if ($flash['err']): ?><div class="alert alert-danger"><i class="fas fa-triangle-exclamation me-2"></i><?= htmlspecialchars($flash['err']) ?></div><?php endif; ?>

      <div class="panel">
        <h5>Registrations Awaiting Approval</h5>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>License No</th><th>Registered</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No pending registrations.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($r['full_name']) ?></strong></td>
                <td><?= htmlspecialchars($r['email']) ?></td>
                <td><?= htmlspecialchars($r['phone'] ?: '—') ?></td>
                <td><?= htmlspecialchars($r['license_no']) ?></td>
                <td><?= htmlspecialchars($r['created_at'] ?: '—') ?></td>
                <td><span class="badge <?= statusBadgeClass($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                <td class="text-end text-nowrap">
                  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewDriver<?= $r['id'] ?>"><i class="fas fa-eye"></i> View</button>
                  <form method="post" class="d-inline" onsubmit="return confirm('Approve this driver?')">
                    <input type="hidden" name="driver_action" value="approve">
                    <input type="hidden" name="driver_id" value="<?= $r['id'] ?>">
                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                  </form>
                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectDriver<?= $r['id'] ?>"><i class="fas fa-xmark"></i> Reject</button>
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
<?php $modalDrivers = $rows; include '../includes/driver_modals.php'; ?>
<?php include '../includes/footer.php'; ?>
