(()=>{'use strict';
document.addEventListener('click',e=>{
 const target=e.target.closest('[data-cws-confirm]');if(target&&!window.confirm(target.dataset.cwsConfirm))e.preventDefault();
 const logo=e.target.closest('[data-cws-clr-logo]');if(logo&&window.wp?.media){e.preventDefault();const frame=wp.media({title:'Choose report logo',multiple:false,library:{type:'image'}});frame.on('select',()=>{const item=frame.state().get('selection').first().toJSON();document.getElementById('cws-clr-logo-id').value=item.id;});frame.open();}
 const clear=e.target.closest('[data-cws-link-clear]');if(clear){e.preventDefault();const tools=clear.closest('[data-cws-link-tools]');const search=tools?.querySelector('[data-cws-link-search]');if(search){search.value='';search.dispatchEvent(new Event('input'));search.focus();}}
});
document.querySelectorAll('[data-cws-link-tools]').forEach(tools=>{
 const search=tools.querySelector('[data-cws-link-search]');
 const scope=tools.closest('section')||document;
 const rows=[...scope.querySelectorAll('[data-cws-link-row]')];
 const status=tools.querySelector('[data-cws-link-status]');
 if(!search||!rows.length)return;
 const update=()=>{const q=search.value.trim().toLocaleLowerCase();let shown=0;rows.forEach(row=>{const match=!q||row.textContent.toLocaleLowerCase().includes(q);row.hidden=!match;if(match)shown++;});const selected=rows.filter(row=>row.querySelector('input[type="checkbox"]')?.checked).length;if(status)status.textContent=q?`${shown} of ${rows.length} links shown · ${selected} selected`:`${rows.length} links loaded · ${selected} selected`;};
 search.addEventListener('input',update);scope.addEventListener('change',e=>{if(e.target.matches('[data-cws-link-row] input[type="checkbox"]'))update();});update();
});
})();
