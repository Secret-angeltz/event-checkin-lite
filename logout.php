<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

// Sign-out is a POST with a CSRF token, so another site cannot log staff out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals(csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
    logout();
}

header('Location: login.php');
exit;
