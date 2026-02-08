const puppeteer = require('puppeteer');
const { spawn } = require('child_process');
const path = require('path');

(async () => {
    // 1. Start PHP Server
    const phpServer = spawn('php', ['-S', 'localhost:9002', '-t', 'tests/fixtures']);
    
    // Wait for server to start
    await new Promise(resolve => setTimeout(resolve, 1000));
    console.log('started php server');

    const browser = await puppeteer.launch({
        headless: "new",
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors']
    });

    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });

        // 2. Login
        console.log('Logging in...');
        await page.goto('https://knowledge.cope.zone/wp-login.php', { waitUntil: 'networkidle0' });
        await page.type('#user_login', 'admin');
        await page.type('#user_pass', 'stc54');
        await Promise.all([
            page.click('#wp-submit'),
            page.waitForNavigation({ waitUntil: 'networkidle0' })
        ]);

        // 3. Go to Ingestion Page
        console.log('Navigating to Ingestion page...');
        await page.goto('https://knowledge.cope.zone/wp-admin/admin.php?page=knowledge-ingestion', { waitUntil: 'networkidle0' });

        // 4. Ingest URL
        console.log('Ingesting URL...');
        await page.type('#url', 'http://localhost:9002/test-article.html');
        await Promise.all([
            page.click('#submit'),
            page.waitForNavigation({ waitUntil: 'networkidle0' })
        ]);

        // 5. Verify Success Message
        const successNotice = await page.$('.notice-success');
        if (successNotice) {
            const text = await page.evaluate(el => el.textContent, successNotice);
            console.log('✅ Success Notice Found:', text);
        } else {
            console.error('❌ Failure: No success notice found.');
            await page.screenshot({ path: 'tests/e2e/screenshots/ingestion_fail.png' });
            throw new Error('Ingestion failed');
        }

        // 6. Check Versions List
        console.log('Checking Versions list...');
        await page.goto('https://knowledge.cope.zone/wp-admin/edit.php?post_type=kb_version', { waitUntil: 'networkidle0' });
        
        // Look for the "Test Article" in the table
        const found = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a'));
            return links.some(link => link.textContent.includes('Test Article'));
        });
        
        if (found) {
            console.log('✅ Found "Test Article" in Versions list.');
        } else {
            console.error('❌ "Test Article" not found in Versions list.');
            await page.screenshot({ path: 'tests/e2e/screenshots/versions_list_fail.png' });
            throw new Error('Version verification failed');
        }

    } catch (error) {
        console.error('❌ Error during test:', error);
        // await page.screenshot({ path: 'tests/e2e/screenshots/error.png' }); // page might be undefined
    } finally {
        await browser.close();
        phpServer.kill();
        console.log('Test finished.');
    }
})();
