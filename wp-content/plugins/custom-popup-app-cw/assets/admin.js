(function(){
 'use strict';
 const root=document.getElementById('cw_popup_admin_root');if(!root)return;
 const form=root.querySelector('form');const get=id=>root.querySelector('#cw_popup_'+id);
 const config=window.cw_popup_admin;
 function imagePreview(media){
  get('image').value=media?.id||0;get('image_preview').replaceChildren();get('dimensions').textContent='';
  if(!media)return;
  const image=new Image();image.src=media.sizes?.medium?.url||media.url;image.alt='';get('image_preview').append(image);
  get('dimensions').textContent=media.width+' × '+media.height+' px · '+(media.height>media.width?'Portrait':'Landscape / square');
  if(!get('description').value.trim())get('description').value=media.alt||'';
 }
 get('choose').addEventListener('click',()=>{const picker=wp.media({title:'Choose popup image',library:{type:'image'},multiple:false,button:{text:'Use image'}});picker.on('select',()=>imagePreview(picker.state().get('selection').first().toJSON()));picker.open();});
 get('remove').addEventListener('click',()=>imagePreview(null));
 root.querySelectorAll('[name=target]').forEach(input=>input.addEventListener('change',()=>{get('page_selector').hidden=form.elements.target.value!=='pages';}));
 const selected=get('selected'),results=get('results'),search=get('search'),status=get('search_status'),more=get('more');
 function chosen(id){return !!selected.querySelector('[data-page-id="'+id+'"]');}
 function refresh(){results.querySelectorAll('button').forEach(b=>{b.disabled=chosen(b.dataset.id);b.textContent=b.dataset.label+(b.disabled?' — Selected':'');});}
 selected.addEventListener('click',e=>{if(!e.target.classList.contains('cw_popup_remove_page'))return;const li=e.target.closest('li');const next=li.nextElementSibling?.querySelector('button')||li.previousElementSibling?.querySelector('button');li.remove();refresh();(next||search).focus();});
 function add(item){if(chosen(item.id))return;const li=document.createElement('li');li.dataset.pageId=item.id;const input=document.createElement('input');input.type='hidden';input.name='pages[]';input.value=item.id;const label=document.createElement('span');label.textContent=item.label;const button=document.createElement('button');button.type='button';button.className='button cw_popup_remove_page';button.textContent='Remove';button.setAttribute('aria-label','Remove '+item.label);li.append(input,label,button);selected.append(li);refresh();button.focus();}
 let request=0,timer,controller,page=1;
 async function fetchResults(append=false){
  const term=search.value.trim();const seq=++request;controller?.abort();controller=new AbortController();
  more.hidden=true;if(!append)results.replaceChildren();
  if(term.length<2){status.textContent='Type at least 2 characters.';return;}
  status.textContent='Loading pages…';
  const data=new FormData();data.set('action','cw_popup_search');data.set('nonce',config.searchNonce);data.set('term',term);data.set('page',page);
  try {
   const response=await fetch(config.ajax,{method:'POST',body:data,signal:controller.signal,credentials:'same-origin'});const json=await response.json().catch(()=>{throw new Error('Unable to search pages. Please try again.');});if(seq!==request)return;
   if(!response.ok||!json.success)throw new Error(json.data?.message||'Unable to search pages. Please try again.');
   json.data.items.forEach(item=>{const li=document.createElement('li'),button=document.createElement('button');button.type='button';button.className='button';button.dataset.id=item.id;button.dataset.label=item.label;button.addEventListener('click',()=>add(item));li.append(button);results.append(li);});refresh();
   status.textContent=results.children.length?results.children.length+' results shown.':'No matching public pages.';more.hidden=!json.data.more;
  }catch(error){if(seq===request&&error.name!=='AbortError'){status.textContent=error.message;more.hidden=true;}}
 }
 search.addEventListener('input',()=>{clearTimeout(timer);request++;controller?.abort();more.hidden=true;page=1;timer=setTimeout(()=>fetchResults(),250);});
 more.addEventListener('click',()=>{page++;fetchResults(true);});
 get('preview').addEventListener('click',async()=>{
  const button=get('preview'),message=get('preview_status');button.disabled=true;message.textContent='Preparing preview…';
  const data=new FormData(form);data.set('action','cw_popup_preview');data.set('nonce',config.previewNonce);
  try{const response=await fetch(config.ajax,{method:'POST',body:data,credentials:'same-origin'});const json=await response.json().catch(()=>{throw new Error('Unable to prepare preview. Please try again.');});if(!response.ok||!json.success)throw new Error(json.data?.message||'Preview failed.');message.textContent='Preview uses unsaved values; nothing is saved.';const shown=await window.cw_popup_preview(json.data);if(!shown)throw new Error('The image could not be displayed. Check the image and try again.');}
  catch(error){message.textContent=error.message;}finally{button.disabled=false;button.focus();}
 });
})();
