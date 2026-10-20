/* Reveal the existing WordPress search form beside its icon. */
(()=>{
 const form=document.querySelector('.ed-header-search');if(!form)return;
 const field=form.querySelector('input[type=search]');if(!field)return;
 const control=document.createElement('div');control.className='ed-header-search-control';
 const toggle=document.createElement('button');toggle.type='button';toggle.className='ed-search-open';toggle.setAttribute('aria-label',window.edSearchLabels?.open||'Search');toggle.setAttribute('aria-expanded','false');
 if(!form.id)form.id='ed-inline-search-form';toggle.setAttribute('aria-controls',form.id);
 toggle.innerHTML='<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M15 15L21 21M10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10C17 13.866 13.866 17 10 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
 form.before(control);control.append(toggle,form);form.inert=true;form.setAttribute('aria-hidden','true');
 const size=()=>{const available=Math.max(1,innerWidth-toggle.getBoundingClientRect().right-24);control.style.setProperty('--ed-inline-search-width',`${Math.min(170,available)}px`);};
 const close=returnFocus=>{control.classList.remove('is-open');toggle.setAttribute('aria-expanded','false');form.inert=true;form.setAttribute('aria-hidden','true');if(returnFocus)toggle.focus();};
 toggle.addEventListener('click',()=>{if(control.classList.contains('is-open')){close(true);return;}size();form.inert=false;form.removeAttribute('aria-hidden');control.classList.add('is-open');toggle.setAttribute('aria-expanded','true');setTimeout(()=>{if(control.classList.contains('is-open'))field.focus({preventScroll:true});},matchMedia('(prefers-reduced-motion: reduce)').matches?0:210);});
 control.addEventListener('keydown',event=>{if(event.key==='Escape'&&control.classList.contains('is-open')){event.preventDefault();event.stopPropagation();close(true);}});
 document.addEventListener('pointerdown',event=>{if(!control.contains(event.target))close(false);});
 control.addEventListener('focusout',()=>{queueMicrotask(()=>{if(!control.contains(document.activeElement))close(false);});});
 window.addEventListener('resize',()=>{if(control.classList.contains('is-open'))size();});
 const menu=document.querySelector('.ed-primary-navigation .wp-block-navigation__responsive-container');
 if(menu)new MutationObserver(()=>{if(!menu.classList.contains('is-menu-open')&&matchMedia('(max-width:1024px)').matches)close(false);}).observe(menu,{attributes:true,attributeFilter:['class']});
})();
