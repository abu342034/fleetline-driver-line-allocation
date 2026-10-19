<?php
require '../db.php';
requireRole('admin');

$redirectTo = 'drivers.php';
require '../includes/driver_actions.php';

// Admin can still add a driver manually (created pre-approved)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $license = trim($_POST['license_no'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $pass    = password_hash($_POST['password'] ?: 'Driver@12345', PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();
        $u = $pdo->prepare("INSERT INTO users (full_name,email,password,role) VALUES (?,?,?,'driver')");
        $u->execute([$name, $email, $pass]);
        $uid = $pdo->lastInsertId();
        $d = $pdo->prepare("INSERT INTO drivers (user_id,license_no,phone,address,status,approved_at,approved_by)
                            VALUES (?,?,?,?, 'Approved', CURRENT_TIMESTAMP, ?)");
        $d->execute([$uid, $license, $phone, $address, $_SESSION['user_id']]);
        $pdo->commit();
        setFlash('Driver added and approved.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setFlash('Error: ' . $e->getMessage(), 'error');
    }
    header('Location: drivers.php'); exit;
}

$filter = $_GET['status'] ?? 'all';
$sql = "SELECT d.*, u.full_name, u.email, au.full_name AS approver_name
        FROM drivers d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN users au ON d.approved_by = au.id";
$params = [];
if (in_array($filter, ['Pending','Approved','Rejected'], true)) {
    $sql .= " WHERE d.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY FIELD(d.status,'Pending','Approved','Rejected'), u.full_name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$counts = getDriverCounts($pdo);
$flash  = takeFlash();

$pageTitle = 'Drivers';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-id-card me-2"></i>Drivers</h5>
      <a href="pending_drivers.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-user-clock me-1"></i>Pending Approvals (<?= $counts['pending'] ?>)</a>
    </div>
    <div class="content-area">
      <?php if ($flash['ok']): ?><div class="alert alert-success"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($flash['ok']) ?></div><?php endif; ?>
      <?php if ($flash['err']): ?><div class="alert alert-danger"><i class="fas fa-triangle-exclamation me-2"></i><?= htmlspecialchars($flash['err']) ?></div><?php endif; ?>

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="panel">
            <h5>Add New Driver</h5>
            <form method="post">
              <input type="hidden" name="add" value="1">
              <div class="mb-2"><label class="form-label">Full Name</label><input name="full_name" class="form-control" required></div>
              <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
              <div class="mb-2"><label class="form-label">License No</label><input name="license_no" class="form-control" required></div>
              <div class="mb-2"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
              <div class="mb-2"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
              <div class="mb-3"><label class="form-label">Password (default: Driver@12345)</label><input name="password" class="form-control" placeholder="Leave blank for default"></div>
              <button class="btn-primary-fl"><i class="fas fa-plus me-2"></i>Add Driver</button>
              <div class="creds-box"><i class="fas fa-circle-info me-1"></i>Drivers added here are approved automatically. Self-registered drivers appear under Pending Approvals.</div>
            </form>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="panel">
            <h5>All Drivers</h5>
            <div class="mb-3">
              <?php foreach ([['all','All',$counts['total']],['Pending','Pending',$counts['pending']],['Approved','Approved',$counts['approved']],['Rejected','Rejected',$counts['rejected']]] as [$k,$lbl,$n]): ?>
                <a href="drivers.php?status=<?= $k ?>" class="btn btn-sm <?= $filter === $k ? 'btn-primary-fl' : 'btn-outline-secondary' ?> me-1"><?= $lbl ?> (<?= $n ?>)</a>
              <?php endforeach; ?>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>License</th><th>Status</th><th>Registered</th><th>Approved</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                  <tr><td colspan="9" class="text-center text-muted py-4">No drivers found.</td></tr>
                <?php else: foreach ($rows as $i => $r): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td>
                      <strong><?= htmlspecialchars($r['full_name']) ?></strong>
                      <?php if ((int)$r['is_active'] !== 1): ?><br><small class="text-muted">Deactivated</small><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars($r['phone'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['license_no']) ?></td>
                    <td><span class="badge <?= statusBadgeClass($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                    <td><small><?= htmlspecialchars($r['created_at'] ?: '—') ?></small></td>
                    <td><small><?= htmlspecialchars($r['approved_at'] ?: '—') ?></small></td>
                    <td class="text-end text-nowrap">
                      <button class="btn btn-sm btn-outline-secondary mb-1" data-bs-toggle="modal" data-bs-target="#viewDriver<?= $r['id'] ?>"><i class="fas fa-eye"></i></button>
                      <?php if ($r['status'] === 'Pending'): ?>
                        <form method="post" class="d-inline" onsubmit="return confirm('Approve this driver?')">
                          <input type="hidden" name="driver_action" value="approve">
                          <input type="hidden" name="driver_id" value="<?= $r['id'] ?>">
                          <button class="btn btn-sm btn-success mb-1"><i class="fas fa-check"></i></button>
                        </form>
                        <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#rejectDriver<?= $r['id'] ?>"><i class="fas fa-xmark"></i></button>
                      <?php endif; ?>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="driver_action" value="<?= ((int)$r['is_active'] === 1) ? 'deactivate' : 'activate' ?>">
                        <input type="hidden" name="driver_id" value="<?= $r['id'] ?>">
                        <button class="btn btn-sm btn-outline-warning mb-1" title="<?= ((int)$r['is_active'] === 1) ? 'Deactivate' : 'Reactivate' ?>">
                          <i class="fas <?= ((int)$r['is_active'] === 1) ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                        </button>
                      </form>
                      <form method="post" class="d-inline" onsubmit="return confirm('Delete this driver permanently? Their allocations will also be removed.')">
                        <input type="hidden" name="driver_action" value="delete">
                        <input type="hidden" name="driver_id" value="<?= $r['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger mb-1"><i class="fas fa-trash"></i></button>
                      </form>
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
  </div>
</div>
<?php $modalDrivers = $rows; include '../includes/driver_modals.php'; ?>
<?php include '../includes/footer.php'; ?>
