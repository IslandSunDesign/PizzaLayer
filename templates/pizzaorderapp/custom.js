/* ======================== NEW JAVASCRIPT ======================== */

/* +=== PZT: Mobile Order Navigation Logic ===+ */
jQuery(document).ready(function () {

    let tabOrder = ['home', 'crust', 'sauce', 'cheese', 'toppings', 'drizzle', 'slicing', 'order'];
    let currentTab = 0;
    let selections = { crust: null, sauce: null, cheese: null, drizzle: null, slicing: null, toppings: [] };

    let tabs = jQuery('.tab-btn');
    let dropdown = jQuery('#pztp-mobileorder-nav-dropdown');
    let contentArea = jQuery('#pztp-mobileorder-content');
    let preview = jQuery('.pztp-live-preview-placeholder');
    let orderSummary = jQuery('#order-summary');
    let toppingsCSV = jQuery('#toppings-csv');
    let nextBtn = jQuery('#next-btn');
    let prevBtn = jQuery('#prev-btn');

    function showTab(name) {
        jQuery('.tab-content').removeClass('active');
        jQuery('#tab-' + name).addClass('active');

        tabs.each(function () {
            let t = jQuery(this);
            t.toggleClass('active', t.data('tab') === name);
        });

        if (dropdown.length) dropdown.val(name);
        currentTab = tabOrder.indexOf(name);

        if (prevBtn.length) prevBtn.css('display', currentTab === 0 ? 'none' : 'inline-block');
        if (nextBtn.length) {
            nextBtn.text(name === 'home' ? 'Start Building my Pizza' : 'Next');
            nextBtn.css('display', name === 'order' ? 'none' : 'inline-block');
        }

        if (name === 'order') populateOrder();
    }

    tabs.on('click', function () {
        showTab(jQuery(this).data('tab'));
    });

    dropdown.on('change', function () {
        showTab(jQuery(this).val());
    });

    nextBtn.on('click', function () {
        showTab(tabOrder[currentTab + 1] || tabOrder[currentTab]);
    });

    prevBtn.on('click', function () {
        showTab(tabOrder[currentTab - 1] || tabOrder[currentTab]);
    });

    contentArea.on('click', '.add-remove-btn', function () {
        let btn = jQuery(this);
        let card = btn.closest('.option-card');
        let layer = card.data('layer');
        let title = card.data('title');

        if (layer === 'toppings') {
            card.toggleClass('selected');
            if (card.hasClass('selected')) {
                selections.toppings.push(title);
                btn.text('Remove');
            } else {
                selections.toppings = selections.toppings.filter(t => t !== title);
                btn.text('Add');
            }
            toppingsCSV.text('Toppings: ' + selections.toppings.join(','));
        } else {
            jQuery(`.option-card[data-layer="${layer}"]`)
                .removeClass('selected')
                .find('.add-remove-btn').text('Add');
            card.addClass('selected');
            btn.text('Remove');
            selections[layer] = title;
        }

        // --- Safe animation: only if both circle and preview exist
        const circle = card.find('.option-circle').first();
        const circleEl = circle.get(0);
        const previewEl = preview.get(0);

        if (!circleEl || !previewEl) {
            // Skip the animation gracefully if markup isn’t present
            return;
        }

        const clone = circle.clone();
        const r = circleEl.getBoundingClientRect();
        const p = previewEl.getBoundingClientRect();

        clone.css({
            position: 'fixed',
            left: r.left + 'px',
            top: r.top + 'px',
            width: r.width + 'px',
            height: r.height + 'px',
            transition: 'all 0.8s ease',
            pointerEvents: 'none',
            zIndex: 9999
        });

        jQuery('body').append(clone);

        requestAnimationFrame(function () {
            clone.css({
                left: (p.left + p.width / 2 - r.width / 2) + 'px',
                top: (p.top + p.height / 2 - r.height / 2) + 'px',
                transform: 'scale(0.2)',
                opacity: '0'
            });
        });

        setTimeout(function () {
            clone.remove();
        }, 820);
    });

    function populateOrder() {
        orderSummary.empty();
        toppingsCSV.text('');

        ['crust', 'sauce', 'cheese', 'drizzle', 'slicing'].forEach(function (l) {
            if (selections[l]) {
                let box = jQuery('<div>').addClass('order-box');
                let label = jQuery('<label>').text(l.charAt(0).toUpperCase() + l.slice(1));
                let input = jQuery('<input>').val(selections[l]).prop('readonly', true);
                box.append(label, input);
                orderSummary.append(box);
            }
        });

        toppingsCSV.text('Toppings: ' + selections.toppings.join(','));
    }

    showTab('home');
});

/* ============================================================
 * PizzaLayer template.js — UI Enhancements (Preview + Portions)
 * - Injects portion icon sprite (currentColor-driven)
 * - Adds half-left / half-right to all portion choosers
 * - Normalizes hidden inputs for portion/coverage fields
 * - Preview size controls (full / default / float) + float overlay
 * - "Go to tab" helper buttons
 * ============================================================ */
(function () {
  'use strict';

  /* -----------------------------
   * 0) Small helpers
   * ----------------------------- */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const on = (el, evt, fn, opts) => el && el.addEventListener(evt, fn, opts || false);

  function ensureEl(tag, attrs = {}, parent) {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
    if (parent) parent.appendChild(el);
    return el;
  }

  /* -----------------------------
   * 1) Inject portion icon sprite if missing
   *    (solves the "all black circles" by using currentColor)
   * ----------------------------- */
  function injectIconSprite() {
    if (document.getElementById('pz-portion-outline')) return; // already present (by id inside <defs>)
    const holder = document.createElement('div');
    holder.style.position = 'absolute';
    holder.style.left = '-9999px';
    holder.style.visibility = 'hidden';
    holder.innerHTML = `
<svg width="0" height="0" aria-hidden="true">
  <defs>
    <symbol id="pz-portion-outline" viewBox="0 0 100 100">
      <circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-width="8"></circle>
    </symbol>

    <symbol id="pz-portion-whole" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <circle cx="50" cy="50" r="46" fill="currentColor" opacity=".85"></circle>
    </symbol>

    <symbol id="pz-portion-half" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L50,4 A46,46 0 1 1 50,96 Z" fill="currentColor" opacity=".85"></path>
    </symbol>

    <symbol id="pz-portion-half-left" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L4,50 A46,46 0 1 1 96,50 Z" fill="currentColor" opacity=".85"></path>
    </symbol>

    <symbol id="pz-portion-half-right" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L96,50 A46,46 0 1 0 4,50 Z" fill="currentColor" opacity=".85"></path>
    </symbol>

    <symbol id="pz-portion-q-ul" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L50,4 A46,46 0 0 0 4,50 Z" fill="currentColor" opacity=".85"></path>
    </symbol>
    <symbol id="pz-portion-q-ur" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L96,50 A46,46 0 0 0 50,4 Z" fill="currentColor" opacity=".85"></path>
    </symbol>
    <symbol id="pz-portion-q-lr" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L50,96 A46,46 0 0 0 96,50 Z" fill="currentColor" opacity=".85"></path>
    </symbol>
    <symbol id="pz-portion-q-ll" viewBox="0 0 100 100">
      <use href="#pz-portion-outline"></use>
      <path d="M50,50 L4,50 A46,46 0 0 0 50,96 Z" fill="currentColor" opacity=".85"></path>
    </symbol>

    <!-- Small UI glyphs for preview controls -->
    <symbol id="pz-ui-full" viewBox="0 0 24 24">
      <path d="M4 10V4h6M14 4h6v6M4 14v6h6M14 20h6v-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </symbol>
    <symbol id="pz-ui-default" viewBox="0 0 24 24">
      <rect x="3" y="7" width="18" height="10" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"/>
      <path d="M3 6h18M3 18h18" stroke="currentColor" stroke-width="2"/>
    </symbol>
    <symbol id="pz-ui-float" viewBox="0 0 24 24">
      <rect x="3" y="5" width="18" height="14" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="2"/>
      <rect x="11" y="11" width="8" height="6" rx="1.5" fill="currentColor"/>
    </symbol>
  </defs>
</svg>`;
    document.body.appendChild(holder);
  }

  /* -----------------------------
   * 2) Portion buttons
   * ----------------------------- */
  const SYMBOL_BY_PORTION = {
    'whole': 'pz-portion-whole',
    '1': 'pz-portion-whole',
    '1.0': 'pz-portion-whole',
    'half': 'pz-portion-half',
    '1/2': 'pz-portion-half',
    '0.5': 'pz-portion-half',
    'half-left': 'pz-portion-half-left',
    'l': 'pz-portion-half-left',
    'left': 'pz-portion-half-left',
    'half-right': 'pz-portion-half-right',
    'r': 'pz-portion-half-right',
    'right': 'pz-portion-half-right',
    'q1': 'pz-portion-q-ul',
    'ul': 'pz-portion-q-ul',
    'q2': 'pz-portion-q-ur',
    'ur': 'pz-portion-q-ur',
    'q3': 'pz-portion-q-lr',
    'lr': 'pz-portion-q-lr',
    'q4': 'pz-portion-q-ll',
    'll': 'pz-portion-q-ll'
  };

  function makePortionBtn(value, label, symbolId) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pztp-portion-btn';
    btn.setAttribute('data-portion', value);
    btn.setAttribute('aria-label', label);

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 100 100');
    svg.classList.add('pztp-portion-icon');
    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#' + symbolId);
    svg.appendChild(use);

    const cap = document.createElement('span');
    cap.className = 'pztp-portion-label';
    cap.textContent = label;

    btn.appendChild(svg);
    btn.appendChild(cap);
    return btn;
  }

  function replaceLegacyIcon(btn, symbolId) {
    const oldSvg = btn.querySelector('svg');
    if (oldSvg) oldSvg.remove();
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 100 100');
    svg.classList.add('pztp-portion-icon');
    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#' + symbolId);
    svg.appendChild(use);
    btn.insertBefore(svg, btn.firstChild);
  }

  function normalizePortionCode(raw) {
    const v = String(raw || '').toLowerCase().trim();
    if (v === 'l' || v === 'left' || v === 'half-left') return 'half-left';
    if (v === 'r' || v === 'right' || v === 'half-right') return 'half-right';
    if (v === '1/2' || v === '0.5' || v === 'half') return 'half';
    if (!v || v === '1' || v === '1.0' || v === 'whole') return 'whole';
    return v;
  }

  function upgradePortionGroups() {
    $$('.pztp-portion-group, .pizzalayer-portion-group').forEach((group) => {
      // Replace legacy “black dot” icons by our symbol set
      $$('.pztp-portion-btn', group).forEach((btn) => {
        const val = String(btn.getAttribute('data-portion') || '').toLowerCase();
        const symbol = SYMBOL_BY_PORTION[val];
        if (symbol) replaceLegacyIcon(btn, symbol);
      });

      // Ensure half-left / half-right exist
      const hasLeft = group.querySelector('[data-portion="half-left"],[data-portion="l"],[data-portion="left"]');
      const hasRight = group.querySelector('[data-portion="half-right"],[data-portion="r"],[data-portion="right"]');
      if (!hasLeft) group.appendChild(makePortionBtn('half-left', 'Left Half', 'pz-portion-half-left'));
      if (!hasRight) group.appendChild(makePortionBtn('half-right', 'Right Half', 'pz-portion-half-right'));
    });
  }

  // Delegate: click => set active + hidden input update
  function bindPortionClicks() {
    on(document, 'click', function (e) {
      const btn = e.target.closest('.pztp-portion-btn');
      if (!btn) return;

      const portion = normalizePortionCode(btn.getAttribute('data-portion'));
      const group = btn.closest('.pztp-portion-group, .pizzalayer-portion-group');
      if (!group) return;

      group.querySelectorAll('.pztp-portion-btn.is-active').forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      // Hidden input common patterns: *portion* or *coverage*
      const hidden =
        group.querySelector('input[type="hidden"][name*="portion"]') ||
        group.querySelector('input[type="hidden"][name*="coverage"]');
      if (hidden) hidden.value = portion;

      // Notify any listeners (pricing, cart meta, etc.)
      group.dispatchEvent(new CustomEvent('pztp:portionChange', { detail: { portion } }));
    }, { passive: true });
  }

  /* -----------------------------
   * 3) Preview controls (full / default / float)
   * ----------------------------- */
  function ensurePreviewControls() {
    const root = $('#pztp-containers-presentation');
    const preview = $('#pztp-mobileorder-preview');
    if (!root || !preview) return null;

    let bar = $('#pztp-preview-modes');
    if (!bar) {
      bar = ensureEl('div', { id: 'pztp-preview-modes', class: 'pztp-preview-controls', role: 'toolbar', 'aria-label': 'Preview size controls' });
      // Insert right after preview
      preview.insertAdjacentElement('afterend', bar);
    }

    // If empty, populate buttons
    if (!bar.children.length) {
      bar.innerHTML = `
        <button class="pztp-preview-btn" data-mode="full" title="Full-screen preview" aria-label="Full-screen preview">
          <svg viewBox="0 0 24 24" class="pztp-icon"><use href="#pz-ui-full"></use></svg><span>Full</span>
        </button>
        <button class="pztp-preview-btn" data-mode="default" title="Default height" aria-label="Default height">
          <svg viewBox="0 0 24 24" class="pztp-icon"><use href="#pz-ui-default"></use></svg><span>Default</span>
        </button>
        <button class="pztp-preview-btn" data-mode="float" title="Float preview" aria-label="Float preview">
          <svg viewBox="0 0 24 24" class="pztp-icon"><use href="#pz-ui-float"></use></svg><span>Float</span>
        </button>
      `;
    }

    return { root, preview, bar };
  }

  function setupPreviewModes() {
    const ctx = ensurePreviewControls();
    if (!ctx) return;

    const { root, preview, bar } = ctx;

    function setMode(mode) {
      root.classList.remove('pztp-mode-full', 'pztp-mode-default', 'pztp-mode-float');
      root.classList.add('pztp-mode-' + mode);
    }

    // Button clicks
    on(bar, 'click', (e) => {
      const b = e.target.closest('.pztp-preview-btn');
      if (!b) return;
      const mode = b.getAttribute('data-mode');
      if (!mode) return;
      setMode(mode);
    });

    // Floating overlay (icons on hover) — add/remove with mode changes
    function ensureFloatOverlay() {
      if (preview.querySelector('.pztp-float-overlay')) return;
      const ov = ensureEl('div', { class: 'pztp-float-overlay' }, preview);
      ov.innerHTML = `
        <div class="pztp-float-icons">
          <svg viewBox="0 0 24 24"><use href="#pz-portion-whole"></use></svg>
          <svg viewBox="0 0 24 24"><use href="#pz-portion-half-left"></use></svg>
          <svg viewBox="0 0 24 24"><use href="#pz-portion-half-right"></use></svg>
        </div>
      `;
    }

    const observer = new MutationObserver(() => {
      const isFloat = root.classList.contains('pztp-mode-float');
      if (isFloat) ensureFloatOverlay();
      else {
        const ov = preview.querySelector('.pztp-float-overlay');
        if (ov) ov.remove();
      }
    });
    observer.observe(root, { attributes: true, attributeFilter: ['class'] });

    // Start in default
    setMode('default');
  }

  /* -----------------------------
   * 4) Go-to-tab helper (keeps your existing tab system)
   * ----------------------------- */
  function setupGoTabClicks() {
    on(document, 'click', function (e) {
      const btn = e.target.closest('.pztp-go-tab');
      if (!btn) return;

      const target = btn.getAttribute('data-tab');
      if (!target) return;

      const navBtn =
        document.querySelector('.tab-btn[data-tab="' + target + '"]') ||
        document.querySelector('.nav-tab[data-tab="' + target + '"]');

      if (navBtn) {
        navBtn.click();
        const root = document.querySelector('.pztp-mobileorder-wrapper') || document.body;
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }, { passive: true });
  }

  /* -----------------------------
   * 5) Boot
   * ----------------------------- */
  function init() {
    injectIconSprite();
    upgradePortionGroups();
    bindPortionClicks();
    setupPreviewModes();
    setupGoTabClicks();

    // Observe for dynamically injected rows/cards/options
    const mo = new MutationObserver(() => {
      upgradePortionGroups();
      ensurePreviewControls(); // in case preview is re-rendered
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
