(()=>{
'use strict';
const config=window.cwshrBulk;
const button=document.getElementById('cwshr-bulk-process');
if(!config||!button)return;
const agree=document.getElementById('cwshr-bulk-agree');
const status=document.getElementById('cwshr-bulk-status');
const progress=document.getElementById('cwshr-bulk-progress');
const list=document.getElementById('cwshr-bulk-results');
let busy=false;
progress.max=config.steps.length;
function render(job){
 const results=job.results||{};
 let completed=0,failed=0;
 list.replaceChildren();
 config.steps.forEach(step=>{
  const result=results[step.id];
  if(!result)return;
  if(result.status==='completed')completed++;
  if(result.status==='failed')failed++;
  const item=document.createElement('li');
  item.textContent=step.label+' — '+(result.status==='failed'?'Needs attention':result.status==='completed'?'Done':'Processing')+(result.message?': '+result.message:'');
  if(result.status==='failed')item.style.color='#b32d2e';
  list.append(item);
 });
 progress.value=completed+failed;
 if(job.status==='completed'&&completed!==config.steps.length)status.textContent='A new update is available. Check the agreement and click Restore homepage.';
 else if(job.status==='completed')status.textContent='Homepage restored. Clear the site cache and review the homepage.';
 else if(job.status==='needs_attention')status.textContent=completed+' updates completed; '+failed+' need attention. Fix the messages below, then click Restore homepage to retry only unfinished updates.';
 else status.textContent=completed+' of '+config.steps.length+' updates completed. Processing…';
}
async function request(mode,job){
 const controller=new AbortController();
 const timeout=setTimeout(()=>controller.abort(),150000);
 try{
  const response=await fetch(ajaxurl,{method:'POST',credentials:'same-origin',signal:controller.signal,body:new URLSearchParams({action:'cwshr_process_all',_ajax_nonce:config.nonce,agree:'1',mode,job:job||''})});
  const text=await response.text();
  let result;try{result=JSON.parse(text);}catch(_){throw new Error('The server did not return a valid update response (HTTP '+response.status+'). Reload this screen and click Restore homepage to resume.');}
  if(!result.success)throw new Error(result.data&&result.data.message||'The update request failed. Reload and resume.');
  return result.data;
 }finally{clearTimeout(timeout);}
}
button.addEventListener('click',async()=>{
 if(busy)return;
 if(!agree.checked){status.textContent='Check the backup and replacement agreement first.';agree.focus();return;}
 busy=true;button.disabled=true;agree.disabled=true;status.textContent='Starting updates…';
 try{
  let job=await request('start');render(job);
  while(job.status==='running'){job=await request('tick',job.id);render(job);}
 }catch(error){status.textContent=error.name==='AbortError'?'The server took too long. Reload this screen and click Restore homepage to resume.':error.message;}
 finally{busy=false;button.disabled=false;agree.disabled=false;}
});
window.addEventListener('beforeunload',event=>{if(busy){event.preventDefault();event.returnValue='';}});
if(config.job&&config.job.id){render(config.job);if(config.job.status==='running')status.textContent='An update session is unfinished. Check the agreement and click Restore homepage to resume.';}
})();
