<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_staff_page();

$pdo = db();
$event = current_event($pdo);
$events = list_events($pdo);

render_header('Dashboard', 'dashboard', $event);
?>
<main class="container" id="dashboard" data-event-id="<?= $event ? (int) $event['id'] : '' ?>" data-event-name="<?= $event ? e($event['name']) : '' ?>">
    <?php if ($event === null): ?>
        <section class="panel empty-state">
            <h1>No events yet</h1>
            <p>Run <code>php bin/migrate.php --seed</code> to load a demo event.</p>
        </section>
    <?php else: ?>
        <section class="event-head">
            <div>
                <h1><?= e($event['name']) ?></h1>
                <p class="muted"><?= e($event['venue']) ?> &middot; <?= e(format_event_date($event['starts_at'])) ?></p>
            </div>
            <?php if (count($events) > 1): ?>
                <form method="get" class="event-switch">
                    <label for="eventSelect" class="sr-only">Event</label>
                    <select id="eventSelect" name="event" onchange="this.form.submit()">
                        <?php foreach ($events as $item): ?>
                            <option value="<?= (int) $item['id'] ?>"<?= (int) $item['id'] === (int) $event['id'] ? ' selected' : '' ?>><?= e($item['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </section>

        <section class="cards" aria-label="Attendance">
            <article class="card">
                <h2>Checked in</h2>
                <p class="card-value"><span id="statCheckedIn">&ndash;</span><small> / <span id="statRegistered">&ndash;</span></small></p>
                <div class="progress" aria-hidden="true"><span id="statBar"></span></div>
                <p class="card-meta" id="statPercent">&nbsp;</p>
            </article>
            <article class="card">
                <h2>Still to arrive</h2>
                <p class="card-value" id="statRemaining">&ndash;</p>
                <p class="card-meta">Registered guests not yet scanned</p>
            </article>
            <article class="card">
                <h2>VIP guests</h2>
                <p class="card-value" id="statVip">&ndash;</p>
                <p class="card-meta">Arrived / registered</p>
            </article>
            <article class="card">
                <h2>Rejected scans</h2>
                <p class="card-value" id="statRejected">&ndash;</p>
                <p class="card-meta">Duplicate, unknown or wrong event</p>
            </article>
        </section>

        <div class="layout">
            <section class="panel">
                <div class="panel-head">
                    <h2 class="panel-title">Guests</h2>
                    <input type="search" id="guestSearch" placeholder="Search name, phone or code" aria-label="Search guests">
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Guest</th>
                            <th scope="col">Ticket</th>
                            <th scope="col">Code</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="sr-only">Actions</span></th>
                        </tr>
                        </thead>
                        <tbody id="guestRows">
                        <tr><td colspan="5" class="empty">Loading&hellip;</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <aside class="side">
                <section class="panel">
                    <h2 class="panel-title">Register a guest</h2>
                    <form id="guestForm" novalidate>
                        <div class="field">
                            <label for="full_name">Full name</label>
                            <input id="full_name" name="full_name" autocomplete="off" required>
                            <p class="field-error" data-error-for="full_name"></p>
                        </div>
                        <div class="field">
                            <label for="phone">Phone <span class="optional">(optional)</span></label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" placeholder="0712 345 678" autocomplete="off">
                            <p class="field-error" data-error-for="phone"></p>
                        </div>
                        <div class="field">
                            <label for="email">Email <span class="optional">(optional)</span></label>
                            <input id="email" name="email" type="email" autocomplete="off">
                            <p class="field-error" data-error-for="email"></p>
                        </div>
                        <div class="field">
                            <label for="ticket_type">Ticket type</label>
                            <select id="ticket_type" name="ticket_type">
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="guestSave">Register and create ticket</button>
                    </form>
                </section>

                <section class="panel">
                    <h2 class="panel-title">Latest arrivals</h2>
                    <ul class="arrivals" id="recentList"><li class="muted">Loading&hellip;</li></ul>
                </section>
            </aside>
        </div>
    <?php endif; ?>
</main>
<?php render_footer(['assets/js/dashboard.js']); ?>
