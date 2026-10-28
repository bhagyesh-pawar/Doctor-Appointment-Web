
<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'CarePoint Clinic';
$homePrefix = $homePrefix ?? '';
$assetPrefix = $assetPrefix ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?></title>

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

    <link
      href="<?= e($assetPrefix) ?>assets/style.css"
      rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark clinic-nav">
    <div class="container">

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse"
             id="navbarContent">

            <div class="navbar-nav ms-auto align-items-lg-center">

                <a class="nav-link"
                   href="<?= e($homePrefix) ?>index.php">
                    Home
                </a>

                <?php if (empty($_SESSION['user_id'])): ?>

                    <a class="nav-link"
                       href="<?= e($homePrefix) ?>login.php">
                        Login
                    </a>

                    <a class="btn btn-light btn-sm ms-lg-2"
                       href="<?= e($homePrefix) ?>register.php">
                        Patient Registration
                    </a>

                <?php else: ?>

                    <?php if ($_SESSION['role'] === 'doctor'): ?>

                        <a class="nav-link"
                           href="<?= e($homePrefix) ?>doctor/dashboard.php">
                            Doctor Dashboard
                        </a>

                    <?php else: ?>

                        <a class="nav-link"
                           href="<?= e($homePrefix) ?>patient/dashboard.php">
                            My Appointments
                        </a>

                    <?php endif; ?>

                    <a href="<?= $homePrefix ?>logout.php"
   class="btn btn-danger logout-btn ms-lg-2">
    <i class="bi bi-box-arrow-right"></i> Logout
</a>

                <?php endif; ?>

            </div>
        </div>
    </div>
</nav>

<main class="container py-4">

<?php show_flash(); ?>
