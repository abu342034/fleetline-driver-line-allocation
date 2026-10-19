<?php
require '../db.php';

$errors  = [];
$success = false;
$old = ['full_name'=>'','email'=>'','phone'=>'','license_no'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name']  = trim($_POST['full_name'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['phone']      = trim($_POST['phone'] ?? '');
    $old['license_no'] = trim($_POST['license_no'] ?? '');
    $old['address']    = trim($_POST['address'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '')  $errors[] = 'Full name is required.';
    if ($old['email'] === '')      $errors[] = 'Email address is required.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($old['phone'] === '')      $errors[] = 'Phone number is required.';
    if ($old['license_no'] === '') $errors[] = "Driver's license number is required.";
    if ($old['address'] === '')    $errors[] = 'Residential address is required.';
    if (strlen($pass) < 8)         $errors[] = 'Password must be at least 8 characters long.';
    if ($pass !== $confirm)        $errors[] = 'Password and confirmation do not match.';

    if (!$errors) {
        $c = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $c->execute([$old['email']]);
        if ($c->fetchColumn() > 0) $errors[] = 'An account with this email address already exists.';

        $c = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE license_no = ?");
        $c->execute([$old['license_no']]);
        if ($c->fetchColumn() > 0) $errors[] = 'This driver\'s license number is already registered.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $u = $pdo->prepare("INSERT INTO users (full_name,email,password,role) VALUES (?,?,?,'driver')");
            $u->execute([$old['full_name'], $old['email'], $hash]);
            $uid = $pdo->lastInsertId();
            $d = $pdo->prepare("INSERT INTO drivers (user_id,license_no,phone,address,status) VALUES (?,?,?,?,'Pending')");
            $d->execute([$uid, $old['license_no'], $old['phone'], $old['address']]);
            $pdo->commit();
            $success = true;
            $old = ['full_name'=>'','email'=>'','phone'=>'','license_no'=>'','address'=>''];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Driver Registration';
$cssPath = '../assets/css/style.css';
include '../includes/header.php';
?>
<div class="login-wrap">
  <div class="login-card" style="max-width:640px">
    <h3><i class="fas fa-route me-2"></i>FleetLine</h3>
    <p class="sub">Driver Registration</p>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fas fa-circle-check me-2"></i>
        Registration submitted successfully. Your account is awaiting administrator approval.
      </div>
      <a href="../login.php" class="btn-primary-fl w-100 d-block text-center text-decoration-none">
        <i class="fas fa-sign-in-alt me-2"></i>Go to Login
      </a>
    <?php else: ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <i class="fas fa-triangle-exclamation me-2"></i>Please correct the following:
          <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input name="full_name" class="form-control" value="<?= htmlspecialchars($old['full_name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input name="phone" class="form-control" value="<?= htmlspecialchars($old['phone']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Driver's License Number</label>
            <input name="license_no" class="form-control" value="<?= htmlspecialchars($old['license_no']) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Residential Address</label>
            <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($old['address']) ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
          </div>
        </div>
        <button class="btn-primary-fl w-100 mt-4"><i class="fas fa-user-plus me-2"></i>Submit Registration</button>
      </form>

      <div class="creds-box">
        <i class="fas fa-circle-info me-1"></i>
        New accounts must be approved by an administrator before you can sign in.
      </div>

      <div class="text-center mt-3">
        Already registered? <a href="../login.php" style="color:var(--navy);font-weight:600;text-decoration:none">Login here</a>
      </div>
    <?php endif; ?>

    <div class="text-center mt-3">
      <a href="../index.php" style="color:var(--navy);text-decoration:none">&larr; Back to Home</a>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
