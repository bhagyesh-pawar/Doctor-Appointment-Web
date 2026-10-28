
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

if (!empty($_SESSION['user_id'])) {
    redirect(
        $_SESSION['role'] === 'doctor'
        ? 'doctor/dashboard.php'
        : 'patient/dashboard.php'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'doctor') {
            redirect('doctor/dashboard.php');
        }

        redirect('patient/dashboard.php');

    } else {
        $error = 'Invalid email address or password.';
    }
}

$pageTitle = 'Login';
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
    <div class="col-lg-5">

        <div class="card p-4 p-md-5">

            <h2>Welcome Back</h2>

            <p class="text-secondary">
                Log in to your clinic account.
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
                    <label class="form-label">Email Address</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>

                <button type="submit"
                        class="btn btn-primary w-100">
                    Login
                </button>

            </form>

            <p class="mt-3 mb-3">
            New patient?
            <a href="register.php">Create an account</a>
        </p>

        

        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
