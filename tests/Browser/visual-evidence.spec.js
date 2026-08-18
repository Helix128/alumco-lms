import { expect, test } from '@playwright/test';
import { mkdir } from 'node:fs/promises';

const evidenceDirectory = 'docs/audits/screenshots';

async function capture(page, name) {
    await page.waitForLoadState('domcontentloaded');
    await page.evaluate(() => document.fonts.ready);
    await page.screenshot({ path: `${evidenceDirectory}/${name}.png`, fullPage: true });
}

async function login(page, email) {
    await page.goto('/login');
    await page.getByLabel('Correo electrónico').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: /acceder/i }).click();
}

async function logout(page) {
    const form = page.locator('form[action$="/logout"]').first();
    await Promise.all([
        page.waitForURL(/\/login/),
        form.evaluate((element) => element.submit()),
    ]);
}

test('capturas versionables de las rutas críticas', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'chromium-desktop', 'Una captura canónica evita duplicar evidencia entre matrices.');
    test.setTimeout(180_000);
    await mkdir(evidenceDirectory, { recursive: true });

    await page.goto('/login');
    await capture(page, '01-login');
    await page.goto('/ayuda');
    await capture(page, '02-ayuda');
    await page.goto('/certificados/verificar');
    await capture(page, '03-verificacion-certificados');

    await login(page, 'auditoria.trabajador@alumco.local');
    await capture(page, '04-capacitaciones');
    await page.goto('/calendario-cursos');
    await capture(page, '05-calendario-colaborador');
    await page.goto('/mis-certificados');
    await capture(page, '06-certificados');
    await logout(page);

    await login(page, 'auditoria.capacitador@alumco.local');
    await page.goto('/capacitador/cursos');
    await capture(page, '07-contenido-capacitador');
    const courseHref = await page.locator('a[href*="/capacitador/cursos/"]').evaluateAll((links) => links
        .map((link) => link.href)
        .find((href) => /\/capacitador\/cursos\/\d+$/.test(href)));
    expect(courseHref).toBeTruthy();
    await page.goto(courseHref);
    await capture(page, '08a-estructura-capacitacion');
    const evaluationHref = await page.locator('a[href$="/evaluacion"]').first().getAttribute('href');
    expect(evaluationHref).toBeTruthy();
    await page.goto(evaluationHref);
    await capture(page, '08b-editor-evaluacion');
    await page.goto('/capacitador/calendario');
    await capture(page, '08-calendario-planificacion');
    await logout(page);

    await login(page, 'auditoria.admin@alumco.local');
    await page.goto('/admin/reportes');
    await capture(page, '09-reportes');
    await page.goto('/admin/usuarios');
    await capture(page, '10-usuarios');
    await logout(page);

    await login(page, 'auditoria.dev@alumco.local');
    await capture(page, '11-herramientas-tecnicas');
    await expect(page.locator('main')).toBeVisible();
});
