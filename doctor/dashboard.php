<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('doctor');

$stats = [
    'total' => (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    'today' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date=CURDATE()")->fetchColumn(),
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn(),
    'confirmed' => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn()
];

$stmt = $pdo->query(
    "SELECT a.*, u.name AS patient_name, u.email, u.phone
     FROM appointments a
     JOIN users u ON u.id = a.patient_id
     ORDER BY a.appointment_date DESC, a.appointment_time DESC
     LIMIT 200"
);
$appointments = $stmt->fetchAll();

$pageTitle = 'Doctor Dashboard';
$homePrefix = '../';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h1 class="h2">Doctor Dashboard</h1><p class="text-secondary mb-0">Manage your clinic and appointment requests.</p></div>
    <a href="settings.php" class="btn btn-primary">Clinic Settings</a>
</div>

<div class="row g-3 mb-4">
<?php foreach ([
    'total' => 'All Appointments',
    'today' => "Today's Appointments",
    'pending' => 'Pending Requests',
    'confirmed' => 'Confirmed'
] as $key => $label): ?>
    <div class="col-6 col-lg-3">
        <div class="card p-3">
            <div class="text-secondary small"><?= e($label) ?></div>
            <div class="stat-number"><?= $stats[$key] ?></div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="card p-3 p-md-4">
<h3 class="h5 mb-3">Appointment Management</h3>
<div class="table-responsive">
<table class="table">
<thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Contact</th><th>Reason</th><th>Status</th><th>Update</th></tr></thead>
<tbody>
<?php foreach ($appointments as $a): ?>
<tr>
<td><?= e($a['appointment_date']) ?></td>
<td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
<td><?= e($a['patient_name']) ?></td>
<td><?= e($a['email']) ?><br><small><?= e($a['phone'] ?? '') ?></small></td>
<td><?= e($a['reason']) ?></td>
<td><?= e(ucfirst($a['status'])) ?></td>
<td>
<form method="post" action="update_appointment.php" class="d-flex gap-1">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
<select class="form-select form-select-sm" name="status" aria-label="Appointment status">
<?php foreach (['pending','confirmed','rejected','cancelled','completed'] as $status): ?>
<option value="<?= e($status) ?>" <?= $a['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
<?php endforeach; ?>
</select>
<button class="btn btn-sm btn-primary">Save</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$appointments): ?><tr><td colspan="7" class="text-center text-secondary py-4">No appointment requests yet.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
