# GymFlow &mdash; Complete Product Specification & System Overview
## Turnkey Athletic Facility Operating System, Member Portal & Access Engine

> **Comprehensive product specification, functional architecture, screen inventory, and system context for GymFlow.**  
> *Engineered with Next.js 14 App Router, TypeScript, Tailwind CSS, Neon Serverless PostgreSQL, and Drizzle ORM.*

---

## 1. Executive Summary & Product Identity

### 1.1 What is GymFlow?
**GymFlow** is an athletic facility management platform, member portal, and automated turnstile access engine. It provides commercial gyms, powerlifting sanctuaries, martial arts academies, and CrossFit boxes with complete operational autonomy:
* **0% SaaS Commissions & 100% Data Ownership:** Replaces recurring monthly software rents ($150–$450/month) with a turnkey, self-hosted system on modern cloud infrastructure.
* **Autonomous 24/7 Biometric Turnstiles:** Dynamic offline smartphone turnstile passes (`/app`), optical camera verification (`/scan`), and physical relay compatibility.
* **Universal 10th-Cutoff Billing Engine:** Standardized monthly billing cycle enforcing automatic turnstile lockouts for delinquent accounts on the 10th of each month.
* **Integrated Athlete Ecosystem:** 4-step digital onboarding, self-service Athlete Profiles (`/profile`), gamified workout streaks, and 1-on-1 coaching intake (`/coaching`).

### 1.2 Official Taglines & Commercial Hooks
* **Primary Brand Tagline:** *"The Modern Operating Platform Built for Elite Fitness Facilities."*
* **Commercial Hook:** *"Eliminate member fee leakage, automate turnstile access, and ditch expensive monthly SaaS subscriptions."*
* **Access Control Hook:** *"Ditch plastic RFID cards with 100% offline smartphone turnstile passes."*
* **Billing Hook:** *"Automated universal 10th-cutoff billing with automated gate lockouts for unpaid accounts."*

---

## 2. Target Audience & User Personas

### Persona A: Gym Owners & General Managers
* **Core Frustrations:** Paying ongoing monthly software fees, chasing unpaid member dues manually, and members sharing RFID cards.
* **Why GymFlow:** 100% data ownership on Neon PostgreSQL, automated gate lockouts on the 10th, and executive KPI dashboards tracking live floor occupancy and revenue in PKR.

### Persona B: Front-Desk Receptionists & Operations Staff
* **Core Frustrations:** Entrance bottlenecks during peak hours (6:00 PM rush), complicated payment reconciliations, and messy paper logbooks.
* **Why GymFlow:** Sub-second optical QR gate validation (`/scan`), 4-step digital admission with instant dossier allocation, and 1-click cash desk reconciliation.

### Persona C: Gym Members & Athletes
* **Core Frustrations:** Forgetting plastic cards at home, no visibility into due dates or workout streaks, and having to install bloated native app-store apps.
* **Why GymFlow:** Installs instantly as a lightweight PWA (`/app`), works offline, displays attendance streak badges ("🔥 14-Day Streak"), provides an editable Athlete Profile (`/profile`), and enables 1-tap WhatsApp coach communication.

---

## 3. The 6 Core Subsystems

```
                      ┌────────────────────────────────────────┐
                      │            GYMFLOW PLATFORM            │
                      └───────────────────┬────────────────────┘
                                          │
         ┌──────────────────┬─────────────┼──────────────┬──────────────────┬─────────────────┐
         ▼                  ▼             ▼              ▼                  ▼                 ▼
┌─────────────────┐  ┌───────────────┐ ┌──────────────┐ ┌─────────────────┐ ┌───────────────┐ ┌───────────────┐
│  SUBSYSTEM 01   │  │ SUBSYSTEM 02  │ │ SUBSYSTEM 03 │ │  SUBSYSTEM 04   │ │ SUBSYSTEM 05  │ │ SUBSYSTEM 06  │
│ Public Website  │  │ Unified Intake│ │ Athlete PWA  │ │ Camera Turnstile│ │ Executive     │ │ Billing Cron  │
│ & Marketing     │  │ & Coaching    │ │ & Profile    │ │ Scanner (/scan) │ │ Admin Suite   │ │ & APIs        │
└─────────────────┘  └───────────────┘ └──────────────┘ └─────────────────┘ └───────────────┘ └───────────────┘
```

### Subsystem 01: Public Marketing & Discovery (`/`, `/about`, `/programs`, `/facilities`, `/pricing`, `/contact`)
* **Nexora Dark Athletic Aesthetic:** Deep obsidian dark background (`bg-black`, `bg-zinc-950`), fluted pleat textures, crimson ambient lighting (`#ff2a2a`), and strict Title Case typography.
* **Hero Section:** Clean text wrapping, 48px fixed-height dual CTAs (`Start Free Trial`, `Book Gym Tour`), and authentic athletic equipment photography (Eleiko bumper plates, Hammer Strength bays).
* **Live Performance Counters:** Replaced repetitive QR codes with genuine gym floor metrics: Total Iron Moved (2.45M kg), Workouts Logged (18.2K+), and Floor Volume (18.6K Sets) with an SVG area wave sparkline.
* **Static Public Header:** Neutral navigation without administrative buttons or state leakage, providing direct access to Log In, Sign Up, and Athlete Profile.

### Subsystem 02: Unified Onboarding & 1-on-1 Coaching Intake (`/admission`, `/coaching`)
* **Unified Sign-Up & Admission (`/admission`):** Merged sign-up and admission into a single 4-step gateway. Includes account credential setup (Email Login ID + password) in Step 1. Strictly blocks advancement to Steps 2, 3, or 4 until Step 1 identity fields are valid. Automatically persists session to `localStorage` and generates an official Athlete Dossier. Legacy `/register` routes redirect here automatically.
* **1-on-1 Coaching Intake (`/coaching`):** Master coach roster showcase (Marcus Vance, Sarah Jenkins, David Cole, Amina Tariq) with a 2-step application form covering discipline, weekly frequency (2–5 sessions), shift, milestones, and injuries. Generates unique coaching reference ID (e.g. `COACH-4821`) and enables 1-tap WhatsApp dispatch.

### Subsystem 03: Member Self-Service PWA & Athlete Profile (`/app`, `/profile`)
* **Athlete Profile Dashboard (`/profile`):** Member control room with editable contact fields, active plan status, next 10th-of-month cutoff date, 24/7 turnstile access clearance, 1-on-1 Coaching status card (`REVIEWING` / `ACTIVE`), and 1-tap front desk renewal.
* **Offline Smartphone PWA Pass (`/app`):** 100% offline Service Worker pass with Level H error-corrected real 2D QR matrix, gamified workout streak counter ("🔥 14-Day Streak"), daily gate access log stream, in-app sign-in modal, and simulated gate check-ins.

### Subsystem 04: Live Optical Camera Turnstile Scanner (`/scan`)
* **In-Browser Optical Decoding:** Runs on any tablet or webcam using `getUserMedia` and `jsQR` to decode member passes in real time.
* **Zero-Latency Web Audio Chimes:** Synthesizes sound frequencies for granted (880Hz / 1320Hz) and denied entry attempts without external audio files.
* **Hardware Wedge Compatibility:** Compatible with USB and Bluetooth handheld barcode scanners.

### Subsystem 05: Executive Admin Operations Suite (`/admin`)
* **Email Broadcast Mock Section:** Compose email campaigns with audience filters (`All Members`, `Current Members`, `Old Members`, `New Members`, `Selected Members`), real-time recipient counts, template selectors, and live message preview.
* **Executive KPI Cockpit:** Real-time metrics for today's check-ins, current floor occupancy, gross monthly PKR intake, and live turnstile relay stream.
* **Athlete Dossier & CRM:** Sub-second search by Name, Phone, CNIC, or Member Code (`2026-4`) with 1-click gate lockout toggles.
* **10th-Cutoff Billing Ledgers:** Track receivables due on the 10th across Cash, JazzCash, EasyPaisa, and Bank Transfers.

### Subsystem 06: Universal 10th-Cutoff Engine & Serverless APIs (`/api/*`)
* **Synchronized 10th-Cutoff Cycle:** All member cycles synchronize to the 10th of every month. Unpaid members past the grace cutoff are automatically flagged for turnstile lockout.
* **Automated Cron Jobs:** Scheduled daily tasks scanning records, generating renewal alerts, and updating member status flags.
* **REST & Serverless Route Handlers:** Attendance verification, member mutations, and campaign dispatch.

---

## 4. Complete Screen Inventory

| # | Screen ID | Title & Module | Route URL | Key Features & Elements |
|---|:---|:---|:---|:---|
| 1 | `public-home` | **Marketing Landing Page** | `/` | Hero banner, floor metrics (2.45M kg iron), equipment bays, tactical protocol, pricing cards, lead inquiry. |
| 2 | `public-about` | **About & Philosophy** | `/about` | Master coach philosophy, training culture, 24/7 facility history. |
| 3 | `public-programs` | **Training Disciplines** | `/programs` | Powerlifting, Boxing Combatives, Cardio Blitz, coach tags, "Apply for 1-on-1 Coaching" buttons. |
| 4 | `public-coaching` | **1-on-1 Coaching Application** | `/coaching` | Coach roster cards, 2-step intake wizard, goal/frequency picker, live spotlight, WhatsApp handoff. |
| 5 | `public-facilities` | **Facilities & Recovery** | `/facilities` | Olympic lifting platforms, dumbbell bays, Finnish saunas, cold plunge immersion, lockers. |
| 6 | `public-pricing` | **Membership Pricing** | `/pricing` | 1-Month (₨ 8,500), 3-Month (₨ 22,500), 6-Month (₨ 42,000), 10th-cutoff rules, direct admission links. |
| 7 | `public-contact` | **Contact & Assessment** | `/contact` | 2-step validated inquiry form (Step 1 strictly enforced), location map, WhatsApp chat. |
| 8 | `public-admission` | **Sign Up & Admission** | `/admission` | 4-step gated wizard, integrated password creation, PAR-Q screen, shift picker, official Athlete Dossier. |
| 9 | `public-login` | **Athlete Login Portal** | `/login` | Dedicated member login (email/code + password), demo athlete credentials, routes to `/profile`. |
| 10 | `member-profile` | **Athlete Profile Dashboard** | `/profile` | Editable dossier, membership tier, 10th cutoff date, coaching status card, WhatsApp actions. |
| 11 | `mobile-app` | **Member Mobile PWA** | `/app` | Gamified streaks ("🔥 14-Day Streak"), real 2D QR pass, in-app auth modal, simulated turnstile scans. |
| 12 | `turnstile-scan` | **Optical Camera Verifier** | `/scan` | Real-time webcam HUD, laser reticle, Web Audio chimes, barrier relay simulator. |
| 13 | `admin-overview` | **Executive Admin Cockpit** | `/admin` | Floor occupancy, check-in counters, email broadcast engine with member filters, live gate stream. |
| 14 | `admin-members` | **Member Directory & CRM** | `/admin/members` | Full athlete rosters, CNIC search, status toggles, turnstile clearance switches. |
| 15 | `admin-billing` | **10th-Cutoff Billing Ledgers**| `/admin/billing` | Receivables due on 10th, payment recording, multi-channel receipts (Cash/EasyPaisa/JazzCash). |

---

## 5. Technical Architecture & Engineering Stack

* **Framework:** Next.js 14 App Router (React 18 / TypeScript)
* **Styling & Design System:** Tailwind CSS with custom Nexora Dark Athletic theme tokens (`brand-red: #ff2a2a`, `dark-950: #070709`, font families `Teko` and `Plus Jakarta Sans`)
* **Database & ORM:** Neon Serverless PostgreSQL with Drizzle ORM
* **State & Offline Persistence:** React Client State, LocalStorage session sync, Web Storage, and Service Worker (`sw.js`) cache storage
* **Gate & QR Technology:** `qrcode` Level H rendering, `jsQR` frame decoders, Web Audio API sound synthesis
* **Communication Channels:** Direct WhatsApp Business deep linking (`+92 325 9491349`) with pre-formatted URL payloads for admissions, coaching applications, and plan renewals
