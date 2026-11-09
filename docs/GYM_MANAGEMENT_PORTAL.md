# GymFlow &mdash; Gym Management Portal & Ecosystem
## Complete Product Specification, Features & System Capabilities

---

> [!NOTE]
> **Product Name:** GymFlow (Gym Management Portal & Ecosystem)  
> **Architecture:** Next.js 14 App Router &bull; React 18 / TypeScript &bull; Neon Serverless PostgreSQL &bull; Drizzle ORM &bull; Tailwind CSS &bull; Standalone Progressive Web App (PWA) &bull; Optical Camera Turnstile Verifier  
> **Currency Support:** Pakistani Rupee (PKR - ₨) &bull; Multi-channel local payment reconciliation (Cash, Credit/Debit Card, JazzCash, EasyPaisa, Bank Transfer)  
> **Target Audience:** Commercial Gyms, Strength Facilities, CrossFit Boxes, Boutique Studios, and Personal Training Centers.

---

## 📋 Table of Contents
1. [Executive Summary & Product Mission](#1-executive-summary--product-mission)
2. [What This Product Does (Core Operational Scope)](#2-what-this-product-does-core-operational-scope)
3. [Target User Roles & Access Hierarchy](#3-target-user-roles--access-hierarchy)
4. [Comprehensive Feature Breakdown](#4-comprehensive-feature-breakdown)
   - [4.1 Public Marketing Sanctuary (`/`, `/about`, `/programs`, `/facilities`, `/pricing`)](#41-public-marketing-sanctuary)
   - [4.2 1-on-1 Personal Coaching Intake (`/coaching`)](#42-1-on-1-personal-coaching-intake-coaching)
   - [4.3 Unified Athlete Sign-Up & Admission Gateway (`/admission`)](#43-unified-athlete-sign-up--admission-gateway-admission)
   - [4.4 Athlete Profile Dashboard (`/profile`)](#44-athlete-profile-dashboard-profile)
   - [4.5 Dedicated Athlete Login Portal (`/login`)](#45-dedicated-athlete-login-portal-login)
   - [4.6 Strict 2-Step Contact Flow (`/contact`)](#46-strict-2-step-contact-flow-contact)
   - [4.7 Standalone Mobile Progressive Web Application (`/app`)](#47-standalone-mobile-progressive-web-application-app)
   - [4.8 Live Optical Camera Turnstile Scanner (`/scan`)](#48-live-optical-camera-turnstile-scanner-scan)
   - [4.9 Administrative Command Center & Email Broadcast Mock (`/admin`)](#49-administrative-command-center--email-broadcast-mock-admin)
   - [4.10 Billing, Invoicing & Cutoff Management (`/admin/billing`)](#410-billing-invoicing--cutoff-management-adminbilling)
   - [4.11 Turnstile API Gateway (`/api/attendance`)](#411-turnstile-api-gateway-apiattendance)
5. [System Capabilities & Technical Highlights](#5-system-capabilities--technical-highlights)
6. [Design System & Typographical Standards](#6-design-system--typographical-standards)

---

## 1. Executive Summary & Product Mission

**GymFlow** is an athletic facility operating platform and digital member portal designed to modernize gym operations. It eliminates fragmented paper logbooks, disconnected Excel spreadsheets, and expensive SaaS subscriptions by consolidating all club operations into a high-performance serverless ecosystem.

### The Problem It Solves:
* **Manual & Error-Prone Fee Collections:** Eliminates forgotten dues through a strict, automated **10th-cutoff monthly billing system**.
* **Access Control Vulnerabilities:** Eliminates unpaid facility usage with live **2D QR turnstile access passes** tied directly to subscription clearance in Neon PostgreSQL.
* **Member Disengagement:** Keeps members motivated with automated consistency streak engines, monthly workout counters, and instant direct access to dedicated fitness coaches.
* **Hardware Monopoly Trap:** Replaces expensive, high-maintenance biometric hardware with universal **optical camera scanners** running in any web browser with offline Web Audio API chimes and USB barcode wedge compatibility.
* **Operational Blindspots:** Grants facility owners real-time visibility into active headcount, daily turnstile check-ins, monthly recurring revenue, overdue receivables, and email communication.

---

## 2. What This Product Does (Core Operational Scope)

GymFlow manages the complete lifecycle of a modern fitness enterprise:

```mermaid
flowchart LR
    A["Public Website & Disciplines (/)"] --> B["1-on-1 Coaching (/coaching) or Admission (/admission)"]
    B --> C["Plan Assignment & Credentials Setup"]
    C --> D["Instant Athlete Dossier & Member ID (2026-4)"]
    D --> E["Athlete Profile (/profile) & PWA Pass (/app)"]
    E --> F["Daily Optical Gate Verification (/scan)"]
    F --> G["Monthly 10th-Cutoff Billing Engine"]
    G -->|Paid| E
    G -->|Overdue Past Cutoff| H["Automated Gate Lockout"]
```

---

## 3. Target User Roles & Access Hierarchy

1. **Guest / Prospective Athlete:** Explores training programs, reviews facilities, calculates pricing in PKR, applies for 1-on-1 coaching, or completes online admission.
2. **Enrolled Athlete / Member:** Logs in via `/login` to access their Athlete Profile (`/profile`), manages contact data, tracks training streaks and gate logs via the Mobile PWA (`/app`), and communicates directly with coaches.
3. **Front-Desk Receptionist:** Monitors live check-ins, operates camera scanners (`/scan`), registers walk-in counter members, and processes cash/online receipts.
4. **Gym Director / Administrator:** Operates the Executive Cockpit (`/admin`), monitors gross revenue and floor occupancy, configures membership plans, and sends segmented email broadcasts to members.

---

## 4. Comprehensive Feature Breakdown

### 4.1 Public Marketing Sanctuary
* **Landing Page (`/`):** Hero section with authentic athletic imagery, fixed 48px CTAs, floor metrics replacing repetitive QR codes (Total Iron Moved: 2.45M kg, Workouts Logged: 18,200+, Floor Volume sparkline), equipment bays, tactical protocol, and transparent pricing.
* **Public Header:** Clean, static navigation with zero administrative exposure: `Home`, `About`, `Programs`, `Coaching`, `Facilities`, `Memberships`, `Admission`, `Contact`, `Log In`, `Sign Up`, and Profile shortcut.

### 4.2 1-on-1 Personal Coaching Intake (`/coaching`)
* **Coach Roster:** Highlights head coaches (Marcus Vance, Sarah Jenkins, David Cole, Amina Tariq) with certifications and specialties.
* **2-Step Application:** Collects athletic objectives, preferred coach, frequency (2–5 sessions/week), shift, and 90-day milestones.
* **WhatsApp Dispatch & Profile Sync:** Allocates a unique reference ID (e.g. `COACH-4821`), links to `localStorage`, and provides a 1-tap WhatsApp button pre-filled with application details.

### 4.3 Unified Athlete Sign-Up & Admission Gateway (`/admission`)
* **Combined Flow:** Merges sign-up and admission into one flow. Legacy `/register` automatically redirects here.
* **Integrated Credentials:** Step 1 includes Email Address and Create Login Password (with show/hide toggle).
* **Strict Sequential Validation:** Steps 2, 3, and 4 cannot be accessed until all required Step 1 fields are valid.
* **Official Athlete Dossier:** Upon submission, persists user data to `localStorage`, allocates a Member ID (e.g. `2026-4`), and generates an official Athlete Dossier with printable voucher and WhatsApp confirmation.

### 4.4 Athlete Profile Dashboard (`/profile`)
* **Personal Control Center:** Editable identity and emergency contact fields with instant `localStorage` synchronization.
* **Membership Status Card:** Active plan tier, next 10th-of-month cutoff date, 24/7 turnstile access clearance, and 1-tap WhatsApp renewal.
* **1-on-1 Coaching & Mentorship Card:** Live application review status (`REVIEWING` / `ACTIVE`), assigned coach details, and direct WhatsApp communication channel.

### 4.5 Dedicated Athlete Login Portal (`/login`)
* **No Admin Role Switcher:** Form is strictly dedicated to Athlete / Member authentication (email/code + password).
* **Demo Credentials:** Displays only athlete test credentials (`moeezahmad.tech@gmail.com` / `password123`).
* **Direct Redirection:** Authenticated members are redirected straight to their Athlete Profile (`/profile`).

### 4.6 Strict 2-Step Contact Flow (`/contact`)
* **Strict Validation:** Step 1 (Name, Email, WhatsApp) must be valid before Step 2 (Program, Goals, Message) can be submitted.
* Direct WhatsApp handoff to the front desk (`+92 325 9491349`).

### 4.7 Standalone Mobile Progressive Web Application (`/app`)
* **100% Offline Smartphone Pass:** Operates without cellular reception using Service Workers (`sw.js`).
* **Real 2D QR Matrix:** Level H error correction with high-contrast retina rendering.
* **Gamified Workout Streaks:** Tracks consecutive workout days ("🔥 14-Day Streak").
* **Native In-App Authentication:** Built-in modal allowing athletes to sign in or register directly within the mobile app shell.

### 4.8 Live Optical Camera Turnstile Scanner (`/scan`)
* **Zero-Cost Hardware Compatibility:** Turns any smartphone, tablet, or laptop webcam into a turnstile scanner.
* **Real-Time jsQR Frame Decoding:** Instant sub-second barcode & QR reading.
* **Web Audio Sound Chimes:** Synthesizes sound frequencies for granted (880Hz / 1320Hz) and denied entry attempts.

### 4.9 Administrative Command Center & Email Broadcast Mock (`/admin`)
* **Email Broadcast Mock Section:** Compose email announcements with audience segmentation filters:
  * `All Members`
  * `Current Members` (active subscriptions)
  * `Old Members` (expired or lapsed memberships)
  * `New Members` (enrolled in the last 30 days)
  * `Selected Members` (manual checkbox multi-select)
* Real-time recipient counter, subject line input, template selectors, live message preview, and dispatch confirmation.
* Executive KPIs: Today's check-ins, current floor occupancy, gross monthly PKR intake, live turnstile relay feed, and member directories.

### 4.10 Billing, Invoicing & Cutoff Management (`/admin/billing`)
* Universal 10th-cutoff tracking, receivable logs, multi-channel payment recording (Cash, EasyPaisa, JazzCash, Bank Transfer).

### 4.11 Turnstile API Gateway (`/api/attendance`)
* Fast serverless route handlers for GET and POST turnstile verification with smart code normalization.

---

## 5. System Capabilities & Technical Highlights

* **Next.js 14 App Router:** TypeScript, Server Components, Route Handlers.
* **Neon Serverless PostgreSQL:** Managed cloud database with Drizzle ORM.
* **Client Session Sync:** Synchronized `localStorage` session handling across Admission, Login, Profile, and Coaching.
* **Zero SaaS Fees:** 100% owned turnkey deployment with zero monthly rental fees.

---

## 6. Design System & Typographical Standards

* **Nexora Dark Athletic Aesthetic:** Deep obsidian surfaces, crimson red accents (`#ff2a2a`), glassmorphic panels, and fluted textures.
* **Strict Capital Case / Title Case:** All headings, section titles, card headers, and button labels strictly use Title Case. Zero forced all-caps.
* **Standardized Button Geometry:** 48px fixed height (`h-12`) on primary hero CTAs with consistent `gap-4`.
* **Generous Footer Spacing:** Multi-column layout with `pt-24 pb-20` padding and `gap-12` to `gap-20` column spacing.
