/* ATP_URL=https://Eyebank:8890/ ATP_PLAYWRIGHT=/path/to/playwright ATP_CHROME=/path/to/chrome node tests/viewport-bottom.cjs */
const {chromium}=require(process.env.ATP_PLAYWRIGHT||'playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.ATP_CHROME||undefined,headless:true});
 const page=await browser.newPage({ignoreHTTPSErrors:true,viewport:{width:1440,height:900}});
 const response=await page.goto(process.env.ATP_URL||'http://localhost/',{waitUntil:'domcontentloaded',timeout:90000});
 const html=await response.text();
 const widget=page.locator('#cwsatp-widget');
 async function assertPosition(gap,side='right'){
  const box=await widget.boundingBox(),viewport=page.viewportSize();
  const sideGap=side==='right'?viewport.width-box.x-box.width:box.x;
  if(Math.abs(viewport.height-box.y-box.height-gap)>1||Math.abs(sideGap-5)>1)throw Error('Incorrect viewport gap '+JSON.stringify(box));
  if(await widget.evaluate(el=>getComputedStyle(el).position)!=='fixed')throw Error('Not fixed');
  const before=box.y;await page.evaluate(()=>scrollTo(0,1000));await page.waitForTimeout(350);
  if(Math.abs((await widget.boundingBox()).y-before)>1)throw Error('Widget moved on scroll');
 }
 async function assertMenus(){
  for(const selector of ['.cwsatp-trigger','.cwsatp-language-trigger']){
   const trigger=widget.locator(selector);if(!await trigger.count())continue;
   await trigger.click();const menu=widget.locator(selector==='.cwsatp-trigger'?'#cwsatp-panel':'#cwsatp-languages');
   const box=await menu.boundingBox(),viewport=page.viewportSize();
   if(box.x<0||box.y<0||box.x+box.width>viewport.width+1||box.y+box.height>viewport.height+1)throw Error('Menu outside viewport');
   await page.keyboard.press('Escape');
  }
 }
 for(const width of [1440,1920,1024,390]){
  await page.setViewportSize({width,height:900});await page.waitForTimeout(400);
  await assertPosition(5);await assertMenus();console.log(width,'bottom viewport, scroll and menus passed');
 }
 await page.setViewportSize({width:1440,height:900});
 // Test alternative settings through rendered HTML fixtures without changing database settings.
 let fixture=html;
 await page.route('**/*',async route=>{
  if(route.request().isNavigationRequest()&&route.request().frame()===page.mainFrame())await route.fulfill({status:200,contentType:'text/html',body:fixture});
  else await route.continue();
 });
 fixture=html.replace('class="cwsatp-side-right"','class="cwsatp-side-left"').replace('data-mobile-bottom="1"','data-mobile-bottom="0"').replace(/--cwsatp-bottom:\d+px/,'--cwsatp-bottom:37px');
 await page.reload({waitUntil:'domcontentloaded'});await assertPosition(37,'left');await assertMenus();
 await page.setViewportSize({width:390,height:700});await page.waitForTimeout(350);await assertPosition(37,'left');await assertMenus();
 console.log('Left side, adjustable gap and disabled mobile override passed');
 await page.setViewportSize({width:1440,height:900});
 fixture=html.replace('data-placement="viewport-bottom"','data-placement="screen"');await page.reload({waitUntil:'domcontentloaded'});
 let before=await widget.boundingBox();await page.evaluate(()=>scrollTo(0,1000));await page.waitForTimeout(350);if(Math.abs((await widget.boundingBox()).y-before.y)>1)throw Error('Legacy top mode moved');
 fixture=html.replace('data-placement="viewport-bottom"','data-placement="footer"');await page.reload({waitUntil:'domcontentloaded'});await page.evaluate(()=>scrollTo(0,document.documentElement.scrollHeight));await page.waitForTimeout(350);before=await widget.boundingBox();await page.evaluate(()=>scrollBy(0,-100));await page.waitForTimeout(350);if(Math.abs((await widget.boundingBox()).y-before.y-100)>2)throw Error('Footer mode no longer follows document');
 console.log('Existing top and footer modes preserved');await browser.close();
})().catch(error=>{console.error(error);process.exit(1);});
