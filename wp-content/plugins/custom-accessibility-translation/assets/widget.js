(() => {
  'use strict';
  const widget = document.getElementById('cwsatp-widget');
  if (!widget) return;
  const compactViewport = window.matchMedia('(max-width: 1024px)');
  const footer = document.querySelector('.ed-footer') || document.querySelector('footer');
  const placeWidget = () => {
    const bottom = widget.dataset.mobileBottom === '1' && compactViewport.matches;
    widget.classList.toggle('cwsatp-mobile-bottom', bottom);
    widget.classList.remove('cwsatp-in-footer', 'cwsatp-footer-fallback');
    if (footer) footer.classList.remove('cwsatp-footer-host', 'cwsatp-footer-left', 'cwsatp-footer-right');
    if (!bottom && widget.dataset.placement === 'footer') {
      widget.classList.add('cwsatp-in-footer');
      if (footer) {
        footer.classList.add('cwsatp-footer-host', widget.classList.contains('cwsatp-side-left') ? 'cwsatp-footer-left' : 'cwsatp-footer-right');
        footer.appendChild(widget);
      } else {
        widget.classList.add('cwsatp-footer-fallback');
        document.body.appendChild(widget);
      }
    } else document.body.appendChild(widget);
  };
  placeWidget();
  const trigger = widget.querySelector('.cwsatp-trigger');
  const panel = widget.querySelector('#cwsatp-panel');
  const languageTrigger = widget.querySelector('.cwsatp-language-trigger');
  const languages = widget.querySelector('#cwsatp-languages');
  const status = widget.querySelector('.cwsatp-status');
  const key = 'cwsatp-preferences-v1';
  const defaults = () => ({ size: 0, contrast: false, grayscale: false, links: false, font: false });
  let state = defaults();
  const originals = new Map();
  const excluded = el => el.closest('#cwsatp-widget, #wpadminbar, dialog, [role="dialog"], script, style, .screen-reader-text');
  const targets = 'p,h1,h2,h3,h4,h5,h6,a,button,label,input,textarea,select,li,td,th,blockquote,figcaption,span,small,strong,em';
  const restoreSizes = () => {
    originals.forEach((entry, el) => {
      if (entry.inline) el.style.setProperty('font-size', entry.inline, entry.priority);
      else el.style.removeProperty('font-size');
    });
    originals.clear();
  };
  const resizeText = () => {
    restoreSizes();
    if (!state.size) return;
    const measured = [...document.querySelectorAll(targets)].filter(el => !excluded(el)).map(el => [el, {
      inline: el.style.getPropertyValue('font-size'), priority: el.style.getPropertyPriority('font-size'),
      pixels: parseFloat(getComputedStyle(el).fontSize)
    }]);
    measured.forEach(([el, entry]) => {
      if (!Number.isFinite(entry.pixels)) return;
      originals.set(el, entry);
      el.style.setProperty('font-size', `${entry.pixels * (1 + state.size * 0.1)}px`, 'important');
    });
  };
  const apply = (announce = true) => {
    ['contrast', 'grayscale', 'links', 'font'].forEach(action => {
      document.body.classList.toggle(`cwsatp-${action}`, state[action]);
      widget.querySelector(`[data-action="${action}"]`).setAttribute('aria-pressed', String(state[action]));
    });
    resizeText();
    widget.querySelector('[data-action="increase"]').disabled = state.size >= 5;
    widget.querySelector('[data-action="decrease"]').disabled = state.size <= 0;
    if (announce) status.textContent = `${100 + state.size * 10}%`;
  };
  const persist = () => {
    try { if (widget.dataset.remember === '1') localStorage.setItem(key, JSON.stringify(state)); else localStorage.removeItem(key); } catch (_) { /* Storage may be disabled by the browser. */ }
  };
  try {
    if (widget.dataset.remember === '1') {
      const saved = JSON.parse(localStorage.getItem(key));
      if (saved && typeof saved === 'object') {
        state.size = Number.isInteger(saved.size) ? Math.max(0, Math.min(5, saved.size)) : 0;
        ['contrast','grayscale','links','font'].forEach(action => state[action] = saved[action] === true);
      }
    } else localStorage.removeItem(key);
  } catch (_) { /* Ignore malformed or unavailable storage. */ }
  function closePanel(restoreFocus = false) {
    panel.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    if (restoreFocus) trigger.focus();
  }
  function closeLanguages(restoreFocus = false) {
    if (!languages) return;
    languages.hidden = true; languageTrigger.setAttribute('aria-expanded','false');
    if (restoreFocus) languageTrigger.focus();
  }
  compactViewport.addEventListener('change', () => { closePanel(); closeLanguages(); placeWidget(); });
  trigger.addEventListener('click', () => {
    const opening = panel.hidden;
    closeLanguages(); panel.hidden = !opening; trigger.setAttribute('aria-expanded', String(opening));
    if (opening) panel.querySelector('.cwsatp-close').focus();
  });
  widget.querySelector('.cwsatp-close').addEventListener('click', () => closePanel(true));
  if (languageTrigger) languageTrigger.addEventListener('click', () => {
    const opening = languages.hidden;
    closePanel(); languages.hidden = !opening; languageTrigger.setAttribute('aria-expanded',String(opening));
    if (opening) languages.querySelector('a').focus();
  });
  widget.querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', () => {
    const action = button.dataset.action;
    if (action === 'increase') state.size = Math.min(5, state.size + 1);
    else if (action === 'decrease') state.size = Math.max(0, state.size - 1);
    else if (action === 'reset') state = defaults();
    else state[action] = !state[action];
    apply();
    if (action === 'reset') { try { localStorage.removeItem(key); } catch (_) {} }
    else persist();
  }));
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!panel.hidden) { event.stopPropagation(); closePanel(true); }
    else if (languages && !languages.hidden) { event.stopPropagation(); closeLanguages(true); }
  });
  document.addEventListener('pointerdown', event => { if (!widget.contains(event.target)) { closePanel(); closeLanguages(); } });
  document.addEventListener('focusin', event => { if (!widget.contains(event.target)) { closePanel(); closeLanguages(); } });
  let frame;
  const refresh = () => { if (state.size) { cancelAnimationFrame(frame); frame = requestAnimationFrame(resizeText); } };
  window.addEventListener('resize', refresh, { passive: true });
  new MutationObserver(records => { if (records.some(record => !widget.contains(record.target))) refresh(); }).observe(document.body, { childList: true, subtree: true });
  document.body.classList.add('cwsatp-ready');
  apply(false);
})();
