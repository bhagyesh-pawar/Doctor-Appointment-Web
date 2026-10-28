<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('patient');

$error = '';
$settings = $pdo->query("SELECT * FROM clinic_settings WHERE id=1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $date = $_POST['appointment_date'] ?? '';
    $time = $_POST['appointment_time'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    $dateObj = DateTime::createFromFormat('!Y-m-d', $date);
    $timeObj = DateTime::createFromFormat('!H:i', $time);

    if (!$dateObj || $dateObj->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
        $error = 'Choose a valid date today or later.';
    } elseif (!$timeObj || $timeObj->format('H:i') !== $time) {
        $error = 'Choose a valid appointment time.';
    } elseif ($reason === '' || strlen($reason) > 500) {
        $error = 'Enter a reason for the visit (maximum 500 characters).';
    } else {
        $day = (int)$dateObj->format('w'); // 0 Sunday through 6 Saturday
        $avStmt = $pdo->prepare(
            "SELECT * FROM doctor_availability
             WHERE day_of_week = ? AND is_available = 1"
        );
        $avStmt->execute([$day]);
        $availability = $avStmt->fetch();

        if (!$availability) {
            $error = 'The clinic is not available on that day.';
        } else {
            $start = '09:00';
	    $end = '17:00';
            $slotMinutes = max(1, (int)$settings['slot_minutes']);

            $chosen = DateTime::createFromFormat('!H:i', $time);
            $startObj = DateTime::createFromFormat('!H:i', $start);
            $endObj = DateTime::createFromFormat('!H:i', $end);

            $diff = ($chosen->getTimestamp() - $startObj->getTimestamp()) / 60;

            if ($time < $start || $time >= $end ||
                $diff < 0 || ((int)$diff % $slotMinutes) !== 0) {
                $error = 'Choose a slot within the clinic working hours.';
            } elseif ($date === date('Y-m-d') && $time <= date('H:i')) {
                $error = 'Choose a future time.';
            } else {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO appointments
                         (patient_id, appointment_date, appointment_time, reason)
                         VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $date,
                        $time . ':00',
                        $reason
                    ]);
                    set_flash('Appointment request submitted. It is pending until the doctor reviews it.', 'success');
                    redirect('dashboard.php');
                } catch (PDOException $e) {
                    $error = $e->getCode() === '23000'
                        ? 'That time slot is already booked. Please choose another.'
                        : 'Booking failed. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Book Appointment';
$homePrefix = '../';
$assetPrefix = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="mb-3">
    <a href="dashboard.php" class="btn btn-outline-secondary">
        &larr; 
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4 p-md-5">
            <h1 class="h2">Book an Appointment</h1>
            <p class="text-secondary">Choose a date, time, and reason for your visit.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label">Appointment Date</label>
                    <input class="form-control" type="date" name="appointment_date"
                           min="<?= e(date('Y-m-d')) ?>"
                           value="<?= e($_POST['appointment_date'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
    <label class="form-label">Appointment Time</label>
    <input
        class="form-control"
        type="time"
        name="appointment_time"
        min="09:00"
        max="17:00"
        step="<?= max(1, (int)$settings['slot_minutes']) * 60 ?>"
        value="<?= e($_POST['appointment_time'] ?? '') ?>"
        required
    >
    <div class="form-text">
        Available booking hours: 09:00 AM to 05:00 PM.
    </div>
</div>

                <div class="mb-3">
                    <label class="form-label">Reason for Visit</label>
                    <textarea class="form-control" name="reason" rows="3"
                              maxlength="500" required><?= e($_POST['reason'] ?? '') ?></textarea>
                </div>

                <button class="btn btn-primary">Submit Booking Request</button>
               
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
