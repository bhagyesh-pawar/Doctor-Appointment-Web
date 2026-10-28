
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$settings = $pdo->query(
    "SELECT * FROM clinic_settings WHERE id = 1"
)->fetch();

$pageTitle = 'CarePoint Clinic';
$homePrefix = '';
$assetPrefix = '';

require __DIR__ . '/includes/header.php';
?>

<section class="hero mb-4">
    <h1 class="display-5 fw-bold">
        Your Health, Our Priority
    </h1>

    <p class="lead mt-3">
        Book your doctor appointment easily and conveniently.
    </p>

    <?php if (empty($_SESSION['user_id'])): ?>

        
    <?php elseif ($_SESSION['role'] === 'doctor'): ?>

        <a href="doctor/dashboard.php"
           class="btn btn-light btn-lg">
            Doctor Dashboard
        </a>

    <?php else: ?>

        <a href="patient/dashboard.php"
           class="btn btn-light btn-lg">
            My Appointments
        </a>

    <?php endif; ?>
</section>

<div class="row g-4">

    <!-- Doctor Information -->
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h3>Doctor Information</h3>

            <h5 class="mt-3">
                <?= e($settings['doctor_name']) ?>
            </h5>

            <p class="text-secondary">
                <?= e($settings['specialization']) ?>
            </p>

            <p>
                <?= e($settings['qualification']) ?>
            </p>

            <h4>
                Consultation Fee:
                ₹<?= number_format(
                    (float)$settings['consultation_fee'],
                    2
                ) ?>
            </h4>
        </div>
    </div>

    <!-- Clinic Information -->
    <div class="col-md-6">
        <div class="card p-4 h-100">
            <h3>Clinic Information</h3>

            <p class="mt-3">
                <strong>Name:</strong>
                <?= e($settings['clinic_name']) ?>
            </p>

            <p>
                <strong>Address:</strong>
                <?= e($settings['clinic_address'] ?: 'To be updated') ?>
            </p>

            <p>
                <strong>Contact:</strong>
                <?= e($settings['clinic_phone'] ?: 'To be updated') ?>
            </p>

            <p>
                <strong>Timings:</strong>
                <?= e(substr($settings['opening_time'], 0, 5)) ?>
                –
                <?= e(substr($settings['closing_time'], 0, 5)) ?>
            </p>
        </div>
    </div>

</div>

<!-- Login Button Below Both Information Cards -->
<?php if (empty($_SESSION['user_id'])): ?>

    <div class="text-center mt-4">
        <p class="text-secondary mb-3">
            Already have an account?
        </p>

        <a href="login.php"
           class="btn btn-primary btn-lg px-5">
            Login
        </a>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
```