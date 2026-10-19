<?php
// includes/driver_modals.php
// Renders the View + Reject modals for a list of drivers ($modalDrivers).
if (!isset($modalDrivers)) $modalDrivers = [];
foreach ($modalDrivers as $m):
?>
<!-- View modal -->
<div class="modal fade" id="viewDriver<?= $m['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-id-card me-2"></i>Driver Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-5">Full Name</dt><dd class="col-7"><?= htmlspecialchars($m['full_name']) ?></dd>
          <dt class="col-5">Email</dt><dd class="col-7"><?= htmlspecialchars($m['email']) ?></dd>
          <dt class="col-5">Phone</dt><dd class="col-7"><?= htmlspecialchars($m['phone'] ?: '—') ?></dd>
          <dt class="col-5">License No</dt><dd class="col-7"><?= htmlspecialchars($m['license_no']) ?></dd>
          <dt class="col-5">Address</dt><dd class="col-7"><?= htmlspecialchars($m['address'] ?: '—') ?></dd>
          <dt class="col-5">Status</dt><dd class="col-7"><span class="badge <?= statusBadgeClass($m['status']) ?>"><?= htmlspecialchars($m['status']) ?></span></dd>
          <dt class="col-5">Account</dt><dd class="col-7"><?= ((int)$m['is_active'] === 1) ? 'Active' : 'Deactivated' ?></dd>
          <dt class="col-5">Registered</dt><dd class="col-7"><?= htmlspecialchars($m['created_at'] ?: '—') ?></dd>
          <dt class="col-5">Approved On</dt><dd class="col-7"><?= htmlspecialchars($m['approved_at'] ?: '—') ?></dd>
          <dt class="col-5">Approved By</dt><dd class="col-7"><?= htmlspecialchars($m['approver_name'] ?? '—') ?></dd>
          <?php if (!empty($m['rejection_reason'])): ?>
            <dt class="col-5">Rejection Reason</dt><dd class="col-7"><?= htmlspecialchars($m['rejection_reason']) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject modal -->
<div class="modal fade" id="rejectDriver<?= $m['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <input type="hidden" name="driver_action" value="reject">
      <input type="hidden" name="driver_id" value="<?= $m['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-circle-xmark me-2"></i>Reject Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">You are rejecting <strong><?= htmlspecialchars($m['full_name']) ?></strong>.</p>
        <label class="form-label">Rejection Reason</label>
        <textarea name="rejection_reason" class="form-control" rows="3" required
                  placeholder="Explain why this registration is being rejected"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger"><i class="fas fa-circle-xmark me-1"></i>Confirm Rejection</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>
