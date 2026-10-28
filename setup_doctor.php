<?php
// Run once locally, create the doctor account, then DELETE this file.
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $count === 0) {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        $message = 'Enter a name, valid email, and password of at least 12 characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'doctor')"
            );
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $message = 'Doctor account created. Delete setup_doctor.php now, then log in.';
            $count = 1;
        } catch (PDOException $e) {
            $message = $e->getCode() === '23000'
                ? 'That email is already registered.'
                : 'Could not create the doctor account.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Doctor Account Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
<div class="card p-4 mx-auto" style="max-width:560px">
<h2>Initial Doctor Account Setup</h2>
<p>Create the single clinic doctor's account once.</p>
<?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
<?php if ($count === 0): ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label">Doctor Name</label><input class="form-control" name="name" required></div>
<div class="mb-3"><label class="form-label">Doctor Email</label><input class="form-control" type="email" name="email" required></div>
<div class="mb-3"><label class="form-label">Password (at least 12 characters)</label><input class="form-control" type="password" name="password" minlength="12" required></div>
<button class="btn btn-primary">Create Doctor Account</button>
</form>
<?php else: ?>
<p>A doctor account already exists. Delete this setup file now.</p>
<a href="login.php">Go to login</a>
<?php endif; ?>
</div>
</div>
</body>
</html>
