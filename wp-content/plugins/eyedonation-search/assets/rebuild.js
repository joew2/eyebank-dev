(()=>{
'use strict';
const button=document.getElementById('eds-rebuild');if(!button||!window.edsRebuild)return;
const status=document.getElementById('eds-index-status'),progress=document.getElementById('eds-index-progress');let busy=false;
function show(s){progress.max=Math.max(1,s.total);progress.value=s.indexed;status.textContent='Indexed '+s.indexed+' of '+s.total+' published searchable items; '+s.missing+' missing. '+(s.running?'Rebuilding…':'Rebuild finished. Clear the site cache and test your search.');}
async function request(mode){const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),90000);try{
 const response=await fetch(ajaxurl,{method:'POST',credentials:'same-origin',signal:controller.signal,body:new URLSearchParams({action:'eds_rebuild_batch',_ajax_nonce:window.edsRebuild.nonce,mode})});
 const text=await response.text();let result;try{result=JSON.parse(text);}catch(_){throw new Error('The server returned an invalid response (HTTP '+response.status+'). Reload and click rebuild to resume.');}
 if(!result.success)throw new Error(result.data&&result.data.message||'Rebuild failed.');return result.data;
}finally{clearTimeout(timeout);}}
button.addEventListener('click',async event=>{event.preventDefault();if(busy)return;busy=true;button.disabled=true;status.textContent='Starting or resuming the search index rebuild…';try{
 let result=await request('start');show(result);
 while(result.running){await new Promise(resolve=>setTimeout(resolve,200));result=await request('batch');show(result);}
}catch(error){status.textContent=(error.name==='AbortError'?'The request timed out.':error.message)+' Click rebuild to resume unfinished work.';}
finally{busy=false;button.disabled=false;}});
window.addEventListener('beforeunload',event=>{if(busy){event.preventDefault();event.returnValue='';}});
})();
