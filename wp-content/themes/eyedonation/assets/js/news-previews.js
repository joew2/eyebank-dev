/* Measure real wrapping, including the inline link, rather than guessing character counts. */
(()=>{
 const sources=new WeakMap(),observed=new WeakSet();
 const fit=p=>{
  const copy=p.querySelector('.ed-news-preview-copy');if(!copy||!p.clientWidth)return;
  if(!sources.has(copy))sources.set(copy,copy.textContent.trim());
  const full=sources.get(copy);p.classList.add('ed-preview-fitted');
  const limit=p.clientHeight;
  const text=n=>n>=full.length?full:full.slice(0,n).trimEnd().replace(/[.,;:!?]+$/,'')+'…';
  let low=0,high=full.length;
  while(low<high){const mid=Math.ceil((low+high)/2);copy.textContent=text(mid);if(p.scrollHeight<=limit+1)low=mid;else high=mid-1;}
  let end=low;
  if(end<full.length&&end>0&&!/\s/.test(full[end])){const boundary=full.slice(0,end).lastIndexOf(' ');if(boundary>0)end=boundary;}
  copy.textContent=text(end);
 };
 const resize=new ResizeObserver(entries=>entries.forEach(entry=>fit(entry.target)));
 const add=root=>root.querySelectorAll('.ed-news-intro p').forEach(p=>{if(observed.has(p))return;observed.add(p);fit(p);resize.observe(p);});
 const init=()=>document.querySelectorAll('.ed-news').forEach(root=>{
  add(root);
  new MutationObserver(records=>{if(records.some(record=>[...record.addedNodes].some(node=>node.nodeType===1)))add(root);}).observe(root,{childList:true,subtree:true});
 });
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
 document.fonts?.ready.then(()=>document.querySelectorAll('.ed-news-intro p').forEach(fit));
})();
