# Wright Parel — WordPress theme

Minimal monotone WooCommerce theme for [wrightparel.works](https://wrightparel.works).
The repository root *is* the theme folder: Hostinger deploys it as-is into
`public_html/wp-content/themes/wrightparel`.

## Layout

```
style.css            theme header + all styles (tokens at the top)
functions.php        theme setup, WooCommerce hooks, helpers
header.php footer.php front-page.php page.php index.php
inc/seed-content.php placeholder pages, products, menus ("Set up store" button / `wp wrightparel seed`)
assets/fonts         Inter + Instrument Serif (SIL OFL), self-hosted
assets/img           placeholder product and editorial images
assets/js/site.js    mobile menu + sticky header
```

## Deployment (Hostinger Git)

hPanel → **Websites → wrightparel.works → Advanced → Git**

1. **Connect with GitHub** and grant the Hostinger app access to this repository.
2. Repository: this repo. Branch: `main`.
3. Deploy directory: `public_html/wp-content/themes/wrightparel`.
4. Click **Deploy**. Every later push to `main` redeploys automatically.

Deploys replace the folder's files, so make theme changes here, not in
WordPress's theme editor.

### After a deploy, check the active theme

Hostinger's deploy replaces the theme folder, and WordPress falls back to a
default theme if the folder is missing while a request comes in. After any
deploy that changes files, open **Appearance → Themes** and confirm **Wright
Parel** is still active. If it is not, activate it and the site returns to
normal immediately.

New content is added by the dashboard button (see `inc/seed-content.php`),
which is safe to run more than once.

## Content access (WordPress REST API)

Content (products, pages, menus) is managed through the live site's API, not this repo.

1. WordPress → **Users → Add New**: create `automation` with role **Shop Manager**.
2. Log in as that user (or edit it as admin) → **Profile → Application Passwords**,
   name it `hoplite`, click **Add**, copy the generated password.
3. Store it as project secrets, never in this repo or in chat:
   `WP_SITE_URL` (e.g. `https://wrightparel.works`), `WP_USER` (`automation`),
   `WP_APP_PASSWORD` (the generated password).

Revoke it any time from the same Application Passwords screen.

This repository is public: never commit passwords, API keys or customer data.
