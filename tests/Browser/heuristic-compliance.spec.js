import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const credentials = {
    trabajador: ['auditoria.trabajador@alumco.local', '/cursos'],
    capacitador: ['auditoria.capacitador@alumco.local', '/capacitador'],
    administrador: ['auditoria.admin@alumco.local', '/admin/dashboard'],
    desarrollador: ['auditoria.dev@alumco.local', '/dev/salud-lms'],
};

async function assertAccessible(page, label) {
    await expect(page.locator('main')).toBeVisible();
    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

    expect(results.violations, `${label}: ${results.violations.map((item) => `${item.id} (${item.nodes.length})`).join(', ')}`).toEqual([]);
}

async function login(page, email) {
    await page.goto('/login');
    await page.getByLabel('Correo electrónico').fill(email);
    await page.locator('#password').fill('password');
    await page.getByRole('button', { name: /acceder/i }).click();
    await page.waitForLoadState('networkidle');
}

test('superficies públicas accesibles, responsivas y sin enlaces rotos', async ({ page, request }) => {
    for (const path of ['/login', '/ayuda', '/ayuda/iniciar-sesion', '/certificados/verificar', '/soporte/contacto']) {
        const response = await page.goto(path);
        expect(response?.status(), path).toBeLessThan(400);
        await assertAccessible(page, path);
    }

    await page.goto('/ayuda');
    const hrefs = await page.locator('a[href]').evaluateAll((links) => [...new Set(links.map((link) => link.href))]);
    for (const href of hrefs) {
        const url = new URL(href);
        if (url.origin !== new URL(page.url()).origin) continue;
        const response = await request.get(url.href, { maxRedirects: 5 });
        expect(response.status(), `Enlace roto: ${url.pathname}`).toBeLessThan(400);
    }
});

test('recorridos de colaborador, capacitador, administrador y desarrollador', async ({ page }) => {
    test.setTimeout(180_000);
    for (const [role, [email, expectedPath]] of Object.entries(credentials)) {
        await login(page, email);
        await expect(page).toHaveURL(new RegExp(`${expectedPath.replace('/', '\\/')}(?:$|\\?)`));
        await assertAccessible(page, role);

        const helpLink = page.getByRole('link', { name: /ayuda/i }).first();
        await expect(helpLink).toBeVisible();
        await helpLink.click();
        await expect(page).toHaveURL(/\/ayuda/);
        await assertAccessible(page, `${role}: ayuda`);

        await page.goto('/');
        const logout = page.locator('form[action$="/logout"]').first();
        await expect(logout).toBeAttached();
        await logout.evaluate((form) => form.submit());
        await expect(page).toHaveURL(/\/login/);
    }
});

test('errores de formulario conservan datos y enfocan el resumen', async ({ page }, testInfo) => {
    await page.goto('/login');
    const auditId = `${testInfo.project.name}-${Date.now()}`.replace(/[^a-z0-9-]/gi, '-');
    await page.getByLabel('Correo electrónico').fill(`no-existe-${auditId}@alumco.local`);
    await page.locator('#password').fill('incorrecta');
    await page.getByRole('button', { name: /acceder/i }).click();

    const email = page.getByLabel('Correo electrónico');
    await expect(email).toHaveAttribute('aria-invalid', 'true');
    await expect(page.locator('[data-error-summary]')).toBeFocused();
});

test('controles táctiles accionables respetan 44 por 44 píxeles', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name.includes('desktop'), 'La comprobación táctil se ejecuta en viewports compactos.');
    await page.goto('/login');

    const controls = page.locator('button:visible, input:not([type="checkbox"]):visible, label:has(input[type="checkbox"]):visible, a:visible');
    const count = await controls.count();
    for (let index = 0; index < count; index += 1) {
        const box = await controls.nth(index).boundingBox();
        if (!box) continue;
        const element = await controls.nth(index).evaluate((node) => node.outerHTML.slice(0, 180));
        expect(Math.round(box.height), `Control demasiado bajo: ${element}`).toBeGreaterThanOrEqual(44);
    }
});
