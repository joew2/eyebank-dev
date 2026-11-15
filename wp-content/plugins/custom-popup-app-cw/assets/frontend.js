(function () {
 'use strict';
 let active=false;
 const pause=ms=>new Promise(resolve=>setTimeout(resolve,ms));
 function permitted(candidate,site) {
  try {
   if(candidate.frequency==='session') return !sessionStorage.getItem(site+'_'+candidate.id);
   if(candidate.frequency==='daily') {const stamp=Number(localStorage.getItem(site+'_'+candidate.id));return !stamp||Date.now()-stamp>=86400000;}
  } catch (_) { /* Storage blocked: degrade to once per load. */ }
  return true;
 }
 function impression(candidate,site) {
  try {if(candidate.frequency==='session')sessionStorage.setItem(site+'_'+candidate.id,'1');else if(candidate.frequency==='daily')localStorage.setItem(site+'_'+candidate.id,String(Date.now()));} catch (_) {}
 }
 function geometry(candidate,root) {
  const viewport=window.visualViewport;
  const width=Math.min(viewport?.width||window.innerWidth,root.clientWidth||window.innerWidth);
  const height=viewport?.height||root.querySelector('.cw_popup_viewport')?.getBoundingClientRect().height||window.innerHeight;
  const left=viewport?.offsetLeft||0,top=viewport?.offsetTop||0;
  const style=getComputedStyle(root);
  const n=name=>parseFloat(style.getPropertyValue(name))||0;
  // Leave room for the corner control and its hover enlargement.
  const gutter=Math.max(28,n('--cw-popup-gutter'));
  const bar=document.getElementById('wpadminbar');
  const barRect=bar?.getBoundingClientRect();
  const barHeight=barRect?Math.max(0,Math.min(height,barRect.bottom-top)):0;
  const x0=left+gutter+n('--cw-popup-safe-left');
  const y0=top+barHeight+gutter+n('--cw-popup-safe-top');
  const aw=Math.max(1,width-2*gutter-n('--cw-popup-safe-left')-n('--cw-popup-safe-right'));
  const ah=Math.max(1,height-barHeight-2*gutter-n('--cw-popup-safe-top')-n('--cw-popup-safe-bottom'));
  const requested=[600,650,700,800].includes(Number(candidate.displayWidth))?Number(candidate.displayWidth):650;
  const w=Math.max(1,Math.floor(Math.min(requested,aw)));
  const h=Math.max(1,Math.round(w*candidate.height/candidate.width));
  return {w,h,left:x0+(aw-w)/2,top:y0+Math.max(0,(ah-h)/2),gutter};
 }
 function skeleton() {
  const root=document.createElement('dialog');root.className='cw_popup_root';root.setAttribute('aria-label','Announcement');
  root.innerHTML='<span class="cw_popup_viewport" aria-hidden="true"></span><div class="cw_popup_panel"><div class="cw_popup_close_row"><button type="button" class="cw_popup_close" aria-label="Close pop-up" title="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 5 14 14M19 5 5 19"/></svg></button></div></div>';
  document.body.append(root);return root;
 }
 async function prepare(candidate) {
  const root=skeleton();
  if(typeof root.showModal!=='function'){root.remove();return null;}
  const image=new Image();image.className='cw_popup_image';image.alt=candidate.alt;image.decoding='async';
  const box=geometry(candidate,root);image.sizes=box.w+'px';
  const loaded=new Promise(resolve=>{
   const timer=setTimeout(()=>resolve(false),12000);
   image.onload=()=>{clearTimeout(timer);resolve(image.naturalWidth>0);};
   image.onerror=()=>{clearTimeout(timer);resolve(false);};
  });
  if(candidate.srcset)image.srcset=candidate.srcset;
  image.src=candidate.src;
  if(!await loaded){root.remove();return null;}
  if(image.decode)try{await image.decode();}catch(_){root.remove();return null;}
  let graphic;
  if(candidate.url){
   graphic=document.createElement('a');graphic.href=candidate.url;
   if(candidate.newTab){graphic.target='_blank';graphic.rel='noopener noreferrer';const hint=document.createElement('span');hint.className='cw_popup_sr';hint.textContent=' (opens in a new tab)';graphic.append(hint);}
  } else graphic=document.createElement('div');
  graphic.className='cw_popup_graphic';graphic.prepend(image);
  const frame=document.createElement('div');frame.className='cw_popup_frame';frame.append(graphic);root.querySelector('.cw_popup_panel').append(frame);
  return {root,image,frame};
 }
 function open(candidate,prepared,onOpen) {
  const {root,image,frame}=prepared;
  const previous=document.activeElement,scrollX=window.scrollX,scrollY=window.scrollY;
  const body=document.body,html=document.documentElement;
  // Preserve precisely the inline properties we change, including their priorities.
  const changes=[];
  const set=(el,key,value)=>{changes.push([el,key,el.style.getPropertyValue(key),el.style.getPropertyPriority(key)]);el.style.setProperty(key,value);};
  const resize=()=>{
   const g=geometry(candidate,root);
   image.style.setProperty('width',g.w+'px','important');image.style.setProperty('height',g.h+'px','important');image.sizes=g.w+'px';
   const panel=root.querySelector('.cw_popup_panel');
   panel.style.width=g.w+'px';panel.style.marginLeft=g.left+'px';panel.style.marginTop=g.top+'px';panel.style.marginBottom=g.gutter+'px';
  };
  return new Promise(resolve=>{
   let finished=false;
   const cleanup=()=>{
    if(finished)return;finished=true;
    window.removeEventListener('resize',resize);window.visualViewport?.removeEventListener('resize',resize);window.visualViewport?.removeEventListener('scroll',resize);
    if(root.open)root.close();root.remove();
    changes.reverse().forEach(([el,k,v,p])=>v?el.style.setProperty(k,v,p):el.style.removeProperty(k));
    // Avoid a theme's smooth-scroll rule animating scroll restoration.
    const old=html.style.getPropertyValue('scroll-behavior'),priority=html.style.getPropertyPriority('scroll-behavior');html.style.setProperty('scroll-behavior','auto','important');
    window.scrollTo(scrollX,scrollY);if(previous?.isConnected)previous.focus({preventScroll:true});
    if(old)html.style.setProperty('scroll-behavior',old,priority);else html.style.removeProperty('scroll-behavior');
    active=false;resolve(true);
   };
   image.addEventListener('error',cleanup,{once:true});
   root.querySelector('.cw_popup_close').addEventListener('click',cleanup);
   root.addEventListener('cancel',e=>{e.preventDefault();cleanup();});root.addEventListener('close',cleanup);
   // Native dialog sends backdrop events to the dialog itself; guard both ends of the click.
   let backdropDown=false;
   const outside=e=>e.target===root;
   root.addEventListener('pointerdown',e=>{backdropDown=outside(e);});root.addEventListener('click',e=>{if(backdropDown&&outside(e))cleanup();backdropDown=false;});
   root.addEventListener('keydown',e=>{
    if(e.key!=='Tab')return;const nodes=[...root.querySelectorAll('button,a[href],[tabindex="0"]')];const first=nodes[0],last=nodes[nodes.length-1];
    if(e.shiftKey&&(document.activeElement===first||document.activeElement===root)){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}
   });
   try {
    root.showModal();active=true;
    const scrollbar=window.innerWidth-html.clientWidth;
    if(scrollbar)set(body,'padding-right',(parseFloat(getComputedStyle(body).paddingRight)+scrollbar)+'px');
    set(html,'scrollbar-gutter','auto');set(body,'box-sizing','border-box');
    // Keep the document's scroll offset intact so sticky headers do not react to a false scroll-to-top.
    set(body,'overflow','hidden');set(html,'overflow','hidden');set(html,'overscroll-behavior','none');
    resize();window.addEventListener('resize',resize);window.visualViewport?.addEventListener('resize',resize);window.visualViewport?.addEventListener('scroll',resize);
    root.querySelector('.cw_popup_close').focus({preventScroll:true});
    onOpen?.();
   } catch (_) {cleanup();}
  });
 }
 window.cw_popup_preview=async candidate=>{
  if(active)return false;
  const prepared=await prepare(candidate);if(!prepared)return false;
  return open(candidate,prepared);
 };
 async function init(){
  const el=document.getElementById('cw_popup_config');if(!el)return;
  let config;try{config=JSON.parse(el.textContent);}catch(_){return;}
  for(const candidate of config.candidates||[]){
   if(!permitted(candidate,config.site))continue;
   const prepared=await prepare(candidate);if(!prepared)continue;
   await pause(Math.max(0,Math.min(60,candidate.delay))*1000);
   if(document.hidden)await new Promise(resolve=>{const listener=()=>{if(!document.hidden){document.removeEventListener('visibilitychange',listener);resolve();}};document.addEventListener('visibilitychange',listener);});
   await open(candidate,prepared,()=>impression(candidate,config.site));
   return; // A dismissal never advances to another candidate.
  }
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
