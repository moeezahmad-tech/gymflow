# GymFlow &mdash; Feature Specification & Functional Capabilities
## Complete Platform Capabilities & Technical Modules

GymFlow is an end-to-end gym and fitness facility operating system built with **Next.js 14 App Router**, **TypeScript**, **Tailwind CSS**, **Neon Serverless PostgreSQL**, and **Drizzle ORM**. It unifies public club marketing, online athlete admissions, 1-on-1 personal coaching intake, self-service member dashboards, turnstile access automation, and administrative command suites.

---

## Table of Contents
1. [Core Platform Capabilities](#1-core-platform-capabilities)
2. [Public Web Presence & Marketing](#2-public-web-presence--marketing)
3. [Unified Athlete Sign-Up & Admission Gateway (`/admission`)](#3-unified-athlete-sign-up--admission-gateway-admission)
4. [1-on-1 Personal Coaching Application System (`/coaching`)](#4-1-on-1-personal-coaching-application-system-coaching)
5. [Athlete Profile Dashboard (`/profile`)](#5-athlete-profile-dashboard-profile)
6. [Dedicated Athlete Login Portal (`/login`)](#6-dedicated-athlete-login-portal-login)
7. [Strict 2-Step Contact & Assessment Flow (`/contact`)](#7-strict-2-step-contact--assessment-flow-contact)
8. [Member Mobile Companion PWA (`/app`)](#8-member-mobile-companion-pwa-app)
9. [Optical Camera Turnstile Scanner (`/scan`)](#9-optical-camera-turnstile-scanner-scan)
10. [Executive Administrative Operations Suite (`/admin`)](#10-executive-administrative-operations-suite-admin)
11. [Universal 10th-Cutoff Billing Engine](#11-universal-10th-cutoff-billing-engine)
12. [Nexora Dark Athletic Design Standards](#12-nexora-dark-athletic-design-standards)

---

## 1. Core Platform Capabilities

* **0% SaaS Commissions & Complete Data Autonomy:** Eliminates recurring per-member platform rents by providing a modern self-hosted platform running on serverless PostgreSQL.
* **Unified Member Lifecycle:** Seamlessly moves prospective athletes from public discovery & coaching intake into active registered members with turnstile credentials and personalized profile tracking.
* **Universal 10th-Cutoff Billing:** All monthly memberships synchronize to the 10th of every month. Unpaid accounts past the cutoff trigger automated barrier gate lockouts.
* **Dual Access Control Technology:** Dynamic smartphone passes with 100% offline capability (`/app`) and in-browser optical camera decoding (`/scan`).

---

## 2. Public Web Presence & Marketing

### 2.1 Marketing Landing Page (`/`)
* **Nexora Dark Athletic Aesthetic:** Deep obsidian surfaces (`bg-black`, `bg-zinc-950`), subtle fluted pleat textures, and vibrant crimson accents (`#ff2a2a`).
* **Hero Banner:** Clean headline wrapping ("The Strength Sanctuary Built for Peak Performance"), 48px fixed-height dual CTAs (`Start Free Trial`, `Book Gym Tour`), and authentic athletic facility imagery.
* **Real-Time Gym Floor Metrics:** Replaced repetitive QR codes with 3 authentic athletic performance indicators:
  * **Total Iron Moved:** `2.45 M kg` *(32% overload vs last 7 days)*
  * **Workouts Logged:** `18,200+` *(24% attendance vs last 7 days)*
  * **Gym Floor Volume:** `18.6 K Sets` with glowing red SVG area wave sparkline and daily training velocity indicators.
* **Equipment Arsenal & Daily Tactical Protocol:** Showcases calibrated Eleiko plates, Hammer Strength bays, Watson dumbbell bays, Concept2 rowers, and Push/Pull/Legs periodization splits.
* **Training Disciplines Preview:** Highlights Powerlifting & Hypertrophy, Boxing Combatives, and Cardio Blitz with intensity percentages and coach attributions.
* **Featured Membership Tiers:** Transparent pricing in PKR (₨ 8,500 / ₨ 22,500 / ₨ 42,000) with direct routes to admission.

### 2.2 Global Public Header (`PublicHeader`)
* **Static & Neutral:** Identical experience for all visitors; zero administrative controls exposed.
* **Navigation Links (Strict Capital Case):** Home, About, Programs, Coaching, Facilities, Memberships, Admission, Contact.
* **Action CTAs:** Log In (`/login`), Sign Up (`/admission`), and Athlete Profile shortcut (`/profile`).

---

## 3. Unified Athlete Sign-Up & Admission Gateway (`/admission`)

Merged the previous separate Sign-Up and Admission pages into a single, high-conversion, 4-step onboarding gateway. Legacy `/register` routes automatically redirect here via server HTTP 307.

### Step 1: Identity & Credentials (Strictly Gated)
* **Fields:** Full Legal Name, Father/Guardian Name, CNIC / B-Form Number, Date of Birth, Gender, Blood Group, WhatsApp Mobile Number, Alternate Phone, **Email Address (Login ID)**, **Create Login Password** (with toggleable show/hide eye icon), Residential Address & Sector, City, Emergency Contact Name & Emergency Phone.
* **Strict Validation:** Clicking subsequent steps (Step 2, 3, or 4) or the Next button is strictly blocked until all required Step 1 fields are verified.

### Step 2: Physical & Health Screening (PAR-Q)
* Height (Feet/Inches), Weight (kg), Lifting Experience, Primary Fitness Objective, Medical Condition Checkboxes (Heart, High BP, Asthma, Joint Injuries, Recent Surgery), and Dietary/Medical Notes.

### Step 3: Package Selection & Training Shift
* Tier selection (1 Month, 3 Month Pro Athlete, 6 Month Elite), Shift window (Morning 05:00 AM - 11:00 AM, Afternoon 12:00 PM - 05:00 PM, Prime Evening 05:00 PM - 11:00 PM), Dedicated Locker Add-on reservation, and Coach Assignment request.

### Step 4: Payment Verification & Mandatory Waivers
* Payment channel selection: EasyPaisa (`0325 9491349`), JazzCash, Cash at Desk, or Bank Transfer (Meezan Bank).
* Mandatory consent agreements: **Strict 10th-of-the-month cutoff lockout policy**, GymFlow code of conduct, and physical exercise liability release.

### Completion: Official Athlete Dossier Summary
* Replaces raw turnstile passes with an **Official Athlete Dossier**: allocated Member ID (e.g. `2026-4`), tier badge, shift confirmation, printable slip, automatic session persistence to `localStorage`, and direct buttons to **"View Athlete Profile"** (`/profile`) and **"Send Confirmation on WhatsApp"**.

---

## 4. 1-on-1 Personal Coaching Application System (`/coaching`)

A dedicated feature allowing members and prospective athletes to apply for direct 1-on-1 coach mentorship:

* **Master Coach Roster:**
  * **Coach Marcus Vance:** Head of Hypertrophy & Powerlifting (CSCS, 12+ Yrs Exp)
  * **Coach Sarah Jenkins:** Olympic Weightlifting & Combat Prep (USAW L2, 9+ Yrs Exp)
  * **Coach David Cole:** Conditioning & Body Recomposition (EXOS, 8+ Yrs Exp)
  * **Lead Nutritionist Amina Tariq:** Clinical Sports Nutrition & Hormonal Blueprinting (ISSN, 7+ Yrs Exp)
* **2-Step Intake Form:**
  * *Step 1 (Athlete Identity):* Full Name, WhatsApp Number, Email, and optional Member ID (auto-populated if logged in).
  * *Step 2 (Coaching Objectives):* Target Discipline, Preferred Coach, Weekly Frequency (2–5 sessions/week), Lifting Experience, Preferred Shift, and 90-day physical milestones/injury history.
* **Live Dossier Spotlight:** Real-time preview card highlighting the selected coach's credentials, free initial consultation notice, and direct WhatsApp desk inquiry.
* **Instant WhatsApp & Session Handoff:** On submission, generates a unique coaching reference (e.g. `COACH-4821`), saves the application to the athlete's session, and provides a 1-click **"Notify Coach on WhatsApp"** button pre-filled with application details.
* **Site-Wide Integration:**
  * Added "Apply for 1-on-1 Coaching" button on each training discipline on `/programs` with URL pre-selection (`?coach=Sarah+Jenkins`).
  * Integrated Coaching Card into the Athlete Profile dashboard (`/profile`).
  * Added to public navbar and footer.

---

## 5. Athlete Profile Dashboard (`/profile`)

Personal command center for enrolled gym members:
* **Athlete Header Hero:** Name, avatar monogram, active athlete pill, email, WhatsApp phone, and Member ID.
* **Editable Dossier:** Inline editing of address, phone, shift, and emergency contacts with instant `localStorage` synchronization.
* **Membership Status Card:** Active package tier, next 10th-of-month cutoff date, 24/7 turnstile access clearance, and 1-tap WhatsApp renewal.
* **1-on-1 Coaching & Mentorship Card:** Shows assigned coach, target focus, frequency, live review status (`REVIEWING` / `ACTIVE`), and direct **"Message Coach on WhatsApp"** action.
* **Companion App Launcher:** Direct shortcut to launch the Mobile PWA (`/app`).

---

## 6. Dedicated Athlete Login Portal (`/login`)

* **No Admin Role Switcher:** The "Admin Director" tab/button has been completely removed from the form.
* **Dedicated Member Login:** Form fields strictly for Email Address or Member Code (`2026-4`) + Password.
* **Quick Demo Credentials:** Athlete test credentials only (`moeezahmad.tech@gmail.com` / `password123`).
* **Direct Redirection:** Authenticated members are redirected straight to their **Athlete Profile** (`/profile`).

---

## 7. Strict 2-Step Contact & Assessment Flow (`/contact`)

* **Step 1:** Name, WhatsApp Mobile, Email Address. Step 1 validation strictly blocks advancement to Step 2 if fields are empty or invalid.
* **Step 2:** Program of interest (Strength, Boxing, Conditioning, Recovery), primary goals, and custom message.
* Direct desk WhatsApp link (`+92 325 9491349`) and Metropolis HQ address details.

---

## 8. Member Mobile Companion PWA (`/app`)

* **100% Offline Smartphone Pass:** Operates without cellular reception in gym basements using Service Workers (`sw.js`).
* **Real 2D QR Matrix:** Level H error correction with high-contrast retina rendering.
* **Gamified Workout Streaks:** Tracks consecutive workout days ("🔥 14-Day Streak") and monthly training sessions.
* **Native In-App Authentication:** Built-in modal allowing athletes to sign in or register directly within the mobile app shell.
* **Interactive Turnstile Gate Simulator:** Allows members to simulate a physical turnstile scan, updating live gate access streams.

---

## 9. Optical Camera Turnstile Scanner (`/scan`)

* **Zero-Cost Hardware Compatibility:** Turns any smartphone, tablet, or laptop webcam into a turnstile scanner.
* **Real-Time jsQR Frame Decoding:** Instant sub-second barcode & QR reading.
* **Web Audio Sound Chimes:** Synthesizes sound frequencies for granted (880Hz / 1320Hz) and denied entry attempts without external audio assets.
* **Physical Scanner Wedge:** Compatible with handheld USB/Bluetooth barcode guns.

---

## 10. Executive Administrative Operations Suite (`/admin`)

* **Email Broadcast Mock Section:** Compose email announcements with audience segmentation filters:
  * `All Members`
  * `Current Members` (active subscriptions)
  * `Old Members` (expired or lapsed memberships)
  * `New Members` (enrolled in the last 30 days)
  * `Selected Members` (manual checkbox multi-select)
* Real-time recipient counter, subject line input, template selectors, live message preview, and dispatch confirmation.
* **Executive KPI Cockpit:** Real-time metrics for today's check-ins, current floor occupancy, gross monthly PKR intake, and live turnstile relay feed.
* **Athlete Directory & CRM (`/admin/members`):** Sub-second search by Name, Phone, CNIC, or Member Code (`2026-4`) with 1-click gate lockout toggles.
* **10th-Cutoff Billing Ledgers (`/admin/billing`):** Track receivables due on the 10th across Cash, JazzCash, EasyPaisa, and Bank Transfers.

---

## 11. Universal 10th-Cutoff Billing Engine

* **Synchronized 10th-Cutoff Cycle:** All memberships renew on the 10th of every month.
* **Automated Gate Lockouts:** Unpaid accounts past the cutoff date are automatically barred from entry at the turnstile.
* **Automated Cron Jobs:** Scheduled daily tasks scanning records, generating renewal alerts, and updating member status flags.

---

## 12. Nexora Dark Athletic Design Standards

* **Color Palette:** Pure Obsidian Black (`#000000`), Dark Surface Zinc (`#0f1013`, `#18181b`), Crimson Accent (`#ff2a2a`), Emerald Green (`#10b981`), Amber Warning (`#f59e0b`).
* **Typographical Hierarchy:** Headlines in athletic bold (`Teko`), body text in clean sans-serif (`Plus Jakarta Sans`), and codes/metrics in monospace (`JetBrains Mono`).
* **Strict Casing:** 100% Capital Case / Title Case across all public and dashboard views (no forced all-caps).
* **Generous Spacing:** Generous section padding and column gaps across headers, grids, and footers (`pt-24 pb-20`, `gap-12` to `gap-20`).
