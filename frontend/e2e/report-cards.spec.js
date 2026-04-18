import { expect, test } from '@playwright/test';
import {
  ADMIN_EMAIL,
  ADMIN_PASSWORD,
  mockSchoolApi,
} from './helpers/mockBackend';

async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.getByLabel('Email').fill(ADMIN_EMAIL);
  await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD);
  await page.getByRole('button', { name: 'Se connecter' }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
}

test('bulletins page renders list and print action', async ({ page }) => {
  await mockSchoolApi(page);
  await loginAsAdmin(page);

  await page.getByRole('link', { name: 'Bulletins' }).click();
  await expect(page).toHaveURL(/\/report-cards$/);
  await expect(page.getByRole('heading', { name: /Bulletin scolaire/i })).toBeVisible();
  await expect(page.getByRole('heading', { name: /^Bulletins$/ })).toBeVisible();
  await expect(page.getByRole('button', { name: /Imprimer ce bulletin/i })).toBeVisible();
  await expect(page.getByRole('table')).toBeVisible();
});
