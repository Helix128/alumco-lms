import { mkdir, writeFile } from 'node:fs/promises';
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import { chromium } from 'playwright';

const baseUrl = process.env.LIGHTHOUSE_BASE_URL ?? 'http://localhost';
const routes = process.env.LIGHTHOUSE_ROUTES?.split(',').filter(Boolean)
    ?? ['/login', '/ayuda', '/certificados/verificar'];
const profiles = {
    mobile: {
        formFactor: 'mobile',
        screenEmulation: { mobile: true, width: 375, height: 667, deviceScaleFactor: 1, disabled: false },
        performance: 0.85,
        throttling: undefined,
    },
    desktop: {
        formFactor: 'desktop',
        screenEmulation: { mobile: false, width: 1440, height: 900, deviceScaleFactor: 1, disabled: false },
        performance: 0.90,
        throttling: {
            rttMs: 40,
            throughputKbps: 10_240,
            requestLatencyMs: 0,
            downloadThroughputKbps: 0,
            uploadThroughputKbps: 0,
            cpuSlowdownMultiplier: 1,
        },
    },
};
const selectedProfiles = Object.entries(profiles)
    .filter(([name]) => ! process.env.LIGHTHOUSE_PROFILES || process.env.LIGHTHOUSE_PROFILES.split(',').includes(name));

const chrome = await chromeLauncher.launch({
    chromePath: chromium.executablePath(),
    chromeFlags: ['--headless=new', '--no-sandbox', '--disable-dev-shm-usage'],
});
const results = [];

try {
    for (const route of routes) {
        for (const [profile, settings] of selectedProfiles) {
            for (let run = 1; run <= 3; run += 1) {
                const result = await lighthouse(new URL(route, baseUrl).href, {
                    port: chrome.port,
                    output: 'json',
                    logLevel: 'error',
                    onlyCategories: ['performance', 'accessibility'],
                }, {
                    extends: 'lighthouse:default',
                    settings: {
                        formFactor: settings.formFactor,
                        screenEmulation: settings.screenEmulation,
                        throttlingMethod: 'simulate',
                        ...(settings.throttling ? { throttling: settings.throttling } : {}),
                    },
                });
                const lhr = result.lhr;
                results.push({
                    route,
                    profile,
                    run,
                    performance: lhr.categories.performance.score,
                    accessibility: lhr.categories.accessibility.score,
                    lcpMs: lhr.audits['largest-contentful-paint'].numericValue,
                    cls: lhr.audits['cumulative-layout-shift'].numericValue,
                    tbtMs: lhr.audits['total-blocking-time'].numericValue,
                });
            }
        }
    }
} finally {
    await chrome.kill();
}

const medians = [];
for (const route of routes) {
    for (const [profile, settings] of selectedProfiles) {
        const values = results.filter((item) => item.route === route && item.profile === profile);
        const median = (key) => values.map((item) => item[key]).sort((a, b) => a - b)[1];
        const summary = {
            route,
            profile,
            performance: median('performance'),
            accessibility: median('accessibility'),
            lcpMs: median('lcpMs'),
            cls: median('cls'),
            tbtMs: median('tbtMs'),
        };
        summary.passed = summary.performance >= settings.performance
            && summary.lcpMs <= 2500
            && summary.cls <= 0.1
            && summary.tbtMs <= 200;
        medians.push(summary);
        console.log(`${summary.passed ? 'OK' : 'EXCEDE'} ${route} ${profile}: rendimiento ${Math.round(summary.performance * 100)}, LCP ${Math.round(summary.lcpMs)} ms, CLS ${summary.cls.toFixed(3)}, TBT ${Math.round(summary.tbtMs)} ms`);
    }
}

await mkdir('docs/audits', { recursive: true });
await writeFile('docs/audits/performance-summary.json', `${JSON.stringify({ generatedAt: new Date().toISOString(), medians, runs: results }, null, 2)}\n`);
if (medians.some((item) => !item.passed)) process.exitCode = 1;
