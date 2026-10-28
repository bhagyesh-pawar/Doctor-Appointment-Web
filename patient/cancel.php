<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('patient');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare(
    "UPDATE appointments
     SET status = 'cancelled'
     WHERE id = ?
       AND patient_id = ?
       AND status IN ('pending', 'confirmed')
       AND appointment_date >= CURDATE()"
);
$stmt->execute([$id, $_SESSION['user_id']]);

set_flash(
    $stmt->rowCount() ? 'Appointment cancelled.' : 'Appointment could not be cancelled.',
    $stmt->rowCount() ? 'success' : 'warning'
);
redirect('dashboard.php');
