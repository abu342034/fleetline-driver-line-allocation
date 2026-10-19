<?php
require '../db.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("INSERT INTO routes (route_name,origin,destination,distance_km) VALUES (?,?,?,?)");
    $stmt->execute([trim($_POST['route_name']),trim($_POST['origin']),trim($_POST['destination']),(float)$_POST['distance_km']]);
    $_SESSION['flash']='Route added.';
    header('Location: routes.php'); exit;
}
$rows = $pdo->query("SELECT * FROM routes ORDER BY route_name")->fetchAll();
$flash=$_SESSION['flash']??''; unset($_SESSION['flash']);

$pageTitle='Routes'; $cssPath='../assets/css/style.css';
include '../includes/header.php';
?>
<div class="app-shell">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main-content">
    <div class="topbar"><h5><i class="fas fa-map-location-dot me-2"></i>Routes</h5></div>
    <div class="content-area">
      <?php if($flash):?><div class="alert alert-success"><?=htmlspecialchars($flash)?></div><?php endif;?>
      <div class="row g-3">
        <div class="col-lg-5"><div class="panel">
          <h5>Add Route</h5>
          <form method="post">
            <input type="hidden" name="add" value="1">
            <div class="mb-2"><label class="form-label">Route Name</label><input name="route_name" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Origin</label><input name="origin" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Destination</label><input name="destination" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Distance (km)</label><input type="number" step="0.1" name="distance_km" class="form-control" required></div>
            <button class="btn-primary-fl"><i class="fas fa-plus me-2"></i>Add Route</button>
          </form>
        </div></div>
        <div class="col-lg-7"><div class="panel">
          <h5>All Routes</h5>
          <div class="table-responsive"><table class="table table-hover align-middle">
            <thead><tr><th>#</th><th>Name</th><th>Origin</th><th>Destination</th><th>Distance</th></tr></thead>
            <tbody>
            <?php foreach($rows as $i=>$r):?>
              <tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['route_name'])?></td><td><?=htmlspecialchars($r['origin'])?></td>
                <td><?=htmlspecialchars($r['destination'])?></td><td><?=$r['distance_km']?> km</td></tr>
            <?php endforeach;?>
            </tbody>
          </table></div>
        </div></div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
