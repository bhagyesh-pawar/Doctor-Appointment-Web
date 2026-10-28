
<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('patient');

// Fetch the current patient's appointments
$stmt = $pdo->prepare(
    "SELECT *
     FROM appointments
     WHERE patient_id = ?
     ORDER BY appointment_date DESC, appointment_time DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

// Fetch appointments the doctor is currently handling
$stmt = $pdo->query(
    "SELECT a.appointment_date, a.appointment_time, a.status,
            u.name AS patient_name
     FROM appointments a
     JOIN users u ON a.patient_id = u.id
     WHERE a.status IN ('pending', 'confirmed')
       AND a.appointment_date >= CURDATE()
     ORDER BY a.appointment_date ASC, a.appointment_time ASC"
);
$doctorAppointments = $stmt->fetchAll();

$pageTitle = 'Patient Dashboard';
$homePrefix = '../';
$assetPrefix = '../';

require __DIR__ . '/../includes/header.php';

$statusClasses = [
    'pending' => 'warning',
    'confirmed' => 'success',
    'rejected' => 'danger',
    'cancelled' => 'secondary',
    'completed' => 'primary'
];
?>

<!-- Dashboard heading -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h2">Patient Dashboard</h1>
        <p class="text-secondary mb-0">
            Welcome, <?= e($_SESSION['name']) ?>.
        </p>
    </div>

    <a class="btn btn-primary" href="book.php">
        + Book Appointment
    </a>
</div>

<!-- Doctor's current appointments -->
<div class="card p-3 p-md-4 mb-4">
    <h2 class="h4 mb-1">Doctor's Current Appointments</h2>
    <p class="text-secondary">
        Upcoming appointments that are pending or confirmed.
    </p>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctorAppointments as $a): ?>
                    <tr>
                        <td><?= e($a['appointment_date']) ?></td>
                        <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                        <td><?= e($a['patient_name']) ?></td>
                        <td>
                            <span class="badge text-bg-<?= e($statusClasses[$a['status']] ?? 'secondary') ?>">
                                <?= e(ucfirst($a['status'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$doctorAppointments): ?>
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">
                            No upcoming pending or confirmed appointments.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Patient's own bookings -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h2 class="h4 mb-0">My Bookings</h2>
</div>

<div class="card p-3 p-md-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= e($a['appointment_date']) ?></td>
                        <td><?= e(substr($a['appointment_time'], 0, 5)) ?></td>
                        <td><?= e($a['reason']) ?></td>
                        <td>
                            <span class="badge text-bg-<?= e($statusClasses[$a['status']] ?? 'secondary') ?>">
                                <?= e(ucfirst($a['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (
                                in_array($a['status'], ['pending', 'confirmed'], true)
                                && $a['appointment_date'] >= date('Y-m-d')
                            ): ?>
                                <form method="post" action="cancel.php" class="d-inline">
                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= e(csrf_token()) ?>">
                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int)$a['id'] ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            data-confirm="Cancel this appointment?">
                                        Cancel
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-secondary">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$appointments): ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">
                            You have not booked any appointments yet.
                            <br>
                            <a href="book.php" class="btn btn-primary btn-sm mt-3">
                                Book Your First Appointment
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
