import { expect, test } from '@playwright/test';
import {
  ADMIN_EMAIL,
  ADMIN_PASSWORD,
  mockSchoolApi,
} from './helpers/mockBackend';

async function submitLogin(page, email, password) {
  await page.goto('/login');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Mot de passe').fill(password);
  await page.getByRole('button', { name: 'Se connecter' }).click();
}

test('admin can log in and reach dashboard', async ({ page }) => {
  await mockSchoolApi(page);
  await submitLogin(page, ADMIN_EMAIL, ADMIN_PASSWORD);

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByRole('heading', { name: /Tableau de bord/i })).toBeVisible();
});

test('invalid credentials stay on login page', async ({ page }) => {
  await mockSchoolApi(page, { allowLogin: false });
  await submitLogin(page, 'invalid@school.com', 'wrong-password');

  await expect(page).toHaveURL(/\/login$/);
  await expect(page.getByRole('heading', { name: /Connexion/i })).toBeVisible();
  await expect(page.getByText('Identifiants invalides.')).toBeVisible();
});
