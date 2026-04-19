/* admin-kit — shared admin JS
   Handles: mobile side nav · record toggle · search filter · drag-sort · save indicator · media library · copy-URL · confirm delete
*/
(() => {
  'use strict';
  const $  = (sel, el = document) => el.querySelector(sel);
  const $$ = (sel, el = document) => [...el.querySelectorAll(sel)];

  /* ---------------- Mobile side nav ------------------------------- */
  const topbar = document.querySelector('.ak-topbar');
  const side = document.querySelector('.ak-side');
  if (topbar && side) {
    topbar.addEventListener('click', (e) => {
      if (e.offsetX < 52 && window.innerWidth < 900) side.classList.toggle('is-open');
    });
    document.addEventListener('click', (e) => {
      if (!side.contains(e.target) && !topbar.contains(e.target)) side.classList.remove('is-open');
    });
  }

  /* ---------------- Record list: toggle / search / delete / sort -- */
  $$('.ak-record').forEach(wireRecord);
  const addBtn = $('#addRecord');
  if (addBtn) addBtn.addEventListener('click', addRecord);
  const search = $('#recordSearch');
  if (search) search.addEventListener('input', debounce(filterRecords, 120));

  function wireRecord(rec) {
    const head = rec.querySelector('[data-toggle]');
    const toggleBtn = rec.querySelector('[data-toggle-btn]');
    const delBtn = rec.querySelector('[data-delete]');
    const deletedInput = rec.querySelector('[data-deleted]');
    if (head) head.addEventListener('click', (e) => {
      if (e.target.closest('button,input,select,a,textarea')) return;
      rec.classList.toggle('is-open');
      if (toggleBtn) toggleBtn.textContent = rec.classList.contains('is-open') ? 'Згорнути' : 'Редагувати';
    });
    if (toggleBtn) toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      rec.classList.toggle('is-open');
      toggleBtn.textContent = rec.classList.contains('is-open') ? 'Згорнути' : 'Редагувати';
    });
    if (delBtn) delBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (rec.classList.contains('is-deleted')) {
        rec.classList.remove('is-deleted');
        deletedInput && (deletedInput.value = '');
        delBtn.textContent = 'Видалити';
        return;
      }
      if (!confirm('Позначити запис як видалений? Це не збережеться до «Зберегти».')) return;
      rec.classList.add('is-deleted');
      rec.classList.remove('is-open');
      deletedInput && (deletedInput.value = '1');
      delBtn.textContent = 'Відновити';
    });
    // Sync title/meta preview from first input when editing
    rec.querySelectorAll('input[type=text], textarea, select').forEach(inp => {
      inp.addEventListener('input', debounce(() => updateRecordPreview(rec), 200));
    });
  }

  function updateRecordPreview(rec) {
    const inputs = rec.querySelectorAll('.ak-record__body input[type=text], .ak-record__body select, .ak-record__body textarea');
    const values = [...inputs].slice(0, 4).map(i => i.value).filter(Boolean);
    const titleEl = rec.querySelector('.ak-record__title');
    const metaEl  = rec.querySelector('.ak-record__meta');
    if (values.length && titleEl) titleEl.textContent = values[0] || '(без назви)';
    if (metaEl) metaEl.textContent = values.slice(1).join(' · ');
  }

  function addRecord() {
    const tpl = $('#recordTemplate');
    const container = $('#records');
    if (!tpl || !container) return;
    const idx = container.querySelectorAll('.ak-record').length;
    const html = tpl.innerHTML.replace(/__I__/g, idx);
    container.insertAdjacentHTML('beforeend', html);
    const rec = container.lastElementChild;
    wireRecord(rec);
    rec.querySelector('input[type=text], textarea, select')?.focus();
    rec.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function filterRecords() {
    const q = search.value.trim().toLowerCase();
    $$('.ak-record').forEach(rec => {
      if (!q) { rec.style.display = ''; return; }
      const hay = rec.textContent.toLowerCase();
      rec.style.display = hay.includes(q) ? '' : 'none';
    });
  }

  /* ---------------- Drag-sort (minimal, no library) --------------- */
  const sortList = $('[data-sortable="1"]');
  if (sortList) enableSort(sortList);

  function enableSort(list) {
    let dragEl = null;
    list.querySelectorAll('.ak-record__drag').forEach(h => {
      const rec = h.closest('.ak-record');
      h.setAttribute('draggable', 'true');
      h.addEventListener('dragstart', (e) => {
        dragEl = rec;
        rec.classList.add('ak-sortable-drag');
        e.dataTransfer.effectAllowed = 'move';
      });
      h.addEventListener('dragend', () => {
        dragEl && dragEl.classList.remove('ak-sortable-drag');
        list.querySelectorAll('.ak-sortable-ghost').forEach(x => x.classList.remove('ak-sortable-ghost'));
        dragEl = null;
        reindexRecords(list);
      });
    });
    list.addEventListener('dragover', (e) => {
      if (!dragEl) return;
      e.preventDefault();
      const over = e.target.closest('.ak-record');
      if (!over || over === dragEl) return;
      const rect = over.getBoundingClientRect();
      const after = (e.clientY - rect.top) / rect.height > 0.5;
      over.classList.add('ak-sortable-ghost');
      if (after) over.parentNode.insertBefore(dragEl, over.nextSibling);
      else over.parentNode.insertBefore(dragEl, over);
    });
    list.addEventListener('dragleave', (e) => {
      const over = e.target.closest('.ak-record');
      if (over && over !== dragEl) over.classList.remove('ak-sortable-ghost');
    });
  }

  function reindexRecords(list) {
    list.querySelectorAll('.ak-record').forEach((rec, i) => {
      rec.dataset.idx = i;
      rec.querySelectorAll('[name^="records["]').forEach(inp => {
        inp.name = inp.name.replace(/records\[\d+\]/, 'records[' + i + ']');
      });
    });
  }

  /* ---------------- Save indicator + auto-save -------------------- */
  const form = $('#editForm');
  const indicator = $('#saveIndicator');
  let dirty = false;
  let saveTimer = null;

  if (form) {
    form.addEventListener('input', () => { dirty = true; setIndicator('dirty'); scheduleAutosave(); });
    window.addEventListener('beforeunload', (e) => {
      if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });
  }

  function setIndicator(state) {
    if (!indicator) return;
    indicator.classList.remove('is-saving', 'is-saved', 'is-error', 'is-dirty');
    const label = indicator.querySelector('span');
    const map = { saving: 'Зберігаю…', saved: 'Збережено', error: 'Помилка', dirty: 'Є зміни', idle: '' };
    if (label) label.textContent = map[state] || '';
    if (state === 'saving') indicator.classList.add('is-saving');
    if (state === 'saved')  indicator.classList.add('is-saved');
    if (state === 'error')  indicator.classList.add('is-error');
    if (state === 'dirty')  indicator.classList.add('is-dirty');
  }

  function scheduleAutosave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autosave, 30_000);
  }

  async function autosave() {
    if (!dirty || !form) return;
    setIndicator('saving');
    try {
      const data = new FormData(form);
      data.append('_ajax', '1');
      const r = await fetch('/admin/?action=autosave', { method: 'POST', body: data, credentials: 'same-origin' });
      if (!r.ok) throw new Error('http ' + r.status);
      dirty = false;
      setIndicator('saved');
      setTimeout(() => setIndicator('idle'), 1500);
    } catch (e) {
      console.warn('[autosave]', e);
      setIndicator('error');
    }
  }

  /* ---------------- Media library --------------------------------- */
  const mediaDrop = $('#mediaDrop');
  const mediaInput = $('#mediaInput');
  const mediaGrid = $('#mediaGrid');
  if (mediaDrop && mediaInput) {
    mediaDrop.addEventListener('click', () => mediaInput.click());
    mediaInput.addEventListener('change', () => uploadFiles(mediaInput.files));
    ['dragenter', 'dragover'].forEach(ev => mediaDrop.addEventListener(ev, (e) => {
      e.preventDefault(); mediaDrop.classList.add('is-drag');
    }));
    ['dragleave', 'drop'].forEach(ev => mediaDrop.addEventListener(ev, (e) => {
      e.preventDefault(); mediaDrop.classList.remove('is-drag');
    }));
    mediaDrop.addEventListener('drop', (e) => uploadFiles(e.dataTransfer.files));
  }
  if (mediaGrid) {
    mediaGrid.addEventListener('click', (e) => {
      const del = e.target.closest('[data-del]');
      const copy = e.target.closest('[data-url]');
      if (del) deleteMedia(del.dataset.del, del.closest('.ak-media-tile'));
      if (copy) {
        navigator.clipboard.writeText(copy.dataset.url).then(() => {
          copy.textContent = 'Скопійовано';
          setTimeout(() => copy.textContent = 'Копіювати URL', 1500);
        });
      }
    });
    mediaGrid.addEventListener('change', async (e) => {
      const alt = e.target.closest('[data-alt]');
      if (!alt) return;
      const fd = new FormData();
      fd.append('csrf', window.AK_CSRF || '');
      fd.append('id', alt.dataset.alt);
      fd.append('alt', alt.value);
      await fetch('/admin/?action=media-alt', { method: 'POST', body: fd, credentials: 'same-origin' });
    });
  }

  async function uploadFiles(files) {
    if (!files || !files.length) return;
    const fd = new FormData();
    fd.append('csrf', window.AK_CSRF || '');
    [...files].forEach(f => fd.append('files[]', f));
    try {
      const r = await fetch('/admin/?action=upload', { method: 'POST', body: fd, credentials: 'same-origin' });
      const d = await r.json();
      if (!d.ok) throw new Error('upload failed');
      location.reload();
    } catch (e) {
      alert('Помилка завантаження: ' + e.message);
    }
  }

  async function deleteMedia(id, tile) {
    if (!confirm('Видалити цей файл?')) return;
    const fd = new FormData();
    fd.append('csrf', window.AK_CSRF || '');
    fd.append('id', id);
    const r = await fetch('/admin/?action=media-delete', { method: 'POST', body: fd, credentials: 'same-origin' });
    const d = await r.json();
    if (d.ok) tile.remove();
    else alert('Не вдалось видалити');
  }

  /* ---------------- Discard-draft confirm ------------------------- */
  $$('[data-discard-draft]').forEach(a => a.addEventListener('click', (e) => {
    if (!confirm('Скинути всі незбережені зміни?')) e.preventDefault();
  }));

  /* ---------------- Utility ---------------------------------------- */
  function debounce(fn, ms) {
    let t; return function (...a) { clearTimeout(t); t = setTimeout(() => fn.apply(this, a), ms); };
  }
})();
