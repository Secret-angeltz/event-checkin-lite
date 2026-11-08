<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$next = safe_next_url($_GET['next'] ?? $_POST['next'] ?? null);

if (is_staff()) {
    header('Location: ' . $next);
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $error = attempt_login((string) ($_POST['password'] ?? ''));
        if ($error === null) {
            header('Location: ' . $next);
            exit;
        }
    }
}

$notConfigured = empty(app_config()['staff']['password_hash']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff sign in | Event Check-in Lite</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="center-page">
<main class="login-card">
    <h1>Event Check-in <span>Lite</span></h1>
    <p class="muted">Staff sign in</p>

    <?php if ($notConfigured): ?>
        <p class="alert alert-error">No staff password is set. Run <code>php bin/hash-password.php</code> and add the hash to config.php.</p>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <p class="alert alert-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>
</main>
</body>
</html>
