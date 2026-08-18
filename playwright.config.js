import { defineConfig } from '@playwright/test';

const browsers = ['chromium', 'firefox', 'webkit'];
const sizes = [
    ['mobile', { width: 375, height: 667 }],
    ['tablet-portrait', { width: 768, height: 1024 }],
    ['tablet-landscape', { width: 1024, height: 768 }],
    ['desktop', { width: 1440, height: 900 }],
];

export default defineConfig({
    testDir: './tests/Browser',
    timeout: 60_000,
    expect: { timeout: 8_000 },
    fullyParallel: false,
    workers: 1,
    reporter: [['line'], ['html', { open: 'never' }]],
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost',
        locale: 'es-CL',
        reducedMotion: 'reduce',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: browsers.flatMap((browserName) => sizes.map(([sizeName, viewport]) => ({
        name: `${browserName}-${sizeName}`,
        use: { browserName, viewport },
    }))),
});
