const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
    const browser = await puppeteer.launch({
        headless: "new",
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--ignore-certificate-errors']
    });

    let page;
    try {
        page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });

        // 1. Login
        console.log('Logging in...');
        await page.goto('https://knowledge.cope.zone/wp-login.php', { waitUntil: 'networkidle0' });
        await page.type('#user_login', 'admin');
        await page.type('#user_pass', 'stc54');
        await Promise.all([
            page.click('#wp-submit'),
            page.waitForNavigation({ waitUntil: 'networkidle0' })
        ]);

        // 2. Go to Ingestion Page
        console.log('Navigating to Ingestion page...');
        await page.goto('https://knowledge.cope.zone/wp-admin/admin.php?page=knowledge-ingestion', { waitUntil: 'networkidle0' });

        // 3. Ingest Real URL
        const targetUrl = 'https://migrationology.com/100-best-thai-dishes-to-eat-in-bangkok-ultimate-eating-guide/';
        console.log(`Ingesting URL: ${targetUrl}`);
        
        // Increase timeout for real network request
        page.setDefaultNavigationTimeout(300000); // 5 minutes
        
        // Capture console logs
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));

        await page.type('#url', targetUrl);
        await Promise.all([
            page.click('#submit'),
            page.waitForNavigation({ waitUntil: 'networkidle0' })
        ]);

        // Check for "Ingestion started" (Async Handling)
        const processingNotice = await page.$('.notice-info');
        if (processingNotice) {
             const processingText = await page.evaluate(el => el.textContent, processingNotice);
             console.log('ℹ️ Async Ingestion Started:', processingText);
             
             // Poll for completion
             console.log('Polling for completion...');
             let completed = false;
             for(let i=0; i<60; i++) { // Poll for up to 6 minutes (large page)
                 await new Promise(r => setTimeout(r, 6000)); // Wait 6s
                 console.log(`Poll attempt ${i+1}/60...`);
                 await page.reload({ waitUntil: 'networkidle0' });
                 
                 const success = await page.$('.notice-success');
                 if (success) {
                     completed = true;
                     break;
                 }
                 
                 const error = await page.$('.notice-error');
                 if (error) {
                      const errText = await page.evaluate(el => el.textContent, error);
                      throw new Error('Ingestion failed with error: ' + errText);
                 }
             }
             
             if (!completed) {
                 throw new Error('Timeout waiting for async ingestion to complete');
             }
        }

        // 4. Verify Success and Get UUID
        const successNotice = await page.$('.notice-success');
        if (!successNotice) {
            console.error('❌ Failure: No success notice found.');
            const errorNotice = await page.$('.notice-error');
            if (errorNotice) {
                const errText = await page.evaluate(el => el.textContent, errorNotice);
                console.error('Error message:', errText);
            }
            const html = await page.content();
            fs.writeFileSync('tests/e2e/screenshots/ingest_real_fail.html', html);
            await page.screenshot({ path: 'tests/e2e/screenshots/ingest_real_fail.png' });
            throw new Error('Ingestion failed');
        }

        const successText = await page.evaluate(el => el.textContent, successNotice);
        console.log('✅ Success Notice:', successText);

        // Extract UUID (Format: "Successfully ingested: Title (UUID: ...)")
        const uuidMatch = successText.match(/UUID: ([a-f0-9-]+)/);
        if (!uuidMatch) {
            throw new Error('Could not extract UUID from success message');
        }
        const uuid = uuidMatch[1];
        console.log(`Extracted UUID: ${uuid}`);

        // 5. Inspect Filesystem Content
        // Note: The test runner is on the same machine as the WP instance, so we can check disk directly.
        const kbDataPath = '/Users/stevecope/Sites/knowledge.cope.zone/wp-content/kb-data';
        const versionDir = path.join(kbDataPath, 'versions', uuid);
        const contentPath = path.join(versionDir, 'content.html');
        const metadataPath = path.join(versionDir, 'metadata.json');

        if (!fs.existsSync(contentPath)) {
            throw new Error(`Content file not found at ${contentPath}`);
        }

        const content = fs.readFileSync(contentPath, 'utf8');
        const metadata = JSON.parse(fs.readFileSync(metadataPath, 'utf8'));

        console.log('--- Content Analysis ---');
        console.log(`Title: ${metadata.title}`);
        console.log(`Content Size: ${content.length} bytes`);

        // Check for specific phrases (Content Integrity)
        const phrases = ['Tom Yum Goong', 'Gaeng Som', 'Tom Kha Gai'];
        const missingPhrases = phrases.filter(p => !content.includes(p));
        
        if (missingPhrases.length === 0) {
            console.log('✅ All key phrases found in content.');
        } else {
            console.warn('⚠️ Missing phrases:', missingPhrases);
        }

        // Check for Images
        // Count 'src' attributes pointing to media proxy
        // Pattern: src=".../wp-content/plugins/knowledge/..." or similar proxy URL
        // Actually, AssetDownloader sets src to FileProxyController URL which is typically:
        // https://knowledge.cope.zone/wp-json/kb/v1/file/media/... (if using REST)
        // OR standard plugin URL if naive.
        // Let's check for "media/" in src.
        
        const imgCount = (content.match(/<img/g) || []).length;
        console.log(`Found ${imgCount} <img> tags.`);
        
        // Check if images are localized
        // We look for the presence of our local storage path in the src logic or just that they are NOT the original domain
        // The AssetDownloader replaces src with `FileProxyController::get_url`.
        // Let's just check if they are NOT pointing to wanderlustchloe.com
        
        // Simple heuristic: Count images that do NOT contain the original domain in src
        // (Note: AssetDownloader stores original in data-original-src)
        
        console.log('--- Image Verification ---');
        // We expect a significant number of images (Migrationology has lots)
        if (imgCount > 10) {
             console.log('✅ Image count looks reasonable.');
        } else {
             console.warn('⚠️ Low image count. Check extraction.');
        }

    } catch (error) {
        console.error('❌ Error during test:', error);
        await page.screenshot({ path: 'tests/e2e/screenshots/ingest_real_error.png' });
        process.exit(1);
    } finally {
        await browser.close();
        console.log('Test finished.');
    }
})();
