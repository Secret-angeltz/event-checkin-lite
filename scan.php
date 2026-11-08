<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_staff_page();

$event = current_event(db());

render_header('Scan tickets', 'scan', $event);
?>
<main class="container scan-page" id="scanner" data-event-id="<?= $event ? (int) $event['id'] : '' ?>">
    <?php if ($event === null): ?>
        <section class="panel empty-state"><h1>No events yet</h1></section>
    <?php else: ?>
        <section class="event-head">
            <div>
                <h1>Scan tickets</h1>
                <p class="muted"><?= e($event['name']) ?> &middot; <?= e($event['venue']) ?></p>
            </div>
        </section>

        <div class="scan-layout">
            <section class="panel">
                <div id="reader" class="reader"></div>
                <div class="reader-actions">
                    <button type="button" class="btn btn-primary" id="cameraBtn">Start camera</button>
                    <p class="muted small" id="cameraHint">The camera needs HTTPS (or localhost) and permission from the browser.</p>
                </div>

                <form id="manualForm" class="manual-form" autocomplete="off">
                    <label for="manualCode">Or type the code on the ticket</label>
                    <div class="inline">
                        <input id="manualCode" name="code" placeholder="e.g. K7MP-4XQ2" maxlength="40" autocapitalize="characters">
                        <button type="submit" class="btn">Check in</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <div class="result result-idle" id="result" role="status" aria-live="assertive">
                    <p class="result-title">Ready to scan</p>
                    <p class="result-detail">Point the camera at a ticket QR code.</p>
                </div>
                <h2 class="panel-title">This session</h2>
                <ul class="arrivals" id="scanLog"><li class="muted">No scans yet.</li></ul>
            </section>
        </div>
    <?php endif; ?>
</main>
<?php render_footer(['assets/vendor/html5-qrcode.min.js', 'assets/js/scan.js']); ?>
