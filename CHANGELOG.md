# admin-kit changelog

## v1.0.0 — 2026-04-19

Initial extract from acamp.croscor.com. Ships:

- Auto-router `bootstrap.php` dispatching `?action=...`
- Content types DSL (`types.php`), auto-built CRUD forms with search / drag-sort / autosave
- Dashboard with KPIs + activity log + per-type cards
- GrapesJS visual editor with brand blocks and the following canvas fixes:
  - Server-side pre-populate of starter HTML (no async fetch, synchronous)
  - Dual-channel style inject: `editor.setStyle()` + direct iframe `<head>` injection
  - `.reveal { opacity: 1 !important }` override so IntersectionObserver-driven reveals show up
  - Safari-safe selectors (no `:where()`, explicit color fallbacks for when CSS vars don't resolve)
  - `#wrapper` background transparent + body dark-green !important
  - Re-injection on `load` / `canvas:frame:load` / `component:mount` + 3 setTimeouts (600, 2500, 5000ms)
- Media library with drag-drop, alt-text auto-save, delete, copy-URL
- Draft/publish workflow with snapshots (keeps last 10)
- Inline-edit floating "Edit" button on live site for admins (via `/admin/?action=inline-check`)
- bcrypt login, CSRF, activity log, dedicated PHP-FPM pool pattern
