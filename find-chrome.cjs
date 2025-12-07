const chromium = require('@sparticuz/chromium');

(async () => {
    try {
        const path = await chromium.executablePath();
        console.log(path);
    } catch (e) {
        console.error(e);
        process.exit(1);
    }
})();
