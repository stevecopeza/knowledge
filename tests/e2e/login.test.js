const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: "new", // Use new headless mode
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors'] // Sandbox args for safety + ignore cert errors for local dev
  });
  const page = await browser.newPage();

  // Set viewport to a reasonable desktop size
  await page.setViewport({ width: 1280, height: 800 });

  try {
    console.log('Navigating to login page...');
    await page.goto('https://knowledge.cope.zone/wp-login.php', { waitUntil: 'networkidle0' });

    console.log('Typing credentials...');
    await page.type('#user_login', 'admin');
    await page.type('#user_pass', 'stc54');

    console.log('Clicking login...');
    await Promise.all([
      page.click('#wp-submit'),
      page.waitForNavigation({ waitUntil: 'networkidle0' }),
    ]);

    console.log('Logged in. Checking for "Knowledge" menu...');
    
    // Take a screenshot of the dashboard
    await page.screenshot({ path: 'tests/e2e/screenshots/dashboard.png' });

    // Look for the menu item
    // The menu slug is 'knowledge-main', usually ends up as an ID #toplevel_page_knowledge-main
    const menuExists = await page.$('#toplevel_page_knowledge-main');

    if (menuExists) {
      console.log('✅ SUCCESS: "Knowledge" menu item found!');
    } else {
      console.error('❌ FAILURE: "Knowledge" menu item NOT found.');
      // List all menu IDs to help debug
      const menuIds = await page.$$eval('#adminmenu > li', lis => lis.map(li => li.id));
      console.log('Available Menus:', menuIds);
    }

  } catch (error) {
    console.error('❌ Error during test:', error);
    await page.screenshot({ path: 'tests/e2e/screenshots/error.png' });
  } finally {
    await browser.close();
  }
})();
