(() => {
  'use strict';
  const config = window.EYE_CAN_SEE_CONFIG || { videos: {}, links: {} };
  const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
  document.getElementById('copyright-year').textContent = new Date().getFullYear();
  const notice = document.getElementById('link-notice');
  const dialog = document.getElementById('video-dialog');
  const player = document.getElementById('video-player');
  const fallback = document.getElementById('video-fallback');
  let lastTrigger = null;
  let closeTimer;
  function openDialog(target, trigger) {
    clearTimeout(closeTimer);
    target.classList.remove('is-closing');
    lastTrigger = trigger;
    target.showModal();
    document.body.classList.add('dialog-open');
    target.querySelector('.dialog-close').focus();
  }
  function closeDialog(target) {
    if (!target.open || target.classList.contains('is-closing')) return;
    // Stop playback immediately, including during the closing animation.
    if (target === dialog) player.replaceChildren();
    const finish = () => {
      target.close();
      target.classList.remove('is-closing');
      document.body.classList.remove('dialog-open');
      lastTrigger?.focus({ preventScroll: true });
    };
    if (reducedMotion.matches) finish();
    else { target.classList.add('is-closing'); closeTimer = setTimeout(finish, 180); }
  }
  [dialog, notice].forEach(target => {
    target.querySelector('.dialog-close').addEventListener('click', () => closeDialog(target));
    target.addEventListener('cancel', event => { event.preventDefault(); closeDialog(target); });
    target.addEventListener('click', event => {
      if (event.target !== target) return;
      const box = target.getBoundingClientRect();
      if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) closeDialog(target);
    });
    target.addEventListener('close', () => {
      if (target === dialog) player.replaceChildren();
      document.body.classList.remove('dialog-open');
    });
  });
  function vimeoEmbed(raw) {
    try {
      const url = new URL(raw);
      if (!['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'].includes(url.hostname)) return null;
      const parts = url.pathname.split('/').filter(Boolean);
      const index = parts.findIndex(part => /^\d+$/.test(part));
      if (index < 0) return null;
      const embed = new URL('https://player.vimeo.com/video/' + parts[index]);
      const hash = url.searchParams.get('h') || parts[index + 1];
      if (hash && /^[a-zA-Z0-9]+$/.test(hash)) embed.searchParams.set('h', hash);
      embed.searchParams.set('autoplay', '1');
      embed.searchParams.set('dnt', '1');
      return embed.href;
    } catch { return null; }
  }
  document.querySelectorAll('[data-video]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const video = config.videos[trigger.dataset.video];
      const embed = video && vimeoEmbed(video.url);
      const title = video?.title || 'Story video';
      document.getElementById('video-dialog-title').textContent = title;
      player.replaceChildren(); fallback.replaceChildren();
      if (embed) {
        const iframe = document.createElement('iframe');
        iframe.title = title;
        iframe.allow = 'autoplay; fullscreen; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        iframe.src = embed;
        player.append(iframe);
        const link = document.createElement('a');
        link.href = video.url; link.target = '_blank'; link.rel = 'noopener noreferrer';
        link.textContent = 'Watch on Vimeo';
        fallback.append('If the video does not play, ', link, '.');
      } else {
        const message = document.createElement('p');
        message.className = 'pending-video';
        message.textContent = 'Adam’s video will be available here soon. In the meantime, explore the other sight restoration stories below.';
        player.append(message);
      }
      openDialog(dialog, trigger);
    });
  });
  document.querySelectorAll('[data-site-link]').forEach(link => {
    const destination = config.links[link.dataset.siteLink];
    if (destination) link.href = destination;
    else link.addEventListener('click', event => { event.preventDefault(); openDialog(notice, link); });
  });
  const toggle = document.querySelector('.menu-toggle');
  const menu = document.getElementById('mobile-menu');
  function setMenu(open) {
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    menu.classList.toggle('is-open', open); menu.inert = !open;
  }
  toggle.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
  menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { setMenu(false); toggle.focus(); } });
  document.addEventListener('click', event => { if (!event.target.closest('.site-header')) setMenu(false); });
  matchMedia('(max-width: 700px)').addEventListener('change', () => setMenu(false));

  const grid = document.getElementById('portrait-grid');
  const load = document.getElementById('load-portraits');
  const status = document.getElementById('portrait-status');
  let portraits = null; let offset = 0;
  load.addEventListener('click', async () => {
    if (load.disabled) return;
    load.disabled = true; load.setAttribute('aria-busy', 'true');
    load.textContent = 'Loading portraits…'; status.textContent = '';
    try {
      // No manifest request or extra image elements are created before this click.
      if (!portraits) {
        const response = await fetch('assets/data/portraits.json');
        if (!response.ok) throw new Error('Portrait list unavailable');
        const data = await response.json();
        if (!Array.isArray(data) || data.some(item => typeof item.name !== 'string' || !/^assets\/portraits\/\d+\.jpg$/.test(item.src))) throw new Error('Invalid portrait list');
        portraits = data;
      }
      const batch = portraits.slice(offset, offset + 10);
      // Assign src only for the requested batch; fetching JSON never preloads other images.
      const items = await Promise.all(batch.map(item => new Promise(resolve => {
        const image = new Image(230, 288);
        image.alt = item.name + ', cornea transplant recipient';
        image.onload = () => resolve({ image, item, ok: true });
        image.onerror = () => resolve({ image, item, ok: false });
        image.src = item.src;
      })));
      if (items.some(item => !item.ok)) throw new Error('A portrait could not be loaded');
      const fragment = document.createDocumentFragment();
      let first;
      items.forEach(({image}, index) => {
        const li = document.createElement('li'); li.className = 'portrait-new';
        li.style.animationDelay = Math.min(index * 35, 200) + 'ms'; li.append(image);
        fragment.append(li); if (!first) first = li;
      });
      grid.append(fragment); offset += batch.length;
      status.textContent = batch.length + ' more portraits loaded. ' + (10 + offset) + ' of ' + (10 + portraits.length) + ' portraits shown.';
      if (offset >= portraits.length) {
        load.hidden = true;
        status.textContent = 'All ' + (10 + portraits.length) + ' portraits are now displayed.';
        status.tabIndex = -1; status.focus({preventScroll:true});
      } else load.textContent = 'View More Portraits';
    } catch (error) {
      status.textContent = 'We couldn’t load these portraits. Please try again.';
      load.textContent = 'Try Again';
    } finally { load.disabled = false; load.removeAttribute('aria-busy'); }
  });
})();
