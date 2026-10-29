/* A hanging search panel overlays the page without changing menu layout. */
(()=>{
 const form=document.querySelector('.ed-header-search');if(!form)return;
 const field=form.querySelector('input[type=search]');if(!field)return;
 const icon='<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M15 15L21 21M10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10C17 13.866 13.866 17 10 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
 const control=document.createElement('div');control.className='ed-header-search-control';
 const toggle=document.createElement('button');toggle.type='button';toggle.className='ed-search-open';
 const labels=window.edSearchLabels||{};
 toggle.setAttribute('aria-label',labels.open||'Search');toggle.setAttribute('aria-expanded','false');
 if(!form.id)form.id='ed-inline-search-form';toggle.setAttribute('aria-controls',form.id);
 toggle.innerHTML=icon+'<svg class="ed-search-chevron" viewBox="0 0 12 8" fill="none" aria-hidden="true" focusable="false"><path d="m1 1 5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
 const submit=form.querySelector('button[type=submit]')||form.querySelector('.wp-block-search__button');
 if(submit){submit.setAttribute('aria-label',labels.open||'Search');submit.innerHTML=icon;}
 form.before(control);control.append(toggle,form);form.inert=true;form.setAttribute('aria-hidden','true');
 const position=()=>{
  const rect=control.getBoundingClientRect(),width=form.offsetWidth;
  const preferred=matchMedia('(max-width:1024px)').matches?(rect.width-width)/2:rect.width-width;
  const left=Math.max(12-rect.left,Math.min(preferred,innerWidth-width-12-rect.left));
  control.style.setProperty('--ed-search-panel-left',left+'px');
  control.classList.toggle('opens-up',innerHeight-rect.bottom<form.offsetHeight+12&&rect.top>form.offsetHeight+12);
 };
 let hoverCloseTimer,focusTimer,pinned=false,pointerInteracting=false;
 const close=returnFocus=>{
  clearTimeout(hoverCloseTimer);clearTimeout(focusTimer);pinned=false;
  control.classList.remove('is-open');toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label',labels.open||'Search');
  form.inert=true;form.setAttribute('aria-hidden','true');if(returnFocus)toggle.focus();
 };
 const open=focusField=>{
  clearTimeout(hoverCloseTimer);position();form.inert=false;form.removeAttribute('aria-hidden');control.classList.add('is-open');
  toggle.setAttribute('aria-expanded','true');toggle.setAttribute('aria-label',labels.close||'Close search');
  if(focusField){clearTimeout(focusTimer);focusTimer=setTimeout(()=>{if(control.classList.contains('is-open'))field.focus({preventScroll:true});},matchMedia('(prefers-reduced-motion: reduce)').matches?0:180);}
 };
 toggle.addEventListener('click',()=>{
  if(control.classList.contains('is-open')&&pinned){close(true);return;}
  pinned=true;open(true);
 });
 // Hover reveals the panel without stealing keyboard focus. Touch remains click-driven.
 const canHover=event=>event.pointerType==='mouse'&&matchMedia('(any-hover:hover)').matches;
 control.addEventListener('pointerenter',event=>{if(canHover(event))open(false);});
 control.addEventListener('pointerleave',event=>{
  if(!canHover(event))return;
  clearTimeout(hoverCloseTimer);hoverCloseTimer=setTimeout(()=>{
   if(!pinned&&!control.contains(document.activeElement))close(false);
  },180);
 });
 control.addEventListener('keydown',event=>{if(event.key==='Escape'&&control.classList.contains('is-open')){event.preventDefault();event.stopPropagation();close(true);}});
 document.addEventListener('pointerdown',event=>{if(!control.contains(event.target))close(false);});
 control.addEventListener('pointerdown',()=>{pointerInteracting=true;});
 document.addEventListener('pointerup',()=>{setTimeout(()=>{pointerInteracting=false;},0);});
 document.addEventListener('pointercancel',()=>{pointerInteracting=false;});
 control.addEventListener('focusout',()=>{queueMicrotask(()=>{if(!control.contains(document.activeElement)&&!pointerInteracting){pinned=false;if(!control.matches(':hover'))close(false);}});});
 window.addEventListener('resize',()=>{if(control.classList.contains('is-open'))position();});
 const menu=document.querySelector('.ed-primary-navigation .wp-block-navigation__responsive-container');
 if(menu)new MutationObserver(()=>{if(!menu.classList.contains('is-menu-open')&&matchMedia('(max-width:1024px)').matches)close(false);}).observe(menu,{attributes:true,attributeFilter:['class']});
})();
