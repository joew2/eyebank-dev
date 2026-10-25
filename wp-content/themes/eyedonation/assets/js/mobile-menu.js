/* Mobile accordion controls reuse the native menu labels and desktop chevrons. */
(()=>{
 const nav=document.querySelector('.ed-primary-navigation');
 if(!nav)return;
 const mobile=matchMedia('(max-width:1024px)'),menu=nav.querySelector('.wp-block-navigation__responsive-container');
 // Reuse the header's approved destination and label inside the mobile menu.
 const content=menu?.querySelector('.wp-block-navigation__responsive-container-content');
 const utility=document.querySelector('.ed-brand>.wp-block-buttons .wp-block-button');
 if(content&&utility&&!content.querySelector('.ed-mobile-menu-utility')){
  const copy=utility.cloneNode(true);copy.classList.add('ed-mobile-menu-utility');
  copy.removeAttribute('id');copy.querySelectorAll('[id]').forEach(el=>el.removeAttribute('id'));
  content.append(copy);
 }
 const reduced=matchMedia('(prefers-reduced-motion:reduce)');
 const entries=[];
 nav.querySelectorAll('.wp-block-navigation__container>.has-child').forEach((item,index)=>{
  const label=item.querySelector(':scope>.wp-block-navigation-item__content'),arrow=item.querySelector(':scope>.wp-block-navigation__submenu-icon'),submenu=item.querySelector(':scope>.wp-block-navigation__submenu-container');
  if(!label||!arrow||!submenu)return;
  const button=document.createElement('button');button.type='button';button.className='ed-mobile-submenu-toggle';
  const text=document.createElement('span');text.textContent=label.textContent.trim();
  const indicator=document.createElement('span');indicator.className='wp-block-navigation__submenu-icon';indicator.setAttribute('aria-hidden','true');
  const svg=arrow.querySelector('svg');if(svg)indicator.append(svg.cloneNode(true));
  button.append(text,indicator);if(!submenu.id)submenu.id=`ed-mobile-submenu-${index+1}`;
  button.setAttribute('aria-controls',submenu.id);button.setAttribute('aria-expanded','false');item.prepend(button);
  let animation;
  const setOpen=(open,animate=true)=>{
   const start=submenu.getBoundingClientRect().height;
   const opacity=getComputedStyle(submenu).opacity;
   if(animation){animation.cancel();animation=null;}
   button.setAttribute('aria-expanded',String(open));
   submenu.inert=mobile.matches&&!open;
   if(!animate||reduced.matches||!mobile.matches){
    item.classList.toggle('ed-mobile-submenu-open',open);submenu.style.overflow='';return;
   }
   // Keep the native submenu in flow until its collapse finishes.
   item.classList.add('ed-mobile-submenu-open');
   const full=submenu.getBoundingClientRect().height;
   const style=getComputedStyle(submenu);
   submenu.style.overflow='hidden';
   animation=submenu.animate([
    {height:start+'px',opacity:start?opacity:0,paddingTop:start?style.paddingTop:'0px',paddingBottom:start?style.paddingBottom:'0px'},
    {height:(open?full:0)+'px',opacity:open?1:0,paddingTop:open?style.paddingTop:'0px',paddingBottom:open?style.paddingBottom:'0px'}
   ],{duration:240,easing:'cubic-bezier(.2,.7,.2,1)'});
   const active=animation;
   active.onfinish=()=>{if(animation!==active)return;animation=null;item.classList.toggle('ed-mobile-submenu-open',open);submenu.style.overflow='';};
  };
  button.addEventListener('click',event=>{if(!mobile.matches)return;event.preventDefault();event.stopPropagation();setOpen(button.getAttribute('aria-expanded')!=='true');});
  entries.push({item,button,setOpen});
 });
 if(!entries.length)return;
 nav.classList.add('ed-mobile-accordion');
 const reset=()=>entries.forEach(entry=>entry.setOpen(false,false));
 const closeButton=menu?.querySelector('.wp-block-navigation__responsive-container-close');
 let closing=false,replay=false,closeTimer;
 const finishClose=()=>{
  replay=true;closeButton?.click();replay=false;
  closing=false;menu?.classList.remove('ed-mobile-menu-closing');
 };
 const fadeClose=()=>{
  if(closing)return;
  if(reduced.matches){finishClose();return;}
  closing=true;menu.classList.add('ed-mobile-menu-closing');
  closeTimer=setTimeout(finishClose,180);
 };
 closeButton?.addEventListener('click',event=>{
  if(!mobile.matches||replay||!menu.classList.contains('is-menu-open'))return;
  event.preventDefault();event.stopImmediatePropagation();fadeClose();
 },true);
 mobile.addEventListener('change',()=>{
  clearTimeout(closeTimer);closing=false;replay=false;
  menu?.classList.remove('ed-mobile-menu-closing');reset();
 });
 let wasOpen=menu?.classList.contains('is-menu-open');
 if(menu)new MutationObserver(()=>{const isOpen=menu.classList.contains('is-menu-open');if(isOpen!==wasOpen){reset();wasOpen=isOpen;if(!isOpen){clearTimeout(closeTimer);closing=false;menu.classList.remove('ed-mobile-menu-closing');}}}).observe(menu,{attributes:true,attributeFilter:['class']});
 nav.addEventListener('keydown',event=>{
  if(!mobile.matches||event.key!=='Escape')return;
  const entry=entries.find(entry=>entry.item.contains(event.target)&&entry.button.getAttribute('aria-expanded')==='true');
  if(entry){event.preventDefault();event.stopPropagation();entry.setOpen(false);entry.button.focus();}
  else if(menu?.classList.contains('is-menu-open')){event.preventDefault();event.stopPropagation();fadeClose();}
 },true);
})();
