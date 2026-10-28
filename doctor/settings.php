<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('doctor');

$settings = $pdo->query("SELECT * FROM clinic_settings WHERE id=1")->fetch();
$error = '';
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    verify_csrf();
    $clinic = trim($_POST['clinic_name'] ?? '');
    $doctor = trim($_POST['doctor_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $address = trim($_POST['clinic_address'] ?? '');
    $phone = trim($_POST['clinic_phone'] ?? '');
    $fee = filter_var($_POST['consultation_fee'] ?? '', FILTER_VALIDATE_FLOAT);
    $opening = $_POST['opening_time'] ?? '';
    $closing = $_POST['closing_time'] ?? '';
    $slot = (int)($_POST['slot_minutes'] ?? 30);

    if ($clinic === '' || $doctor === '' || $specialization === '' ||
        $fee === false || $fee < 0 || $slot < 10 || $slot > 120 ||
        $opening === '' || $closing === '' || $opening >= $closing) {
        $error = 'Check the required fields, fee, working hours, and slot duration.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE clinic_settings
             SET clinic_name=?, doctor_name=?, specialization=?, qualification=?,
                 clinic_address=?, clinic_phone=?, consultation_fee=?,
                 opening_time=?, closing_time=?, slot_minutes=?
             WHERE id=1"
        );
        $stmt->execute([$clinic,$doctor,$specialization,$qualification,$address,$phone,$fee,$opening,$closing,$slot]);
        set_flash('Clinic profile updated.', 'success');
        redirect('settings.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_availability'])) {
    verify_csrf();
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            "UPDATE doctor_availability
             SET is_available=?, start_time=?, end_time=?
             WHERE day_of_week=?"
        );
        foreach ($days as $i => $day) {
            $available = isset($_POST['day_on'][$i]) ? 1 : 0;
            $start = $_POST['day_start'][$i] ?? '09:00';
            $end = $_POST['day_end'][$i] ?? '17:00';
            if ($start >= $end) {
                throw new RuntimeException($day . ': end time must be after start time.');
            }
            $stmt->execute([$available, $start, $end, $i]);
        }
        $pdo->commit();
        set_flash('Weekly availability saved.', 'success');
        redirect('settings.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Availability not saved. Check that end times are after start times.';
    }
}

$availability = $pdo->query("SELECT * FROM doctor_availability ORDER BY day_of_week")->fetchAll();
$pageTitle = 'Clinic Settings';
$homePrefix = '../';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h2 mb-1">Clinic Settings</h1>
<p class="text-secondary">Manage the information shown to patients.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card p-4 mb-4">
<h2 class="h5">Clinic Profile</h2>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="save_profile" value="1">
<div class="row g-3">
<?php
$fields = [
    ['clinic_name','Clinic Name','text'],
    ['doctor_name','Doctor Name','text'],
    ['specialization','Specialization','text'],
    ['qualification','Qualification','text'],
    ['clinic_address','Clinic Address','text'],
    ['clinic_phone','Clinic Phone','text'],
    ['consultation_fee','Consultation Fee (₹)','number'],
    ['opening_time','Default Opening Time','time'],
    ['closing_time','Default Closing Time','time'],
    ['slot_minutes','Slot Duration (minutes)','number']
];
foreach ($fields as [$key,$label,$type]):
?>
<div class="col-md-6">
<label class="form-label"><?= e($label) ?></label>
<input class="form-control" type="<?= e($type) ?>" name="<?= e($key) ?>"
       value="<?= e((string)$settings[$key]) ?>"
       <?= $type === 'number' ? 'min="0" step="0.01"' : '' ?> required>
</div>
<?php endforeach; ?>
</div>
<button class="btn btn-primary mt-3">Save Clinic Profile</button>
</form>
</div>

<div class="card p-4">
<h2 class="h5">Weekly Availability</h2>
<p class="text-secondary small">Set the days and hours when patients may request appointments.</p>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="save_availability" value="1">
<div class="table-responsive">
<table class="table">
<thead><tr><th>Day</th><th>Available</th><th>Start</th><th>End</th></tr></thead>
<tbody>
<?php foreach ($availability as $a): $i = (int)$a['day_of_week']; ?>
<tr>
<td><?= e($days[$i]) ?></td>
<td><input class="form-check-input" type="checkbox" name="day_on[<?= $i ?>]" value="1" <?= $a['is_available'] ? 'checked' : '' ?>></td>
<td><input class="form-control" type="time" name="day_start[<?= $i ?>]" value="<?= e(substr($a['start_time'],0,5)) ?>" required></td>
<td><input class="form-control" type="time" name="day_end[<?= $i ?>]" value="<?= e(substr($a['end_time'],0,5)) ?>" required></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<button class="btn btn-primary">Save Weekly Availability</button>
</form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
