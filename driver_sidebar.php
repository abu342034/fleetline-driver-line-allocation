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
?>
<aside class="sidebar">
  <div class="brand"><i class="fas fa-route"></i> FleetLine</div>
  <nav>
    <?php
    navItem('dashboard.php', 'Dashboard', 'fa-gauge-high', $current);
    navItem('schedule.php', 'My Schedule', 'fa-calendar-days', $current);
    navItem('profile.php', 'Profile', 'fa-user-gear', $current);
    ?>
    <a href='../logout.php' class='nav-item'><i class='fas fa-sign-out-alt'></i> Logout</a>
  </nav>
</aside>
