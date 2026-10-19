<?php
require '../db.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO vehicles (plate_no,model,capacity,status) VALUES (?,?,?,?)");
    $stmt->execute([trim($_POST['plate_no']),trim($_POST['model']),(int)$_POST['capacity'],$_POST['status']]);
    $_SESSION['flash']='Vehicle added.';
    header('Location: vehicles.php'); exit;
}
$rows = $pdo->query("SELECT * FROM vehicles ORDER BY plate_no")->fetchAll();
$flash=$_SESSION['flash']??''; unset($_SESSION['flash']);

$pageTitle='Vehicles'; $cssPath='../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar"><h5><i class="fas fa-truck me-2"></i>Vehicles</h5></div>
    <div class="content-area">
      <?php if($flash):?><div class="alert alert-success"><?=htmlspecialchars($flash)?></div><?php endif;?>
      <div class="row g-3">
        <div class="col-lg-5"><div class="panel">
          <h5>Add Vehicle</h5>
          <form method="post">
            <input type="hidden" name="add" value="1">
            <div class="mb-2"><label class="form-label">Plate No</label><input name="plate_no" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Model</label><input name="model" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" min="1" required></div>
            <div class="mb-3"><label class="form-label">Status</label>
              <select name="status" class="form-select"><option>active</option><option>maintenance</option><option>inactive</option></select>
            </div>
            <button class="btn-primary-fl"><i class="fas fa-plus me-2"></i>Add Vehicle</button>
          </form>
        </div></div>
        <div class="col-lg-7"><div class="panel">
          <h5>All Vehicles</h5>
          <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Plate</th><th>Model</th><th>Capacity</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($rows as $i=>$r):?>
              <tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['plate_no'])?></td><td><?=htmlspecialchars($r['model'])?></td><td><?=$r['capacity']?></td>
                <td><span class="badge bg-<?=$r['status']==='active'?'success':($r['status']==='maintenance'?'warning':'secondary')?>"><?=$r['status']?></span></td></tr>
            <?php endforeach;?>
            </tbody>
          </table></div>
        </div></div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
