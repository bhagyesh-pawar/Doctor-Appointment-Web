<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('doctor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowed = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];

if (!in_array($status, $allowed, true)) {
    set_flash('Invalid appointment status.', 'danger');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

set_flash($stmt->rowCount() ? 'Appointment status updated.' : 'No change was made.', 'success');
redirect('dashboard.php');
