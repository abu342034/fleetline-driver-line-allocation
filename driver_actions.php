<?php
// includes/driver_actions.php
// Shared Approve / Reject / Deactivate / Delete handling for admin driver pages.
// Requires: $pdo, admin session, and $redirectTo (the page to return to).

if (!isset($redirectTo)) $redirectTo = 'drivers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['driver_action'])) {
    $action   = $_POST['driver_action'];
    $driverId = (int)($_POST['driver_id'] ?? 0);
    $adminId  = (int)$_SESSION['user_id'];

    try {
        if ($action === 'approve' && $driverId) {
            $st = $pdo->prepare("UPDATE drivers
                                 SET status='Approved', approved_at=CURRENT_TIMESTAMP,
                                     approved_by=?, rejection_reason=NULL, is_active=1
                                 WHERE id=?");
            $st->execute([$adminId, $driverId]);
            setFlash('Driver approved successfully. They can now log in and be allocated to routes.');

        } elseif ($action === 'reject' && $driverId) {
            $reason = trim($_POST['rejection_reason'] ?? '');
            if ($reason === '') {
                setFlash('A rejection reason is required.', 'error');
            } else {
                $st = $pdo->prepare("UPDATE drivers
                                     SET status='Rejected', rejection_reason=?, approved_at=NULL, approved_by=NULL
                                     WHERE id=?");
                $st->execute([$reason, $driverId]);
                setFlash('Driver registration rejected successfully.');
            }

        } elseif ($action === 'deactivate' && $driverId) {
            $st = $pdo->prepare("UPDATE drivers SET is_active=0 WHERE id=?");
            $st->execute([$driverId]);
            setFlash('Driver deactivated successfully.');

        } elseif ($action === 'activate' && $driverId) {
            $st = $pdo->prepare("UPDATE drivers SET is_active=1 WHERE id=?");
            $st->execute([$driverId]);
            setFlash('Driver reactivated successfully.');

        } elseif ($action === 'delete' && $driverId) {
            $st = $pdo->prepare("SELECT user_id FROM drivers WHERE id=?");
            $st->execute([$driverId]);
            $uid = $st->fetchColumn();
            if ($uid) {
                // ON DELETE CASCADE removes the driver row and their allocations
                $del = $pdo->prepare("DELETE FROM users WHERE id=?");
                $del->execute([$uid]);
                setFlash('Driver deleted successfully.');
            }
        }
    } catch (Exception $e) {
        setFlash('Action failed: ' . $e->getMessage(), 'error');
    }

    header('Location: ' . $redirectTo);
    exit;
}
