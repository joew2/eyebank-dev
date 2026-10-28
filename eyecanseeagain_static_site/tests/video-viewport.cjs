const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.CHROME_PATH?{executablePath:process.env.CHROME_PATH}:{})});
 const page=await browser.newPage();
 await page.route('https://player.vimeo.com/**',route=>route.fulfill({status:200,contentType:'text/html',body:'<!doctype html><title>Video frame test</title>'}));
 await page.goto(process.env.SITE_URL || 'http://127.0.0.1:8765/');
 for(const [width,height] of [[1920,1080],[1440,900],[1366,768],[1280,600],[1024,600],[844,390],[390,844],[320,568]]){
  await page.setViewportSize({width,height});
  for(const key of ['adam','yolanda','annette','jamal','alyssa','greyson','ellen']){
   await page.locator('[data-video='+key+']').click();await page.waitForTimeout(240);
   const box=await page.locator('#video-dialog').boundingBox();const close=await page.locator('#video-dialog .dialog-close').boundingBox();
   for(const r of [box,close])if(r.x<0||r.y<0||r.x+r.width>width+.5||r.y+r.height>height+.5)throw Error('Popup outside viewport '+JSON.stringify({width,height,key,box,close}));
   if(Math.abs(box.width/box.height-16/9)>.02)throw Error('Video aspect ratio changed');
   if(await page.locator('.video-fallback').count())throw Error('Fallback message remains');
   await page.keyboard.press('Escape');await page.waitForTimeout(200);
   if(await page.locator('#video-player iframe').count())throw Error('Player not removed');
  }
  console.log(width+'×'+height+': all seven video players and close buttons fit');
 }
 await browser.close();
})();
