<?php
require 'db.php';
$error = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {

        if ($user['role'] === 'driver') {
            $d = $pdo->prepare("SELECT * FROM drivers WHERE user_id = ?");
            $d->execute([$user['id']]);
            $drv = $d->fetch();

            if (!$drv) {
                $error = 'No driver profile is linked to this account. Please contact the administrator.';
            } elseif ($drv['status'] === 'Pending') {
                $notice = 'Your registration is currently awaiting administrator approval.';
            } elseif ($drv['status'] === 'Rejected') {
                $error = 'Your registration has not been approved.';
                if (!empty($drv['rejection_reason'])) {
                    $error .= ' Reason: ' . $drv['rejection_reason'];
                }
            } elseif ((int)$drv['is_active'] !== 1) {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['driver_id'] = $drv['id'];
                header('Location: driver/dashboard.php');
                exit;
            }
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: admin/dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$flash = takeFlash();
$pageTitle = 'Login';
include 'includes/header.php';
?>
<div class="login-wrap">
  <div class="login-card">
    <h3><i class="fas fa-route me-2"></i>FleetLine</h3>
    <p class="sub">Sign in to your account</p>

    <?php if ($flash['ok']): ?>
      <div class="alert alert-success"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($flash['ok']) ?></div>
    <?php endif; ?>
    <?php if ($notice): ?>
      <div class="alert alert-warning"><i class="fas fa-hourglass-half me-2"></i><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn-primary-fl w-100"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
    </form>

    <div class="text-center mt-3">
      New driver? <a href="driver/register.php" style="color:var(--navy);font-weight:600;text-decoration:none">Create an account</a>
    </div>

    

    <div class="text-center mt-3">
      <a href="index.php" style="color:var(--navy);text-decoration:none">&larr; Back to Home</a>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
