# The Bikers Ranger

Community website for The Bikers Ranger / Raja Kapcai tour: pit stop sign-ups,
the Port Rider directory, Aktiviti (events, gallery, contests, winners), Panas
Atas Jalan videos, Hall of Fame, member accounts and an admin panel.

Plain PHP 8 + MySQL. No framework and no Composer, so it runs on ordinary
cPanel shared hosting.

---

## 1. Server requirements

| Requirement | Notes |
|---|---|
| PHP **8.2 or newer** (8.3/8.4 recommended) | Select it in cPanel → *MultiPHP Manager* |
| PHP extensions: `pdo_mysql`, `mbstring`, `gd` (**with WebP**), `fileinfo`, `exif` | cPanel → *Select PHP Version* → Extensions. Uploaded photos are re-encoded to WebP, so GD must list WebP support |
| MySQL 5.7+ or MariaDB 10.4+ | `utf8mb4` |
| Apache with `mod_rewrite` | `mod_headers`, `mod_expires` and `mod_deflate` are used when present |
| HTTPS certificate | cPanel *AutoSSL* is fine |
| SSH or cPanel *Terminal* | Needed once, to run the migration and seed scripts |

Check GD's WebP support with: `php -r 'var_dump(gd_info()["WebP Support"] ?? false);'`

## 2. Folder layout

```
app/        PHP classes (Core, Controllers, Repositories, Services, Admin)
bin/        command-line scripts (migrate, seed, local dev router)
config/     site configuration (navigation, states, social links, mail)
database/   SQL migrations
public/     the web root: index.php, assets/, uploads/
routes/     URL → controller map (routes/web.php)
storage/    logs (must be writable)
views/      templates: layouts, partials (header.php, footer.php), components, pages
```

Only `public/` should be reachable from the web.

## 3. Deploying to cPanel

1. **Upload the code** outside `public_html`, e.g. `/home/USER/tbr`, using Git
   (cPanel → *Git Version Control*) or by uploading a zip. Do **not** upload
   `.env` from your computer.

2. **Point the domain at `public/`.** In cPanel → *Domains*, set the document
   root of the domain to `/home/USER/tbr/public`.

   If your host will not let you change the document root, upload the whole
   project *into* the document root instead. The `.htaccess` at the project
   root then forwards every request into `public/`, so `app/`, `.env` and the
   other folders stay unreachable. This is only a fallback: pointing the
   document root at `public/` is safer.

3. **Create the database.** In cPanel → *MySQL Databases*, create a database
   and a user, and give that user *ALL PRIVILEGES* on the database.

4. **Create `.env`.** Copy `.env.example` to `.env` and fill it in:

   ```ini
   APP_ENV=production      # anything else makes robots.txt block all crawlers
   APP_DEBUG=false         # true shows error details to visitors. Never on live.
   APP_URL=https://thebikersranger.com   # https, no trailing slash; used for canonical URLs and the sitemap

   DB_HOST=localhost
   DB_NAME=cpaneluser_tbr
   DB_USER=cpaneluser_tbr
   DB_PASS=...

   MAIL_DRIVER=mail
   MAIL_FROM=enquiry@hive-asia.com
   ```

   Then restrict it: `chmod 600 .env`.

5. **Create the tables and first content** (cPanel → *Terminal*):

   ```bash
   cd ~/tbr
   php bin/migrate.php                 # safe to re-run; only new migrations are applied
   ADMIN_EMAIL=you@hive-asia.com php bin/seed.php
   ```

   The seed script prints the admin password **once**. Store it in your
   password manager. You can also set it yourself with `ADMIN_PASSWORD=...`.

   The seed loads the copy from the approved design. The pit stop dates in
   it are **demo dates**: replace them with the real tour in *Admin → Pit
   Stop* before launch, and remove any other sample content you do not want.

6. **Folder permissions.** `storage/logs` and `public/uploads` must be
   writable by PHP (on cPanel they normally already are: `755` folders,
   owned by your account).

7. **Upload limits.** `public/.user.ini` raises PHP's upload limit to 10 MB
   so admins can upload photos straight from a phone. Some hosts ignore
   `.user.ini`. If large uploads fail, set `upload_max_filesize = 10M` and
   `post_max_size = 12M` in cPanel → *MultiPHP INI Editor*.

8. **Force HTTPS.** Turn on cPanel → *Domains* → *Force HTTPS Redirect*.
   Once the site is served over HTTPS it sends an HSTS header automatically.

9. **Check the launch.** Work through the list in section 7.

### Updating the live site later

```bash
cd ~/tbr
git pull                # or upload the changed files
php bin/migrate.php     # applies any new migrations
```

CSS/JS links carry a version stamp based on the file's modified time, so
browsers pick up changes straight away.

## 4. Email (password resets)

Password reset emails are sent from `MAIL_FROM` using PHP's `mail()`. To keep
them out of spam folders, the domain in `MAIL_FROM` (hive-asia.com) must allow
this server to send for it:

- **SPF:** add the web server to the domain's SPF record, e.g.
  `v=spf1 +a +mx include:<your-host's-spf> ~all`. cPanel → *Email Deliverability*
  shows the exact record your host needs.
- **DKIM:** enable it in cPanel → *Email Deliverability* and publish the key
  it gives you in the domain's DNS.
- If hive-asia.com's DNS or mail is managed elsewhere (e.g. Microsoft 365 or
  Google Workspace), ask whoever manages that DNS to add the records. If they
  cannot, send from an address on the site's own domain instead, such as
  `no-reply@thebikersranger.com`.

Send yourself a reset from `/lupa-kata-laluan` after launch to confirm it arrives.

## 5. Brand assets still to add

These files are referenced by the templates. Until they exist the site uses
text or placeholders.

| File (in `public/assets/img/`) | Used for | Format |
|---|---|---|
| `logo-tbr.svg` | Header and footer logo | SVG, light version for a dark background |
| `logo-raja-kapcai.svg` | Co-brand logo in the footer | SVG |
| `hero-rider.webp` | Rider cut-out beside the home sign-up form (desktop) | WebP with transparency, about 434×765 (upload 868×1530 for sharp retina) |
| `favicon.svg` | Browser tab icon (**placeholder** now) | Square SVG |
| `apple-touch-icon.png` | Home-screen icon (**placeholder** now) | 180×180 PNG |
| `og-default.jpg` | Link preview on WhatsApp/Facebook (**placeholder** now) | 1200×630 JPG |

Replace the file with the same name. No code changes are needed.

### Fonts

The CSS uses two font tokens at the top of `public/assets/css/app.css`:

```css
--font-display: "Oswald", "Arial Narrow", system-ui, sans-serif; /* headings, buttons */
--font-body: "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
```

To use the design's fonts, put the `.woff2` files in `public/assets/fonts/`
and add `@font-face` rules at the top of `app.css`:

```css
@font-face {
    font-family: "Brand Display";
    src: url("../fonts/brand-display-700.woff2") format("woff2");
    font-weight: 700;
    font-display: swap;
}
```

Then put the family name first in the matching token. Fonts are self-hosted
on purpose. The Content-Security-Policy only allows fonts from the site
itself, and it avoids sending visitors' IPs to a third party (PDPA).

## 6. Admin panel

Sign in at `/log-masuk` with the admin account, then go to `/admin`.

- **Pendaftaran:** pit stop bookings, with status changes and CSV export.
- **Ahli:** members. Suspending a member signs them out everywhere. Includes
  a WhatsApp export limited to members who opted in.
- **Kandungan:** pit stops, Port Rider listings, gallery albums and photos,
  contests, prizes and winners, videos, Hall of Fame, banners and home counters.
- **Tetapan:** editable site copy, social links and contact details.

More admins: sign up normally, then run in the database
`UPDATE users SET role = 'admin' WHERE email = '...';`

## 7. Launch checklist

- [ ] `.env` has `APP_ENV=production`, `APP_DEBUG=false` and an `https://` `APP_URL`
- [ ] `https://DOMAIN/.env`, `/app/`, `/storage/logs/php-error.log` all return 403/404
- [ ] `https://DOMAIN/robots.txt` shows `Disallow: /admin` and the sitemap line (not `Disallow: /`)
- [ ] `https://DOMAIN/sitemap.xml` lists the pages
- [ ] Demo pit stop dates replaced with the real tour
- [ ] Admin password stored safely; the seed output is gone from the terminal history
- [ ] Logos, rider image, favicon and share image uploaded (section 5)
- [ ] Test sign-up, login, pit stop booking and password reset on a phone
- [ ] Password reset email arrives in the inbox, not spam (section 4)
- [ ] Submit the sitemap in Google Search Console
- [ ] Share a page on WhatsApp to check the preview image and title
- [ ] Set up a daily database backup (cPanel → *Backup*, or JetBackup if your host has it) and include `public/uploads/`

## 8. Troubleshooting

- **"Alamak, ada masalah teknikal" page:** the full error is in
  `storage/logs/php-error.log`.
- **Every page 404 except the home page:** `mod_rewrite` is off, or the
  document root is not `public/`.
- **Uploads fail over ~2 MB:** see step 7 of section 3 (upload limits).
- **"Sesi telah tamat" after submitting a form:** the form sat open for too
  long, or cookies are blocked. Reload and resubmit.

## 9. Local development

```bash
cp .env.example .env    # set APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost:8000, MAIL_DRIVER=log
php bin/migrate.php && php bin/seed.php
php -S 127.0.0.1:8000 -t public bin/dev-router.php
```

`bin/dev-router.php` makes PHP's built-in server behave like the Apache
rules: it serves real files and sends everything else, including
`/sitemap.xml` and `/robots.txt`, to `index.php`. With `MAIL_DRIVER=log`,
emails are written to `storage/logs/mail.log` instead of being sent.

### Security notes for developers

- Every form needs `<?= Csrf::field() ?>`, and every POST handler calls `$this->verifyCsrf()`.
- Escape all output with `e()`. JSON-LD goes through the `Seo` class.
- Always pass query values as bound parameters to `Database::*`, never by joining strings.
- The CSP blocks inline `<script>`, inline `style=""` and third-party
  hosts. Put code in `public/assets/js` and styles in `app.css`. Allow new
  embeds in `app/Core/SecurityHeaders.php`.
