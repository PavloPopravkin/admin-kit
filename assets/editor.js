/**
 * GrapesJS editor — loaded on /admin/?action=html. Seeds the canvas with the live
 * page (or existing override), exposes brand-specific blocks, saves the full HTML
 * document into a hidden textarea before form submit.
 */
(function () {
  if (!window.grapesjs) { console.warn('[editor] grapesjs not loaded'); return; }
  const AREA = document.getElementById('htmlArea');
  const RAW  = document.getElementById('htmlRaw');
  const FORM = AREA ? AREA.closest('form') : null;
  const MOUNT = document.getElementById('gjs');
  if (!AREA || !FORM || !MOUNT) return;

  const BRAND = {
    fonts: 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Unbounded:wght@700;800;900&display=swap',
    styles: '/assets/css/styles.css',
    sprite: '/assets/img/icons/sprite.svg',
    mainJs: '/assets/js/main.js',
    lang: 'uk',
    title: 'A·CAMP · Літні табори 2026',
    theme: '#023936',
  };

  const BRAND_BLOCKS = [
    { id: 'acamp-hero', label: 'Hero', category: 'A·CAMP', content: `
<section class="hero" id="top">
  <div class="wrap hero__grid">
    <div>
      <div class="hero__eyebrow-row"><span class="dot"></span><span class="label">ADVENTIST YOUTH UKRAINE</span></div>
      <h1 class="hero__headline"><span>Літні</span><span> табори</span><span class="year"> 2026</span></h1>
      <p class="hero__sub">Каталог адвентистських літніх таборів в Україні.</p>
      <div class="hero__cta">
        <a class="btn btn--primary" href="#camps">Дивитись каталог</a>
        <a class="btn btn--secondary" href="#about">Про A·CAMP</a>
      </div>
    </div>
  </div>
</section>` },
    { id: 'acamp-section', label: 'Section', category: 'A·CAMP', content: `
<section class="section"><div class="wrap">
  <div class="section-head"><span class="eyebrow">EYEBROW</span><h2>Заголовок</h2><p class="section-lede">Опис секції.</p></div>
  <p>Контент…</p>
</div></section>` },
    { id: 'acamp-stats', label: 'Stats', category: 'A·CAMP', content: `
<div class="wrap"><div class="stats">
  <div class="stat"><div class="stat__value">13</div><div class="stat__label">таборів</div></div>
  <div class="stat"><div class="stat__value">3</div><div class="stat__label">конференцій</div></div>
  <div class="stat"><div class="stat__value">Чр–Вер</div><div class="stat__label">сезон</div></div>
</div></div>` },
    { id: 'acamp-cat', label: 'Category circle', category: 'A·CAMP', content: `
<a href="#" class="cat-card">
  <svg class="cat-card__icon" style="width:72px;height:72px" aria-hidden="true"><use href="${BRAND.sprite}#ic-tent"/></svg>
  <span class="cat-card__title">ДИТЯЧІ</span>
  <span class="cat-card__sub">7–15 років</span>
  <span class="cat-card__count">0 таборів</span>
</a>` },
    { id: 'acamp-camp', label: 'Camp row', category: 'A·CAMP', content: `
<div class="camp-row">
  <div class="dates">1-7 липня</div>
  <div class="name">Назва табору<em>Відділ</em></div>
  <div class="conf">Конференція</div>
  <div class="place">Місце проведення</div>
  <div class="lodging"><svg style="width:24px;height:24px" aria-hidden="true"><use href="${BRAND.sprite}#ic-house"/></svg></div>
  <div class="resp"><strong>Відповідальний</strong><a href="tel:+380000000000">+380 00 000 00 00</a></div>
  <div class="age">10-15</div>
</div>` },
    { id: 'acamp-btn-primary', label: 'Button primary', category: 'A·CAMP', content: `<a class="btn btn--primary" href="#">Кнопка</a>` },
    { id: 'acamp-btn-orange',  label: 'Button orange',  category: 'A·CAMP', content: `<a class="btn btn--orange" href="#">Кнопка</a>` },
    { id: 'acamp-pill', label: 'Sticker pill', category: 'A·CAMP', content: `<span class="sticker--pill">A·CAMP</span>` },
    { id: 'acamp-submit', label: 'Submit CTA', category: 'A·CAMP', content: `
<section class="section section--tight"><div class="wrap"><div class="submit-band">
  <div><h2>Проводиш табір у 2026?</h2><p>Заповни форму — твій табір зʼявиться в каталозі.</p></div>
  <a class="btn btn--orange" href="#">Додати табір</a>
</div></div></section>` },
    { id: 'acamp-arc', label: 'Archive card', category: 'A·CAMP', content: `
<a class="archive-card" href="#" target="_blank">
  <span class="archive-card__year">2025</span>
  <span class="archive-card__meta">
    <span class="archive-card__title">Літні табори 2025</span>
    <span class="archive-card__action">Відкрити PDF</span>
  </span>
</a>` },
    { id: 'acamp-contact', label: 'Contact card', category: 'A·CAMP', content: `
<div class="contact-card">
  <span class="contact-card__role">Координатор</span>
  <span class="contact-card__name">Імʼя Прізвище</span>
  <div class="contact-card__list">
    <a href="mailto:email@example.com">email@example.com</a>
    <a href="tel:+380000000000">+380 00 000 00 00</a>
  </div>
</div>` },
  ];

  const editor = grapesjs.init({
    container: '#gjs',
    height: '70vh',
    width: 'auto',
    fromElement: false,
    storageManager: false,
    avoidInlineStyle: true,
    plugins: ['gjs-blocks-basic', 'grapesjs-preset-webpage'],
    pluginsOpts: {
      'gjs-blocks-basic':        { flexGrid: true, addBasicStyle: true, category: 'Basic' },
      'grapesjs-preset-webpage': { modalImportTitle: 'Import HTML', modalImportButton: 'Import', modalImportLabel: 'Paste HTML:' },
    },
    canvas: { styles: [BRAND.fonts, BRAND.styles] },
    deviceManager: {
      devices: [
        { name: 'Desktop', width: '' },
        { name: 'Tablet',  width: '900px',  widthMedia: '992px' },
        { name: 'Mobile',  width: '390px',  widthMedia: '480px' },
      ],
    },
  });

  const bm = editor.BlockManager;
  BRAND_BLOCKS.forEach(b => bm.add(b.id, {
    label: b.label, category: b.category, content: b.content,
    media: '<svg viewBox="0 0 24 24" width="24" height="24"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#D8FE01" stroke-width="2" fill="none"/><path d="M8 10 H16 M8 14 H13" stroke="#D8FE01" stroke-width="2" stroke-linecap="round" fill="none"/></svg>',
  }));

  // Ensure the canvas iframe has brand bg + styles.css loaded + color fallbacks.
  // Re-fires on load, device switch, and component mount.
  function injectCanvasBg() {
    try {
      const frame = editor.Canvas.getFrameEl();
      if (!frame || !frame.contentDocument || !frame.contentDocument.head) return;
      const doc = frame.contentDocument;

      // Ensure Google Fonts is loaded
      if (!doc.getElementById('ak-canvas-fonts')) {
        const l = doc.createElement('link');
        l.id = 'ak-canvas-fonts'; l.rel = 'stylesheet';
        l.href = BRAND.fonts;
        doc.head.appendChild(l);
      }
      // Ensure site stylesheet is loaded (defensive — canvas.styles may not always stick)
      if (!doc.getElementById('ak-canvas-site-css')) {
        const l = doc.createElement('link');
        l.id = 'ak-canvas-site-css'; l.rel = 'stylesheet';
        l.href = BRAND.styles;
        doc.head.appendChild(l);
      }

      // Color fallback overrides — always LAST in head so they win cascade
      let s = doc.getElementById('ak-canvas-bg');
      if (!s) {
        s = doc.createElement('style');
        s.id = 'ak-canvas-bg';
        doc.head.appendChild(s);
      } else {
        // move to end of head so it beats anything appended by GrapesJS later
        s.parentNode.appendChild(s);
      }
      s.textContent = CANVAS_OVERRIDES_CSS;
    } catch (e) { console.warn('[editor] canvas bg inject failed', e); }
  }
  editor.on('load', injectCanvasBg);
  editor.on('canvas:frame:load', injectCanvasBg);
  editor.on('component:mount', () => setTimeout(injectCanvasBg, 50));
  setTimeout(injectCanvasBg, 600);
  setTimeout(injectCanvasBg, 2500);
  setTimeout(injectCanvasBg, 5000);

  // Overrides baked into every canvas session. These override site styles that misbehave
  // inside the editor iframe (mostly JS-driven animations like .reveal and cross-browser
  // var resolution quirks in Safari).
  const CANVAS_OVERRIDES_CSS = `
    html, body { background-color: #023936 !important; color: #FEFEFE !important; }
    body { font-family: 'Manrope', system-ui, sans-serif; min-height: 100vh; margin: 0; }
    #wrapper, [data-gjs-type="wrapper"] { background: transparent !important; min-height: 100vh; color: #FEFEFE; }
    .reveal, .reveal.is-visible { opacity: 1 !important; transform: none !important; visibility: visible !important; transition: none !important; }
    #wrapper h1, #wrapper h2, #wrapper h3, #wrapper h4, #wrapper h5, #wrapper h6 { color: #FEFEFE; }
    #wrapper p, #wrapper li, #wrapper span, #wrapper em, #wrapper strong, #wrapper td, #wrapper th { color: inherit; }
    #wrapper a { color: #D8FE01; }
    #wrapper .hero__headline, #wrapper .hero__sub, #wrapper .section-head h2, #wrapper .bucket-head__title, #wrapper .stat__value { color: #FEFEFE; }
    #wrapper .year, #wrapper .eyebrow, #wrapper .lime, #wrapper .hero__eyebrow-row .label, #wrapper .section-head .accent { color: #D8FE01; }
    #wrapper .orange, #wrapper .cat-card__icon, #wrapper .bucket-head__icon { color: #E68620; }
    #wrapper .btn--primary { background: #D8FE01; color: #023936; }
    #wrapper .btn--orange { background: #E68620; color: #FEFEFE; }
    #wrapper .btn--secondary { color: #FEFEFE; border-color: rgba(254,254,254,.22); }
    ::selection { background: #D8FE01; color: #023936; }
  `;

  function loadIntoCanvas(fullHtml) {
    const bodyMatch = fullHtml.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
    const styleTags = [...fullHtml.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/gi)].map(m => m[1]).join('\n');
    const body = bodyMatch ? bodyMatch[1] : fullHtml;
    const bodyClean = body
      .replace(/<svg\s+xmlns[^>]*style="display:none"[\s\S]*?<\/svg>/, '')
      .replace(/<script[\s\S]*?<\/script>/g, '');
    editor.setComponents(bodyClean);
    // setStyle replaces the editor CSS — MUST include our overrides too, otherwise they get wiped
    editor.setStyle((styleTags || '') + '\n' + CANVAS_OVERRIDES_CSS);
  }

  // AREA is pre-populated server-side (see html-editor.php). Synchronous load.
  const starter = (AREA.value || '').trim();
  if (starter.length > 100) {
    console.log('[editor] seeding canvas from pre-populated textarea, size=', starter.length);
    loadIntoCanvas(starter);
    if (RAW) RAW.value = starter;
  } else {
    console.warn('[editor] textarea empty, falling back to fetch');
    fetch('/?preview=raw&_=' + Date.now(), { credentials: 'same-origin' })
      .then(r => r.text()).then(t => { loadIntoCanvas(t); if (RAW) RAW.value = t; })
      .catch(err => console.warn('[editor] seed fetch failed', err));
  }

  function serializeFullHtml() {
    const html = editor.getHtml();
    const css  = editor.getCss();
    return `<!doctype html>
<html lang="${BRAND.lang}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>${BRAND.title}</title>
<meta name="theme-color" content="${BRAND.theme}">
<link rel="stylesheet" href="${BRAND.fonts}">
<link rel="stylesheet" href="${BRAND.styles}">
<style>${css}</style>
</head>
<body id="top">
${html}
<script src="${BRAND.mainJs}" defer></script>
</body>
</html>`;
  }

  FORM.addEventListener('submit', () => { AREA.value = serializeFullHtml(); });
  editor.on('update', () => { if (RAW && RAW.getAttribute('data-live') === '1') RAW.value = serializeFullHtml(); });

  document.getElementById('revertDefault')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!confirm('Очистити канвас і завантажити стандартну сторінку?')) return;
    const starter = document.getElementById('htmlStarter');
    if (starter && starter.value.trim().length > 100) {
      loadIntoCanvas(starter.value);
      return;
    }
    fetch('/?preview=raw&_=' + Date.now(), { credentials: 'same-origin' })
      .then(r => r.text()).then(t => loadIntoCanvas(t));
  });

  const rawDetails = document.getElementById('rawDetails');
  rawDetails?.addEventListener('toggle', () => {
    if (rawDetails.open && RAW) { RAW.value = serializeFullHtml(); RAW.setAttribute('data-live', '1'); }
    else if (RAW) RAW.removeAttribute('data-live');
  });
})();
