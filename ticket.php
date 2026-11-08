<?php
/**
 * Public ticket page. Anyone with the link can open it; the 128-bit random
 * token in the URL is what makes the link unguessable.
 */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$token = (string) ($_GET['t'] ?? '');
$guest = preg_match('/^[a-f0-9]{32}$/', $token) ? find_guest_by_token(db(), $token) : null;

if ($guest === null) {
    http_response_code(404);
}

$ticketUrl = $guest ? app_url('ticket.php?t=' . $guest['token']) : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= $guest ? e($guest['event_name']) . ' ticket' : 'Ticket not found' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="center-page ticket-page">
<?php if ($guest === null): ?>
    <main class="login-card">
        <h1>Ticket not found</h1>
        <p class="muted">Check the link you were sent, or contact the organiser.</p>
    </main>
<?php else: ?>
    <main class="ticket">
        <header class="ticket-head">
            <p class="ticket-label">Admission ticket</p>
            <h1><?= e($guest['event_name']) ?></h1>
            <p><?= e($guest['venue']) ?></p>
            <p><?= e(format_event_date($guest['starts_at'])) ?></p>
        </header>

        <div class="ticket-body">
            <div class="qr" id="qr" data-value="<?= e($ticketUrl) ?>" role="img" aria-label="QR code for this ticket"></div>
            <p class="ticket-code"><?= e(format_ticket_code($guest['code'])) ?></p>

            <dl class="ticket-meta">
                <div><dt>Guest</dt><dd><?= e($guest['full_name']) ?></dd></div>
                <div><dt>Ticket</dt><dd><span class="pill pill-<?= e($guest['ticket_type']) ?>"><?= $guest['ticket_type'] === 'vip' ? 'VIP' : 'Regular' ?></span></dd></div>
                <?php if ($guest['checked_in_at']): ?>
                    <div><dt>Status</dt><dd>Checked in at <?= e(format_time($guest['checked_in_at'])) ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>

        <footer class="ticket-foot no-print">
            <p class="muted small">Show this QR code at the entrance. Keep it private: it admits one person.</p>
            <div class="ticket-actions">
                <button type="button" class="btn" onclick="window.print()">Print</button>
                <a class="btn btn-whatsapp" target="_blank" rel="noopener"
                   href="https://wa.me/?text=<?= rawurlencode('My ticket for ' . $guest['event_name'] . ': ' . $ticketUrl) ?>">Send on WhatsApp</a>
            </div>
        </footer>
    </main>
    <script src="assets/vendor/qrcode-generator.js"></script>
    <script src="assets/js/ticket.js"></script>
<?php endif; ?>
</body>
</html>
