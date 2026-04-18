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

test('notes page is accessible for admin', async ({ page }) => {
  await mockSchoolApi(page);
  await loginAsAdmin(page);

  await page.getByRole('link', { name: 'Notes' }).click();
  await expect(page).toHaveURL(/\/grades$/);
  await expect(page.getByRole('heading', { name: /^Notes$/ })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Nouvelle note' })).toBeVisible();
  await expect(page.getByRole('table')).toBeVisible();
});
