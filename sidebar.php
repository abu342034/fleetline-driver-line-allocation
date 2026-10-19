<?php
$current = basename($_SERVER['PHP_SELF']);
if (!function_exists('navItem')) {
    function navItem($file, $label, $icon, $current, $badge = null) {
        $active = ($current === $file) ? 'active' : '';
        $b = ($badge !== null && $badge > 0)
            ? " <span class='badge bg-warning text-dark ms-1'>$badge</span>"
            : '';
        echo "<a href='$file' class='nav-item $active'><i class='fas $icon'></i> $label$b</a>";
    }
}
$pendingCount = 0;
if (isset($pdo)) {
    try { $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM drivers WHERE status='Pending'")->fetchColumn(); }
    catch (Exception $e) { $pendingCount = 0; }
}
?>
<aside class="sidebar">
  <div class="brand"><i class="fas fa-route"></i> FleetLine</div>
  <nav>
    <?php
    navItem('dashboard.php', 'Dashboard', 'fa-gauge-high', $current);
    navItem('drivers.php', 'Drivers', 'fa-id-card', $current);
    navItem('pending_drivers.php', 'Pending Approvals', 'fa-user-clock', $current, $pendingCount);
    navItem('vehicles.php', 'Vehicles', 'fa-truck', $current);
    navItem('routes.php', 'Routes', 'fa-map-location-dot', $current);
    navItem('new_allocation.php', 'New Allocation', 'fa-plus-circle', $current);
    navItem('all_allocations.php', 'All Allocations', 'fa-list-check', $current);
    ?>
    <a href='../logout.php' class='nav-item'><i class='fas fa-sign-out-alt'></i> Logout</a>
  </nav>
</aside>
