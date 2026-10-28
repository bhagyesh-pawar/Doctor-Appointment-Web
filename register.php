
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'doctor') {
        redirect('doctor/dashboard.php');
    }

    redirect('patient/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = 'Enter a valid name and email. Password must contain at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users
                (name, email, password, role, phone)
                VALUES (?, ?, ?, 'patient', ?)"
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $phone ?: null
            ]);

            set_flash(
                'Registration successful! Please log in.',
                'success'
            );

            redirect('login.php');

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'This email is already registered.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Patient Registration';
$homePrefix = '';
$assetPrefix = '';

require __DIR__ . '/includes/header.php';
?>

<div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary">
        &larr; 
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="card p-4 p-md-5">

            <h2 class="mb-2">Patient Registration</h2>

            <p class="text-secondary">
                Create your account to book a doctor appointment.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           maxlength="120"
                           required
                           value="<?= e($_POST['name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           maxlength="190"
                           required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel"
                           name="phone"
                           class="form-control"
                           maxlength="20"
                           value="<?= e($_POST['phone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           minlength="8"
                           required>
                    <div class="form-text">
                        Use at least 8 characters.
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary w-100">
                    Create Account
                </button>

            </form>

            <p class="mt-3 mb-0">
                Already registered?
                <a href="login.php">Login here</a>
            </p>

        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
