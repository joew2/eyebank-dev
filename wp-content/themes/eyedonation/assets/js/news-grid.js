/* Load public batches without navigating or submitting a form. */
(()=>{
 const init=()=>document.querySelectorAll('.ed-news-archive[data-news-endpoint]').forEach(root=>{
  const button=root.querySelector('.ed-news-load-more'),list=root.querySelector('.ed-news-grid-list'),status=root.querySelector('.ed-news-load-status');
  if(!button||!list||!status||root.dataset.newsReady)return;
  root.dataset.newsReady='true';button.hidden=false;
  let busy=false;
  button.addEventListener('click',async()=>{
   if(busy)return;busy=true;button.disabled=true;button.textContent='Loading…';root.setAttribute('aria-busy','true');status.textContent='Loading more stories…';
   try{
    const url=new URL(root.dataset.newsEndpoint,location.href);url.searchParams.set('cursor',root.dataset.newsCursor);
    const response=await fetch(url,{headers:{Accept:'application/json'},credentials:'same-origin'});
    if(!response.ok)throw new Error('News request failed');
    const data=await response.json();
    if(typeof data.html!=='string'||typeof data.cursor!=='string'||typeof data.hasMore!=='boolean')throw new Error('Invalid news response');
    const template=document.createElement('template');template.innerHTML=data.html;
    const ids=new Set([...list.children].map(item=>item.dataset.postId));
    const additions=[...template.content.children].filter(item=>item.matches('li[data-post-id]')&&!ids.has(item.dataset.postId));
    additions.forEach((item,index)=>{item.classList.add('ed-news-enter');item.style.setProperty('--ed-news-delay',`${Math.min(index,5)*35}ms`);list.append(item);});
    root.dataset.newsCursor=data.cursor;
    status.textContent=additions.length?`${additions.length} more stories loaded.${data.hasMore?'':' All stories are now shown.'}`:'All stories are now shown.';
    if(!data.hasMore){button.hidden=true;if(additions.length)additions[0].querySelector('a')?.focus({preventScroll:true});}
   }catch(error){status.textContent='Stories could not be loaded. Please try again.';}
   finally{busy=false;button.disabled=false;button.textContent='Load more';root.removeAttribute('aria-busy');}
  });
 });
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
