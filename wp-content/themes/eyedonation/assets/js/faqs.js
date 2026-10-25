/* Native details remain usable without JavaScript; enhancement adds accordion motion. */
(()=>{
 const items=[...document.querySelectorAll('.ed-faq-list>.ed-faq-item')];
 if(!items.length)return;
 const reduced=matchMedia('(prefers-reduced-motion:reduce)');
 const entries=items.map((item,index)=>{
  const summary=item.querySelector(':scope>summary'),answer=item.querySelector(':scope>.ed-faq-answer');
  if(!summary||!answer)return null;
  // JS coordinates single-open behavior while allowing the previous answer to animate shut.
  item.removeAttribute('name');
  summary.setAttribute('aria-controls',answer.id);
  summary.id=summary.id||`ed-faq-trigger-${index+1}`;
  summary.setAttribute('aria-expanded',String(item.open));
  answer.inert=!item.open;
  return{item,summary,answer,target:item.open,animation:null};
 }).filter(Boolean);
 function setOpen(entry,open){
  const {item,summary,answer}=entry;
  const start=answer.getBoundingClientRect().height;
  const prior=getComputedStyle(answer);
  const frame={height:start+'px',opacity:start?prior.opacity:0,paddingTop:start?prior.paddingTop:'0px',paddingBottom:start?prior.paddingBottom:'0px'};
  if(entry.animation){entry.animation.cancel();entry.animation=null;}
  entry.target=open;summary.setAttribute('aria-expanded',String(open));answer.inert=!open;
  if(reduced.matches||typeof answer.animate!=='function'){item.open=open;answer.style.overflow='';return;}
  item.open=true;
  const full=answer.getBoundingClientRect().height,style=getComputedStyle(answer);
  answer.style.overflow='hidden';
  const animation=answer.animate([frame,{height:(open?full:0)+'px',opacity:open?1:0,paddingTop:open?style.paddingTop:'0px',paddingBottom:open?style.paddingBottom:'0px'}],{duration:240,easing:'cubic-bezier(.2,.7,.2,1)'});
  entry.animation=animation;
  animation.onfinish=()=>{if(entry.animation!==animation)return;entry.animation=null;item.open=open;answer.style.overflow='';};
 }
 entries.forEach(entry=>entry.summary.addEventListener('click',event=>{
  event.preventDefault();const open=!entry.target;
  if(open)entries.forEach(other=>{if(other!==entry&&other.target)setOpen(other,false);});
  setOpen(entry,open);
 }));
})();
