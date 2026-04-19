/**
 * Injected into the live site for logged-in admins.
 * Adds a floating "Edit" button bottom-right → opens the admin editor
 * with a return URL back to the current page.
 *
 * The site renders this script tag unconditionally for everyone (tiny, ~2KB)
 * and the script first checks /admin/?action=inline-check to see if the
 * current visitor is an admin. If yes, it paints the overlay.
 */
(() => {
  'use strict';
  if (window.__akInlineEditLoaded) return;
  window.__akInlineEditLoaded = true;

  const style = `
.ak-inline-bar {
  position: fixed; bottom: 24px; right: 24px; z-index: 999999;
  display: flex; gap: 8px; align-items: center;
  background: rgba(2, 40, 43, 0.95);
  color: #FEFEFE;
  padding: 8px 8px 8px 14px; border-radius: 999px;
  border: 1px solid rgba(216, 254, 1, 0.5);
  box-shadow: 0 14px 50px -10px rgba(0, 0, 0, 0.5);
  font: 600 13px/1 'Manrope', system-ui, sans-serif;
  transform: translateY(120%); opacity: 0; transition: transform .3s, opacity .3s;
}
.ak-inline-bar.is-shown { transform: translateY(0); opacity: 1; }
.ak-inline-bar__label { display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0.02em; }
.ak-inline-bar__label svg { width: 14px; height: 14px; }
.ak-inline-bar__draft { color: #E68620; font-weight: 700; font-size: 12px; }
.ak-inline-bar__btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 14px; border-radius: 999px;
  background: #D8FE01; color: #023936;
  font: 700 13px/1 'Manrope', system-ui, sans-serif; text-decoration: none;
  transition: transform .15s;
}
.ak-inline-bar__btn:hover { transform: translateY(-2px); text-decoration: none; }
.ak-inline-bar__btn svg { width: 14px; height: 14px; }
.ak-inline-bar__minor {
  padding: 6px 12px; border-radius: 999px;
  background: transparent; color: #FEFEFE;
  text-decoration: none; font-size: 12px; font-weight: 600;
  border: 1px solid rgba(254,254,254,0.2);
}
.ak-inline-bar__minor:hover { border-color: #FEFEFE; text-decoration: none; }
@media (max-width: 600px) {
  .ak-inline-bar { bottom: 16px; right: 16px; left: 16px; justify-content: space-between; padding: 6px; }
  .ak-inline-bar__label { display: none; }
}
@media print { .ak-inline-bar { display: none !important; } }
  `;

  function isDraftPreview() {
    const u = new URL(location.href);
    return u.searchParams.get('preview') === 'draft';
  }

  async function init() {
    try {
      const r = await fetch('/admin/?action=inline-check', { credentials: 'same-origin', cache: 'no-store' });
      if (!r.ok) return;
      const d = await r.json();
      if (!d || !d.admin) return;
      renderBar();
    } catch (e) { /* silent — visitors won't see anything */ }
  }

  function renderBar() {
    const s = document.createElement('style');
    s.textContent = style;
    document.head.appendChild(s);

    const bar = document.createElement('div');
    bar.className = 'ak-inline-bar';
    bar.innerHTML = `
      <span class="ak-inline-bar__label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4 H4 V20 H20 V13"/><path d="M18.5 2.5 A 2.121 2.121 0 0 1 21.5 5.5 L12 15 L8 16 L9 12 Z"/></svg>
        Адмін-режим${isDraftPreview() ? ' · <span class="ak-inline-bar__draft">чорновик</span>' : ''}
      </span>
      <a class="ak-inline-bar__btn" href="/admin/?action=html" title="Візуальний редактор">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5 V19 M5 12 H19"/></svg>
        Редагувати сторінку
      </a>
      <a class="ak-inline-bar__minor" href="/admin/" title="Відкрити панель">Панель</a>
    `;
    document.body.appendChild(bar);
    requestAnimationFrame(() => bar.classList.add('is-shown'));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
