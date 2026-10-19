<?php
require 'db.php';

$updates = [
    ['admin@system.com', 'Admin@12345'],
    ['driver@system.com', 'Driver@12345'],
];

foreach ($updates as [$email, $plain]) {
    $hash = password_hash($plain, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "Updated password for $email<br>";
}

echo "<br><strong>Done.</strong> You may now delete this file. <a href='login.php'>Go to Login</a>";