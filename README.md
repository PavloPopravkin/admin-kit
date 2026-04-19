# admin-kit

Drop-in CMS package for brief.croscor.com auto-built PHP sites. Everything the client needs to edit a site — no code per project.

Ships with:

- Dashboard with KPIs, per-type cards, activity log
- Auto-built CRUD forms from `types.php` (search, drag-sort, inline editing, auto-save, inline validation)
- GrapesJS visual editor with brand blocks, device preview, canvas-brand theming, Safari fixes
- Media library (drag-drop upload, gallery, alt-text, copy-URL, delete)
- Draft / publish workflow with snapshots (keeps last 10)
- Inline-edit floating button on the live site for logged-in admins
- bcrypt login, CSRF, per-site dedicated PHP-FPM pool (see infra note below)

## Install into a new brief

From the brief folder root (the one that contains `site/`):

```bash
git clone https://github.com/PavloPopravkin/admin-kit site/admin/admin-kit
```

Or as submodule if you want to track upstream updates:

```bash
git submodule add https://github.com/PavloPopravkin/admin-kit site/admin/admin-kit
```

Or just copy from the canonical local path on Pavel's Mac:

```bash
cp -R ~/www/admin-kit <brief>/site/admin/admin-kit
```

Then create the four small per-site files described in `INSTALL.md`.

## Canonical location

- **On Pavel's Mac:** `~/www/admin-kit`
- **On GitHub:** https://github.com/PavloPopravkin/admin-kit

Updates go to both (push to GitHub, pull/copy to Mac).

## Structure

```
admin-kit/
  bootstrap.php              # entry router (?action=xxx dispatch)
  core/
    helpers.php              # e(), icon(), phone_href(), flash, human_time
    session.php              # sessions, CSRF, login/logout, activity log
    content.php              # live + draft + snapshots
    types.php                # types DSL + CRUD helpers
    media.php                # upload, index, delete, slugify
  views/
    login.php                # login form
    dashboard.php            # KPIs + cards + activity
    edit.php                 # auto-built form from types.php
    settings.php             # bundled settings tabs (tweak per-site)
    html-editor.php          # GrapesJS host + pre-populated starter HTML
    media-library.php        # drag-drop gallery
  handlers/
    save.php                 # handles type:X / settings:Y / html scopes
    autosave.php             # 30s ajax autosave
    publish.php              # draft → live (+ snapshot)
    discard.php              # remove draft
    upload.php               # media multipart
    media-delete.php
    media-alt.php
    inline-check.php         # returns {admin:bool} for the live-site overlay
  templates/
    layout.php               # admin chrome (nav, topbar, publish btn)
  assets/
    admin.css                # all admin styles
    admin.js                 # record CRUD, search, sort, autosave, media
    editor.js                # GrapesJS init + brand blocks (CUSTOMIZE per site)
    inline-edit.js           # floating Edit button on live site
    icons.svg                # ak-* sprite
  .htaccess
```

## What must be customized per site

1. `editor.js` — `BRAND` object (fonts/styles URL/title/theme/lang/sprite) and `BRAND_BLOCKS` array (custom drag-drop blocks using the site's CSS classes). Everything else is generic.
2. `views/settings.php` — bundled as A·CAMP reference; tweak tabs/fields for your site's settings needs. (Form for site-wide one-off fields like hero, contacts, brand colors — these aren't well-modeled as content types.)

## What the site must provide

See `INSTALL.md`.

## Server-side infra (one-time per server)

- Dedicated PHP-FPM pool running as the site's user
- nginx `index index.php` + PHP fastcgi handler
- `rsync --delete` must `chown` files back to site user after each deploy (otherwise draft.json writes fail)

The deploy.sh from brief.croscor.com's brief packages handles this automatically — see the template in this repo's `examples/deploy.sh`.
