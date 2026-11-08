# Event Check-in Lite

A QR-code ticketing and door check-in system built with plain PHP, MySQL and vanilla JavaScript. Register guests, send each one a ticket link on WhatsApp, and scan them in at the door from any phone.

![Dashboard with live attendance](docs/dashboard.png)

This is a simplified, open-source version of an event ticketing platform I built for events in Tanzania. It shows how I handle a real-world flow end to end: unguessable ticket links, race-safe check-ins, an audit trail, and a scanner that works on a phone at a busy entrance.

## Features

- **Guest registration** with Tanzanian phone number validation (`0712 345 678`, `+255 712…` and other formats are normalised)
- **Ticket page per guest** with a printable QR code and a short backup code such as `K7MP-4XQ2`
- **WhatsApp sharing** of ticket links, from the dashboard or by the guest
- **Phone camera scanner** that reads QR codes in the browser, with a typed-code fallback
- **Clear door results**: green for welcome, amber for already checked in, red for unknown or wrong-event tickets
- **Race-safe check-in**: the guest row is locked during check-in, so two doors scanning the same ticket cannot both admit it
- **Audit log** of every scan, including rejected ones
- **Live dashboard**: checked in, still to arrive, VIP arrivals, rejected scans and latest arrivals, refreshed every 15 seconds
- Staff sign-in with a hashed password and lock-out after repeated failures
- Responsive layout with a hamburger menu on phones

| Door scanner | Guest ticket |
| --- | --- |
| ![Scanner showing a successful check-in](docs/scanner.png) | ![Ticket with QR code](docs/ticket.png) |

## Tech stack

| Layer | Technology |
| --- | --- |
| Back end | PHP 8.1+ (no framework), PDO |
| Database | MySQL 5.7+ / MariaDB 10.3+ (InnoDB, row locking) |
| Front end | HTML, CSS, vanilla JavaScript |
| Libraries | html5-qrcode (camera scanning), qrcode-generator (SVG QR codes), bundled in `assets/vendor` |
| Hosting | Any Apache host, including cPanel shared hosting |

## Getting started

1. **Clone the repository**
   ```bash
   git clone https://github.com/<your-username>/event-checkin-lite.git
   cd event-checkin-lite
   ```
2. **Create a database**
   ```sql
   CREATE DATABASE event_checkin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. **Add your configuration**
   ```bash
   cp config.example.php config.php
   php bin/hash-password.php
   ```
   Put your database details in `config.php` and paste the password hash under `staff`. `config.php` is in `.gitignore` and is never committed.
4. **Create the tables and load a demo event**
   ```bash
   php bin/migrate.php --seed
   ```
5. **Run it**
   ```bash
   php -S localhost:8000
   ```
   Open http://localhost:8000 and sign in with the password you chose.

Browsers only allow camera access on HTTPS or `localhost`. On a live server, enable SSL (free with AutoSSL on cPanel) before using the scanner on phones.

## How check-in works

```
Guest registered ──> token (32 hex chars, random) + short code (8 chars)
                         │
                         ▼
        ticket.php?t=<token>  ──>  QR code on the guest's phone or printout
                         │
          Staff scans QR or types the code at the door
                         │
                         ▼
        api/checkin.php ──> BEGIN
                            SELECT ... FOR UPDATE   (lock the guest row)
                            not found        -> "invalid"
                            other event      -> "wrong_event"
                            already in       -> "duplicate" (with the time)
                            otherwise        -> set checked_in_at, "ok"
                            write to checkin_log
                            COMMIT
```

Short codes use an alphabet without `0`, `O`, `1`, `I` or `L`, so staff do not misread them when typing.

## Project structure

```
event-checkin-lite/
├── api/
│   ├── checkin.php         check a ticket in
│   ├── guests.php          list, search and register guests
│   └── stats.php           live attendance numbers
├── assets/
│   ├── css/style.css
│   ├── js/                 common, dashboard, scan, ticket
│   └── vendor/             html5-qrcode, qrcode-generator
├── bin/
│   ├── hash-password.php   create the staff password hash
│   └── migrate.php         run pending SQL migrations
├── database/
│   ├── migrations/         numbered, additive SQL files
│   └── seed.php            demo event and guests
├── includes/
│   ├── auth.php            staff sign-in and lock-out
│   ├── bootstrap.php       config, session, shared requires
│   ├── db.php              PDO connection
│   ├── guests.php          all SQL, including the check-in transaction
│   ├── helpers.php         JSON responses, CSRF, escaping
│   ├── layout.php          shared page header and footer
│   └── validation.php      pure validation and parsing functions
├── tests/run.php           unit tests (no dependencies)
├── index.php               staff dashboard
├── scan.php                door scanner
├── ticket.php              public guest ticket
├── login.php / logout.php
└── config.example.php
```

## API

All endpoints need a signed-in staff session and return JSON. Write requests need the `X-CSRF-Token` header.

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `api/guests.php?event_id=1&q=amina` | List guests, optionally searching name, phone or code |
| POST | `api/guests.php?event_id=1` | Register a guest: `{ "full_name", "phone", "email", "ticket_type" }` |
| POST | `api/checkin.php?event_id=1` | Check in: `{ "value": "<QR text or typed code>" }` |
| GET | `api/stats.php?event_id=1` | Attendance totals, VIP numbers, rejected scans, latest arrivals |

A check-in always returns `200` with a `result` of `ok`, `duplicate`, `wrong_event` or `invalid`, because a rejected ticket is a normal outcome at the door, not a server error.

## Security

- Ticket links use 128-bit random tokens (`random_bytes`), so they cannot be guessed
- Staff password stored only as a bcrypt hash; five failed attempts lock sign-in for a minute
- Session ID regenerated on sign-in; cookies are `HttpOnly` and `SameSite=Lax`
- CSRF tokens on every write request and on sign-in and sign-out
- Real prepared statements everywhere; search input has `LIKE` wildcards escaped
- All guest data is rendered with `textContent` in JavaScript and `htmlspecialchars` in PHP
- Redirects after sign-in only go to paths inside the app
- `.htaccess` blocks direct access to `includes/`, `database/`, `bin/`, `tests/` and `config.php`

## Tests

```bash
php tests/run.php
```

34 checks covering phone normalisation, guest validation, reading ticket URLs and typed codes, rejection of bad input, and uniqueness of 500 generated codes.

## License

MIT. See [LICENSE](LICENSE). Bundled libraries keep their own licenses: see [assets/vendor/README.md](assets/vendor/README.md).

## Author

**Mwita Nyaimaga**, full-stack developer in Dar es Salaam, Tanzania.
[LinkedIn](https://linkedin.com/in/mwitanyaimaga)
