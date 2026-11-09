# GymFlow &mdash; Intelligent Gym Operating System
## Core System Architecture, Phased Roadmap, Remote Biometrics Strategy & Engineering Specification

> **Document Type:** Master Product & Engineering Blueprint  
> **Target Audience:** Product Managers, Software Architects, Full-Stack Engineers, AI Coding Assistants  
> **Platform Classification:** Athletic Facility Operating Platform & Member Portal  
> **Core Framework:** Next.js 14 App Router, TypeScript, Tailwind CSS, Neon Serverless PostgreSQL, Drizzle ORM

---

## 1. Executive Vision: The Modern Athletic Operating Platform

GymFlow is engineered for modern commercial gyms, powerlifting facilities, boutique strength studios, and combat academies. 

Unlike generic, passive spreadsheet tools or SaaS providers that charge expensive recurring per-member platform fees ($150–$450/month), GymFlow provides an integrated, fully-owned operating platform:
1. **0% SaaS Commissions & Complete Data Autonomy:** Deployed on serverless cloud infrastructure (Vercel + Neon PostgreSQL) with 100% data and code ownership.
2. **Autonomous Turnstile Biometrics:** Dynamic offline smartphone turnstile passes (`/app`), optical camera verification (`/scan`), and hardware relay compatibility eliminating plastic RFID cards.
3. **Universal 10th-Cutoff Billing Engine:** Automated monthly fee synchronization with turnstile lockouts for delinquent accounts on the 10th of every month.
4. **Complete Athlete Lifecycle:** From public discovery, training programs, and 1-on-1 coaching intake (`/coaching`) to 4-step digital admission (`/admission`) and member self-service dashboards (`/profile`).

---

## 2. Phased Product Architecture & Operational Modules

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       GYMFLOW SYSTEM ARCHITECTURE                           │
└─────────────────────────────────────────────────────────────────────────────┘
                                  │
    ┌─────────────────────────────┴─────────────────────────────┐
    ▼                                                           ▼
┌───────────────────────────────────────┐   ┌───────────────────────────────────────┐
│     TIER 1: PUBLIC & ATHLETE FACING   │   │   TIER 2: ADMINISTRATIVE OPERATIONS   │
├───────────────────────────────────────┤   ├───────────────────────────────────────┤
│ • Public Marketing Sanctuary (/)      │   │ • Executive Admin Cockpit (/admin)    │
│ • Unified Sign Up & Admission (/admission)│ • Email Broadcast Engine (Filters)    │
│ • 1-on-1 Coaching Application (/coaching)│ • Athlete Directory & CRM (/members)   │
│ • Athlete Profile Dashboard (/profile)│ • 10th-Cutoff Billing Ledgers (/billing)│
│ • Dedicated Athlete Login (/login)    │ • Live Attendance Stream (/attendance) │
│ • 2-Step Strict Contact Form (/contact)│ • Dynamic Plan CRUD Builder (/plans) │
│ • Offline Mobile PWA Pass (/app)      │ • Optical Camera Gate Verifier (/scan)│
└───────────────────────────────────────┘   └───────────────────────────────────────┘
```

### Module 1: Unified Sign Up & Admission Gateway (`/admission`)
* **Unified Flow:** Combines sign-up and admission into a single 4-step onboarding gateway. Legacy `/register` automatically redirects here.
* **Integrated Credential Setup:** Step 1 includes **Email Address (Login ID)** and **Create Login Password** (with toggleable show/hide eye icon).
* **Strict Sequential Validation:** Advancing past Step 1 or clicking Steps 2, 3, or 4 is strictly prevented until all required identity fields (Name, CNIC, WhatsApp, Email, Password, Address, Emergency Contact) are verified.
* **Official Athlete Dossier:** Upon submission, persists user data to `localStorage`, allocates a Member ID (e.g. `2026-4`), and generates an official Athlete Dossier with printable voucher and 1-tap WhatsApp confirmation.

### Module 2: 1-on-1 Personal Coaching Intake (`/coaching`)
* **Coach Roster:** Showcases head coaches (Marcus Vance - Hypertrophy, Sarah Jenkins - Olympic & Combat, David Cole - Conditioning, Amina Tariq - Nutrition).
* **2-Step Application:** Collects athletic objectives, preferred coach, frequency (2–5 sessions/week), shift, and 90-day physical milestones.
* **Live Dossier Spotlight:** Real-time preview card highlighting the selected coach's credentials and free initial consultation notice.
* **WhatsApp Dispatch & Profile Sync:** Allocates a unique reference ID (e.g. `COACH-4821`), links to `localStorage`, and provides a 1-tap WhatsApp button pre-filled with application details.

### Module 3: Athlete Profile Dashboard (`/profile`)
* **Personal Control Center:** Editable identity and emergency contact fields with instant `localStorage` synchronization.
* **Membership Status Card:** Active plan tier, next 10th-of-month cutoff date, 24/7 turnstile access clearance, and 1-tap WhatsApp renewal.
* **1-on-1 Coaching & Mentorship Card:** Live application review status (`REVIEWING` / `ACTIVE`), assigned coach details, and direct WhatsApp communication channel.
* **Companion App Shortcut:** Direct launcher to the Mobile PWA (`/app`).

### Module 4: Dedicated Athlete Login (`/login`)
* **Zero Admin Switcher:** The "Admin Director" button and role switcher have been completely removed from the login form.
* **Dedicated Member Authentication:** Form fields strictly for Email Address or Member Code (`2026-4`) + Password.
* **Demo Credentials:** Displays only athlete test credentials (`moeezahmad.tech@gmail.com` / `password123`).
* **Direct Redirection:** Authenticated members are redirected straight to their Athlete Profile (`/profile`).

### Module 5: Executive Admin Operations & Email Broadcast Mock (`/admin`)
* **Email Broadcast Mock Section:** Compose email announcements with audience segmentation filters:
  * `All Members`
  * `Current Members` (active subscriptions)
  * `Old Members` (expired or lapsed memberships)
  * `New Members` (enrolled in the last 30 days)
  * `Selected Members` (manual checkbox multi-select)
* Real-time recipient counter, subject line input, template selectors, live message preview, and dispatch confirmation.
* Executive KPIs: Today's check-ins, current floor occupancy, gross monthly PKR intake, live turnstile relay feed, and member directories.

---

## 3. Remote Attendance & Biometric Strategy

### 3.1 Comparison Matrix of Attendance Technologies

| Criteria | 1. Physical Thumb / Fingerprint Scanner | 2. Proprietary Face Hardware | 3. Remote-First Dynamic Mobile QR Pass | 4. Front-Desk Tablet Camera Optical Verifier |
| :--- | :--- | :--- | :--- | :--- |
| **Remote Deployability** | ❌ **Extremely Poor.** Requires static IPs, USB/RS485 drivers, local port forwarding. | ⚠️ **Challenging.** Closed vendor firmware, proprietary cloud bridges. | 🟢 **Exceptional.** 100% cloud-managed; zero local drivers needed. | 🟢 **High.** Uses standard Android/iPad tablet with front camera via browser. |
| **Maintenance Burden** | ❌ High. Dirty sensors, dry skin failures, sensor calibration errors require physical visits. | ⚠️ Moderate. Lens cleaning, camera alignment. | 🟢 Minimal. Runs on member's personal smartphone. | 🟢 Low. Software updates pushed over-the-air (OTA). |
| **Buddy Punching Prevention** | 🟢 High. Biological fingerprint required. | 🟢 High. Live facial biological verification. | 🟢 **High.** Single-device active session with real 2D matrix. | 🟢 High. Real-time liveness detection and camera scan feed. |
| **Initial Hardware Cost for Gym** | $150 – $400 per scanner + wiring costs. | $400 – $1,200 per specialized terminal box. | **$0** (Or $30 for a basic 2D optical scanner at desk). | $100 – $200 (Any standard budget Android tablet). |
| **Implementation Speed** | 2 to 4 weeks of physical installation. | 2 to 3 weeks of vendor hardware onboarding. | **Immediate (Instant Remote Onboarding).** | 1 to 2 days (Open browser on tablet). |

---

## 4. Design Standards & Typography Casing

* **Nexora Dark Athletic Aesthetic:** Deep obsidian black (`#000000`), dark zinc surfaces (`#070709`, `#09090b`), crimson accent (`#ff2a2a`), emerald green (`#10b981`), and amber warning (`#f59e0b`).
* **Strict Capital Case / Title Case:** All headlines, section titles, card headers, and button labels must use **Title Case / Capital Case**. Zero forced all-caps text.
* **Standardized Button Geometry:** Primary CTAs use 48px fixed height (`h-12`), `px-8`, and `gap-4` spacing.
* **Generous Footer Spacing:** Multi-column footer layout with `pt-24 pb-20` padding and `gap-12` to `gap-20` column spacing.
* **Authentic Athletic Indicators:** Replaced repetitive QR codes on the landing page with authentic gym floor volume metrics (Total Iron Moved 2.45M kg, Workouts Logged 18.2K+, glowing red SVG area wave sparkline).

---

## 5. Technology Stack Reference

* **Framework:** Next.js 14 App Router (React 18 / TypeScript)
* **Styling:** Tailwind CSS with Nexora Dark Athletic design tokens
* **Database & ORM:** Neon Serverless PostgreSQL with Drizzle ORM
* **Offline Engine:** Service Worker (`public/sw.js`) and Web Storage (`localStorage`)
* **Access Control:** `qrcode` Level H rendering, `jsQR` frame decoders, Web Audio API sound synthesis
* **Messaging & Communication:** WhatsApp Business API deep linking (`+92 325 9491349`)
