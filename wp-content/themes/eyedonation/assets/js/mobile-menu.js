/* Mobile accordion controls reuse the native menu labels and desktop chevrons. */
(()=>{
 const nav=document.querySelector('.ed-primary-navigation');
 if(!nav)return;
 const mobile=matchMedia('(max-width:1024px)'),menu=nav.querySelector('.wp-block-navigation__responsive-container');
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
  const setOpen=open=>{item.classList.toggle('ed-mobile-submenu-open',open);button.setAttribute('aria-expanded',String(open));};
  button.addEventListener('click',event=>{if(!mobile.matches)return;event.preventDefault();event.stopPropagation();setOpen(button.getAttribute('aria-expanded')!=='true');});
  entries.push({item,button,setOpen});
 });
 if(!entries.length)return;
 nav.classList.add('ed-mobile-accordion');
 const reset=()=>entries.forEach(entry=>entry.setOpen(false));mobile.addEventListener('change',reset);
 let wasOpen=menu?.classList.contains('is-menu-open');
 if(menu)new MutationObserver(()=>{const isOpen=menu.classList.contains('is-menu-open');if(isOpen!==wasOpen){reset();wasOpen=isOpen;}}).observe(menu,{attributes:true,attributeFilter:['class']});
 nav.addEventListener('keydown',event=>{
  if(!mobile.matches||event.key!=='Escape')return;
  const entry=entries.find(entry=>entry.item.contains(event.target)&&entry.button.getAttribute('aria-expanded')==='true');
  if(entry){event.preventDefault();event.stopPropagation();entry.setOpen(false);entry.button.focus();}
 },true);
})();
