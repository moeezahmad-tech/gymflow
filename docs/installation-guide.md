# GymFlow &mdash; Installation & Deployment Guide

This guide walks you through setting up GymFlow in both local development environments and production hosting servers using the modern **Next.js 15 + Neon Serverless PostgreSQL** stack.

---

## Table of Contents
1. [Prerequisites](#1-prerequisites)
2. [Local Development Setup (Blazing Fast)](#2-local-development-setup-blazing-fast)
3. [Environment Configuration (`.env`)](#3-environment-configuration-env)
4. [Database Initialization & Drizzle Migrations](#4-database-initialization--drizzle-migrations)
5. [Production Deployment to Vercel (100% Free Approach)](#5-production-deployment-to-vercel-100-free-approach)
6. [Automating Scheduled Cron Reminders](#6-automating-scheduled-cron-reminders)
7. [Default Seed Credentials & Testing Accounts](#7-default-seed-credentials--testing-accounts)
8. [Turnstile & Hardware Testing Guide](#8-turnstile--hardware-testing-guide)
9. [Troubleshooting Common Issues](#9-troubleshooting-common-issues)

---

## 1. Prerequisites

Before installing, ensure your development machine has:
* **Node.js:** v18.18+ or v20+ / v22+ / v24+ (Recommended: LTS)
* **Package Manager:** `npm` (comes with Node), `yarn`, or `pnpm`
* **Git:** Installed and configured
* **Neon PostgreSQL Database:** Free serverless account at [neon.tech](https://neon.tech/)

---

## 2. Local Development Setup (Blazing Fast)

Boot up the entire stack locally in less than 2 minutes:

### Step 1: Clone or Open the Repository
```bash
git clone https://github.com/moeezahmad-tech/gymflow.git
cd "GYM management system"
```

### Step 2: Install Node Dependencies
```bash
npm install
```
This installs Next.js 15, React 19, Drizzle ORM, `@neondatabase/serverless`, `qrcode`, `jsqr`, and Tailwind CSS.

### Step 3: Configure Your Environment File
Create your local `.env` file from `.env.example`:
```bash
cp .env.example .env
```
Ensure your `DATABASE_URL` is set:
```ini
DATABASE_URL="postgresql://neondb_owner:npg_uy2nUHwhvL9s@ep-patient-shape-bcvc57iq-pooler.c-16.us-east-1.aws.neon.tech/neondb?sslmode=require"
DIRECT_URL="postgresql://neondb_owner:npg_uy2nUHwhvL9s@ep-patient-shape-bcvc57iq.c-16.us-east-1.aws.neon.tech/neondb?sslmode=require"
```

### Step 4: Start the Development Server
```bash
npm run dev
```
Open your browser at **[http://localhost:3000](http://localhost:3000)**.

---

## 3. Environment Configuration (`.env`)

GymFlow utilizes a clean, production-grade `.env` structure:

```ini
# ==============================================================================
# GymFlow Core Serverless Environment Variables
# ==============================================================================

# Application Environment
NODE_ENV=development
APP_NAME="GymFlow"
APP_URL="http://localhost:3000"

# Neon Serverless PostgreSQL (Pooled for Server Actions & API Routes - Port 5432)
DATABASE_URL="postgresql://neondb_owner:npg_uy2nUHwhvL9s@ep-patient-shape-bcvc57iq-pooler.c-16.us-east-1.aws.neon.tech/neondb?sslmode=require"

# Direct Neon Connection (for Drizzle Migrations)
DIRECT_URL="postgresql://neondb_owner:npg_uy2nUHwhvL9s@ep-patient-shape-bcvc57iq.c-16.us-east-1.aws.neon.tech/neondb?sslmode=require"

# Authentication Secret (Used to sign cookies and tokens)
AUTH_SECRET="gymflow_secure_production_secret_key_2026_xyz"

# Automated Cron Worker Security Secret
CRON_SECRET="gymflow_cron_bearer_token_2026"

# Club Contact Information (Displayed across public site & WhatsApp triggers)
NEXT_PUBLIC_GYM_PHONE="+92 325 9491349"
NEXT_PUBLIC_GYM_WHATSAPP="923259491349"
NEXT_PUBLIC_GYM_ADDRESS="Plot 42-C, Commercial Sector 4, Metropolis Boulevard, Islamabad"
```

---

## 4. Database Initialization & Drizzle Migrations

The database schema is organized into clean, modular files in `database/schema/`:

To push schema changes directly to your Neon PostgreSQL instance:
```bash
npx drizzle-kit push
```

To inspect your database schema visually using Drizzle Studio:
```bash
npx drizzle-kit studio
```
This launches a visual database explorer on `https://local.drizzle.studio`.

---

## 5. Production Deployment to Vercel (100% Free Approach)

Deploying to Vercel delivers zero server maintenance, global edge caching, automated SSL, and **$0.00/month** operational cost:

### Step 1: Push Code to GitHub
```bash
git add .
git commit -m "feat: complete nextjs 15 gymflow stack"
git push origin main
```

### Step 2: Import Project in Vercel
1. Log in to [vercel.com](https://vercel.com/) and click **"Add New Project"**.
2. Select your `gymflow` repository.
3. Framework Preset: **Next.js** (auto-detected).
4. Add your Environment Variables:
   - `DATABASE_URL`
   - `DIRECT_URL`
   - `AUTH_SECRET`
   - `CRON_SECRET`
5. Click **"Deploy"**.

### Step 3: Production Build Verification
Vercel executes `npm run build` and optimizes all 25 routes as static edge pages or serverless functions.

---

## 6. Automating Scheduled Cron Reminders

GymFlow includes a scheduled billing reminder worker at `/api/cron/payment-reminders`.

In `vercel.json`:
```json
{
  "crons": [
    {
      "path": "/api/cron/payment-reminders",
      "schedule": "0 8 * * *"
    }
  ]
}
```
Vercel automatically calls this endpoint every morning at 08:00 AM UTC. It checks for subscriptions nearing the **10th-of-the-month cutoff**, identifies overdue accounts, and logs WhatsApp notification payloads.

---

## 7. Default Seed Credentials & Testing Accounts

The Neon PostgreSQL database includes pre-seeded accounts for testing:

| Role | Member ID | Name | Email | Password | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Director / Admin** | `2026-1` | Admin Director | `admin@gymflow.com` | `admin123` | `active` |
| **Front Desk Staff** | `2026-2` | Front Desk Staff | `staff@gymflow.com` | `staff123` | `active` |
| **Active Athlete** | `2026-3` | Active Athlete | `athlete@gymflow.com` | `athlete123` | `active` |
| **VIP Athlete** | `2026-4` | Moeez Ahmad | `moeezahmad.tech@gmail.com` | `athlete123` | `active` |

---

## 8. Turnstile & Hardware Testing Guide

### A. Testing on Smartphone (iOS Camera / Android Google Lens)
1. Open [`http://localhost:3000/app`](http://localhost:3000/app) on a computer or tablet.
2. Go to the **Pass** tab to display the real 2D QR matrix for `2026-4`.
3. Point your smartphone camera at the screen.
4. Tap the detected URL pop-up it opens the `/scan` verification page, plays the audio chime, and unlocks the turnstile gate HUD.

### B. Testing with Reception Webcam / Optical Scanner
1. Open [`http://localhost:3000/scan`](http://localhost:3000/scan).
2. Click **Start Camera** to activate the webcam or mobile rear camera.
3. Aim the camera at any athlete pass it decodes the frame in milliseconds, unlatches the gate relay, and logs the attendance entry to Neon DB.

### C. Testing with Physical USB Barcode Scanner Gun
1. Plug any 2D USB barcode scanner gun into your computer.
2. Open [`http://localhost:3000/scan`](http://localhost:3000/scan) or [`http://localhost:3000/admin/attendance`](http://localhost:3000/admin/attendance).
3. Scan the QR code. The built-in keyboard wedge listener captures the keystrokes and processes the check-in immediately.

---

## 9. Troubleshooting Common Issues

### Issue 1: "DATABASE_URL is not set"
* **Solution:** Ensure your `.env` file exists in the project root and contains the `DATABASE_URL` key with `sslmode=require`.

### Issue 2: Camera Permissions Denied in `/scan`
* **Solution:** Webcams require either `localhost` or a valid HTTPS domain (such as Vercel) to grant camera access.

### Issue 3: Stale Build Cache
* **Solution:** Purge the `.next` directory:
  ```powershell
  Remove-Item -Path .next -Recurse -Force
  npm run build
  ```
