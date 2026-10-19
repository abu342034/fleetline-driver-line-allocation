<?php
require '../db.php';
requireRole('driver');

$driverId = $_SESSION['driver_id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT d.*, u.full_name, u.email
  FROM drivers d JOIN users u ON d.user_id = u.id
  WHERE d.id = ?
");
$stmt->execute([$driverId]);
$driver = $stmt->fetch();

$errors = [];
$flash  = takeFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driver) {
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($phone === '')   $errors[] = 'Phone number is required.';
    if ($address === '') $errors[] = 'Residential address is required.';

    $changePassword = ($new !== '' || $confirm !== '' || $current !== '');
    if ($changePassword) {
        $u = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $u->execute([$_SESSION['user_id']]);
        $hash = $u->fetchColumn();
        if (!password_verify($current, $hash)) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8)                  $errors[] = 'New password must be at least 8 characters long.';
        if ($new !== $confirm)                 $errors[] = 'New password and confirmation do not match.';
    }

    if (!$errors) {
        $up = $pdo->prepare("UPDATE drivers SET phone = ?, address = ? WHERE id = ?");
        $up->execute([$phone, $address, $driverId]);
        if ($changePassword) {
            $pw = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pw->execute([password_hash($new, PASSWORD_BCRYPT), $_SESSION['user_id']]);
        }
        setFlash('Profile updated successfully.');
        header('Location: profile.php');
        exit;
    }
}

$pageTitle = 'Profile';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/driver_sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar">
      <h5><i class="fas fa-user-gear me-2"></i>My Profile</h5>
      <div>Driver: <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></div>
    </div>
    <div class="content-area">
      <?php if ($flash['ok']): ?><div class="alert alert-success"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($flash['ok']) ?></div><?php endif; ?>
      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <i class="fas fa-triangle-exclamation me-2"></i>Please correct the following:
          <ul class="mb-0 mt-2"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <div class="panel" style="max-width:760px">
        <h5>Account Details</h5>
        <form method="post">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input class="form-control" value="<?= htmlspecialchars($driver['full_name'] ?? '') ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address <small class="text-muted">(cannot be changed)</small></label>
              <input class="form-control" value="<?= htmlspecialchars($driver['email'] ?? '') ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">License Number <small class="text-muted">(cannot be changed)</small></label>
              <input class="form-control" value="<?= htmlspecialchars($driver['license_no'] ?? '') ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label><br>
              <span class="badge <?= statusBadgeClass($driver['status'] ?? 'Pending') ?>"><?= htmlspecialchars($driver['status'] ?? '') ?></span>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input name="phone" class="form-control" value="<?= htmlspecialchars($driver['phone'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Residential Address</label>
              <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($driver['address'] ?? '') ?></textarea>
            </div>
          </div>

          <hr class="my-4">
          <h5>Change Password <small class="text-muted" style="font-weight:400">(leave blank to keep current)</small></h5>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" autocomplete="current-password">
            </div>
            <div class="col-md-4">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" autocomplete="new-password">
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" autocomplete="new-password">
            </div>
          </div>

          <button class="btn-primary-fl mt-4"><i class="fas fa-floppy-disk me-2"></i>Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
