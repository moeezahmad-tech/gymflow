# GymFlow &mdash; Modern Gym & Fitness Management Platform

<p align="center">
  <img src="public/images/logo.png" alt="GymFlow Logo" width="220" />
</p>

<p align="center">
  <strong>The Intelligent Brain for Your Gym &bull; Intelligence-as-a-Service (IaaS)</strong><br>
  A comprehensive, high-performance gym management ecosystem featuring public marketing, 4-step online admission, member self-service portals, administrative billing & CRM, a standalone Progressive Web App (PWA), and a real optical camera turnstile verifier.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Next.js-15_(App_Router)-black?style=for-the-badge&logo=next.js&logoColor=white" alt="Next.js 15" />
  <img src="https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react&logoColor=black" alt="React 19" />
  <img src="https://img.shields.io/badge/Neon-Serverless_Postgres-00E599?style=for-the-badge&logo=postgresql&logoColor=black" alt="Neon Postgres" />
  <img src="https://img.shields.io/badge/Drizzle_ORM-Type--Safe-C5F74F?style=for-the-badge&logo=drizzle&logoColor=black" alt="Drizzle ORM" />
  <img src="https://img.shields.io/badge/Vercel-100%25_Free_Tier-black?style=for-the-badge&logo=vercel&logoColor=white" alt="Vercel Free" />
  <img src="https://img.shields.io/badge/PWA-Installable-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white" alt="PWA" />
</p>

---

## 📌 Table of Contents
1. [Overview](#-overview)
2. [Key Capabilities & Subsystems](#-key-capabilities--subsystems)
3. [Technology Stack (100% Free Serverless Architecture)](#-technology-stack-100-free-serverless-architecture)
4. [Quick Start (Local Development)](#-quick-start-local-development)
5. [Default Login Credentials & Testing IDs](#-default-login-credentials--testing-ids)
6. [Repository Structure](#-repository-structure)
7. [Comprehensive Documentation Index](#-comprehensive-documentation-index)
8. [Automated Background Reminders & Cron](#-automated-background-reminders--cron)
9. [Hardware & Turnstile Testing Guide](#-hardware--turnstile-testing-guide)

---

## 🚀 Overview

**GymFlow** is an all-in-one software solution engineered for commercial gyms, private fitness studios, and health clubs. It eliminates disparate spreadsheets and fragmented software by consolidating:

* **Public Marketing Site (`/`):** High-converting showroom with athletic branding, facilities, programs, and transparent PKR pricing (₨ 8,500 / ₨ 22,500 / ₨ 42,000).
* **4-Step Online Admission Wizard (`/admission`):** Athlete onboarding with PAR-Q medical screening, shift allocation, fee calculation, and printable voucher slip.
* **Standalone Progressive Web App (`/app`):** Installable mobile-first web app with 5 native tabs (matching `Group 2.png` Row 4), offline 2D QR pass, consistency streaks, and direct WhatsApp coach bridge.
* **Member Self-Service Portal (`/user`):** Desktop athlete dashboard with attendance history, streak engine, and digital invoice ledger.
* **Live Optical Camera Turnstile Verifier (`/scan`):** Zero-hardware turnstile gate verification running in any browser with real-time video decoding, Web Audio API sound chimes, and USB barcode wedge listener.
* **Administrative Operations Console (`/admin`):** Real-time financial analytics, 10th-cutoff monthly billing workflows, member lifecycle management, package CRUD builder, and inquiries inbox.

---

## ⚡ Key Capabilities & Subsystems

### 1. Public Marketing Portal (`/`, `/about`, `/programs`, `/facilities`, `/pricing`, `/contact`)
* **The Intelligent Brain for Your Gym:** Split device mockup showcasing the Admin Cockpit and glowing Smartphone PWA Pass.
* **Real-Time Operational Counters:** Sub-second check-in (`< 1s`), mobile app attendance visibility (`100%`), zero paper logbooks (`0`), and 24/7 cloud gate management.
* **Transparent Pricing in PKR:** Standardized plans (₨ 8,500 / ₨ 22,500 / ₨ 42,000) with strict 10th-cutoff policy FAQ.

### 2. 4-Step Online Admission Wizard (`/admission`)
* Complete digital onboarding collecting CNIC, blood group, PAR-Q medical screening, workout shifts, and local payment methods (EasyPaisa, JazzCash, Cash).
* Issues immediate allocated Member ID with real scannable 2D QR matrix and printable confirmation voucher.

### 3. Standalone Mobile Progressive Web App (`/app`)
* Dedicated mobile UI with 5 native tabs: Home, Alerts, Pass, Invoices, and Profile.
* Level H 2D QR Code generator with retina 2x rendering, offline cache, and PNG download.
* Consistency Streak Engine ("🔥 14-Day Streak • 18 Visits") to slash member churn.

### 4. Live Optical Camera Turnstile Scanner (`/scan`)
* Instant webcam and mobile camera scanning powered by `jsQR`.
* Offline Web Audio API sound synthesizers (pleasant two-tone success chime, warning buzzer).
* Physical USB barcode scanner gun keyboard wedge listener.
* Visual turnstile gate open/lock indicator with athlete dossier and real-time scan session stream.

### 5. Administrative Control Suite (`/admin`)
* **KPI Metrics Dashboard:** Revenue metrics, today's check-ins, floor occupancy gauge, and live scan stream.
* **New Admissions Manager (`/admin/admissions`):** Review athlete dossiers, verify PAR-Q medical disclosures, approve/reject, and dispatch WhatsApp welcomes.
* **Member Directory & Quick Actions (`/admin/members`):** Search by Name, Phone, CNIC, or Code (`2026-4`), with gate clearance toggle.
* **Cutoff Billing Schedule (`/admin/billing`):** Enforces 10th-cutoff fee schedules with audit ledgers for cash, cards, JazzCash, and EasyPaisa.
* **Dynamic Plan Builder (`/admin/plans`):** Configure plans, pricing in PKR, durations, and feature bullet lists.

---

## 🛠 Technology Stack: 100% Free Serverless Architecture

* **Framework & App Shell:** **Next.js 15 (App Router)** with React 19 &bull; **No separate backend server required**.
* **Database:** **Neon Serverless PostgreSQL** with built-in connection pooling and auto-scaling compute.
* **Database ORM:** **Drizzle ORM** (Fastest serverless cold-starts, modular schema in `database/schema/`).
* **Hosting & CDN:** **Vercel Hobby Tier** ($0/month with unlimited serverless executions and free SSL).
* **QR Engine:** **`qrcode`** (Level H error correction, 2x retina rendering, downloadable PNG).
* **Frame Decoder:** **`jsqr`** (Client-side camera canvas scanning for turnstile kiosks).
* **Sound Synthesizer:** **HTML5 Web Audio API** (Offline, zero-latency dual-tone chimes).
* **Styling & UI:** Tailwind CSS, dark obsidian athletic tokens, and Google Fonts (`Teko` & `Plus Jakarta Sans`).
* **Mobile / PWA:** Service Worker caching (`public/sw.js`) and Web App Manifest (`public/manifest.json`).

---

## 🏃 Quick Start (Local Development)

### Prerequisites
* **Node.js:** v18.18+ or v20+ / v22+ / v24+ LTS
* **Neon PostgreSQL Database:** Free account at [neon.tech](https://neon.tech/)

### Step-by-Step Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/moeezahmad-tech/gymflow.git
   cd "GYM management system"
   ```

2. **Install Node.js Dependencies:**
   ```bash
   npm install
   ```

3. **Configure Environment Variables:**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Ensure your `.env` contains:
   ```ini
   DATABASE_URL="postgresql://user:password@ep-your-endpoint-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require"
   DIRECT_URL="postgresql://user:password@ep-your-endpoint.us-east-1.aws.neon.tech/neondb?sslmode=require"
   ```

4. **Start the Development Server:**
   ```bash
   npm run dev
   ```
   Open **[http://localhost:3000](http://localhost:3000)** in your browser.

5. **Verify Production Build:**
   ```bash
   npm run build
   ```
   Validates all 25 routes with zero errors.

---

## 🔐 Default Login Credentials & Testing IDs

The Neon PostgreSQL database includes pre-seeded accounts:

| Role | Member ID | Name | Email | Password | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Director / Admin** | `2026-1` | Admin Director | `admin@gymflow.com` | `admin123` | `active` |
| **Front Desk Staff** | `2026-2` | Front Desk Staff | `staff@gymflow.com` | `staff123` | `active` |
| **Active Athlete** | `2026-3` | Active Athlete | `athlete@gymflow.com` | `athlete123` | `active` |
| **VIP Athlete** | `2026-4` | Moeez Ahmad | `moeezahmad.tech@gmail.com` | `athlete123` | `active` |

---

## 📁 Repository Structure

```
GYM management system/
├── public/                      # Static assets, branding & PWA configuration
│   ├── images/                  # Authentic gym photography & official logo.png
│   ├── manifest.json            # PWA manifest definition (start_url: "/app")
│   └── sw.js                    # Service worker for offline shell caching
│
├── database/                    # Modular Drizzle ORM Database Architecture
│   ├── index.ts                 # Database client initialization with Neon driver
│   └── schema/                  # Modular PostgreSQL schema definitions
│       ├── users.ts             # Users, roles (admin/staff/member), status
│       ├── plans.ts             # Packages, pricing in PKR, duration
│       ├── subscriptions.ts     # Active subscriptions & 10th cutoff dates
│       ├── payments.ts          # Ledger invoices, payment channels
│       ├── attendance.ts        # Turnstile scan logs, gate access status
│       ├── notifications.ts     # System alerts and WhatsApp reminder logs
│       ├── messages.ts          # Lead capture inquiries and contact messages
│       ├── newsletter.ts        # Newsletter subscribers and campaigns
│       └── index.ts             # Schema relations & exports
│
├── src/
│   ├── components/              # Shared Reusable Components
│   │   ├── public-header.tsx    # Header with branding & portal quick-links
│   │   ├── public-footer.tsx    # Footer with operating hours & social links
│   │   ├── admin-sidebar.tsx    # Dark obsidian admin navigation sidebar
│   │   ├── real-qr-code.tsx     # High-contrast 2D QR matrix generator with PNG save
│   │   └── pwa-register.tsx     # Client component registering Service Worker
│   │
│   └── app/                     # Next.js App Router (25 Validated Routes)
│       ├── page.tsx             # Public marketing landing page (IaaS showcase)
│       ├── admission/           # 4-step public online admission intake wizard
│       ├── app/                 # Standalone Member Mobile PWA (5 Tabs)
│       ├── user/                # Desktop Member Dashboard & Turnstile Pass
│       ├── scan/                # Live Optical Camera & Barcode Turnstile Scanner
│       ├── admin/               # Executive Admin Management Suite
│       │   ├── page.tsx         # Central Dashboard & Live Stream
│       │   ├── admissions/      # Pending admission applications & dossier review
│       │   ├── members/         # Member directory & turnstile lock toggle
│       │   ├── members-add/     # Fast 3-step member registration with QR pass
│       │   ├── billing/         # 10th-cutoff payment ledgers, multi-channel audit
│       │   ├── attendance/      # Live scan stream, desk manual override bar
│       │   ├── plans/           # Membership package builder and pricing CRUD
│       │   └── messages/        # Customer inquiry inbox and WhatsApp actions
│       └── api/                 # Serverless Route Handlers
│           ├── attendance/      # GET & POST turnstile verification and logging
│           └── cron/            # Scheduled 10th-cutoff payment reminder worker
│
└── docs/                        # Complete technical and design documentation
```

---

## 📚 Comprehensive Documentation Index

All technical, architectural, and operational manuals are consolidated inside the [`docs/`](file:///e:/Coding/GYM%20management%20system/docs/) directory:

| Document | Primary Focus |
| :--- | :--- |
| **[`docs/architecture.md`](file:///e:/Coding/GYM%20management%20system/docs/architecture.md)** | Full-stack serverless architecture, Next.js routing, Drizzle ORM data flow, and turnstile mechanics. |
| **[`docs/database.md`](file:///e:/Coding/GYM%20management%20system/docs/database.md)** | Modular Drizzle schema data dictionary, ERD diagrams, 10th-cutoff state machine, and Neon connection strings. |
| **[`docs/installation-guide.md`](file:///e:/Coding/GYM%20management%20system/docs/installation-guide.md)**| Local quickstart, environment configuration, Vercel zero-cost deployment, and hardware turnstile testing. |
| **[`docs/api.md`](file:///e:/Coding/GYM%20management%20system/docs/api.md)** | Serverless route handlers (`/api/attendance`, `/api/cron/*`), request/response JSON schemas, and smart normalizer. |
| **[`docs/features.md`](file:///e:/Coding/GYM%20management%20system/docs/features.md)** | Complete breakdown of public showroom, online admissions, PWA mobile app, and admin operations. |
| **[`docs/design.md`](file:///e:/Coding/GYM%20management%20system/docs/design.md)** | Visual design system, dark obsidian color tokens, `Teko` & `Plus Jakarta Sans` typography, and glassmorphism. |
| **[`docs/PRODUCT_CORE_SYSTEM_SPEC.md`](file:///e:/Coding/GYM%20management%20system/docs/PRODUCT_CORE_SYSTEM_SPEC.md)** | IaaS operating philosophy, remote biometrics strategy, and phased product roadmap. |
| **[`docs/PRODUCT_OVERVIEW.md`](file:///e:/Coding/GYM%20management%20system/docs/PRODUCT_OVERVIEW.md)** | Product identity, commercial value proposition, buyer personas, and 18-screen visual inventory. |
| **[`docs/WEBSITE_LANDING_OVERVIEW.md`](file:///e:/Coding/GYM%20management%20system/docs/WEBSITE_LANDING_OVERVIEW.md)** | Public landing page conversion architecture, split device mockup, and social proof counters. |

---

## ⏰ Automated Background Reminders & Cron

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
Vercel automatically triggers this endpoint daily at 08:00 AM UTC. It checks for subscriptions nearing the **10th-of-the-month cutoff**, identifies overdue accounts, and logs WhatsApp notification payloads.

---

## 🎯 Hardware & Turnstile Testing Guide

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
