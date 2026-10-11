# GymFlow &mdash; Feature Specification & Functional Capabilities

GymFlow is an end-to-end gym and fitness club management platform built with PHP 8, MySQL/MariaDB, Tailwind CSS, and Progressive Web App (PWA) standards. It bridges the gap between public club marketing, dedicated member portals, administrative controls, financial ledgers, and on-the-go mobile fitness tracking.

---

## Table of Contents
1. [Core System Overview](#core-system-overview)
2. [Public Web Presence & Marketing](#public-web-presence--marketing)
3. [Member Portal (Web Dashboard)](#member-portal-web-dashboard)
4. [Standalone Mobile Web App (PWA)](#standalone-mobile-web-app-pwa)
5. [Administrative Suite & Control Center](#administrative-suite--control-center)
   - [Dashboard Metrics & Analytics](#dashboard-metrics--analytics)
   - [Member Directory & CRM](#member-directory--crm)
   - [Billing, Invoicing & Cutoff Schedules](#billing-invoicing--cutoff-schedules)
   - [Payment Processing & Transaction Records](#payment-processing--transaction-records)
   - [Membership Plans & Packages](#membership-plans--packages)
   - [Inquiries & Message Processing](#inquiries--message-processing)
   - [Attendance & Turnstile Control](#attendance--turnstile-control)
   - [Automated Messaging & Reminders Engine](#automated-messaging--reminders-engine)
   - [Newsletter & Email Broadcasts](#newsletter--email-broadcasts)
6. [Security, Authentication & Role-Based Access Control](#security-authentication--role-based-access-control)
7. [Feature Summary Matrix](#feature-summary-matrix)

---

## 1. Core System Overview

GymFlow delivers a unified ecosystem catering to three primary user tiers:
* **Guests & Prospects:** Discover facilities, explore workout programs, compare membership packages in PKR, subscribe to fitness newsletters, and submit consultation inquiries.
* **Members:** Access personal workout records, manage subscription validity, generate digital QR passes for turnstile access, record daily check-ins, view past invoices, and communicate directly with certified trainers.
* **Administrators & Staff:** Full operational control over member lifecycles, automated financial cutoff dates, offline & online payment reconciliation, package configuration, and system alerts.

---

## 2. Public Web Presence & Marketing

### 2.1 Dynamic Landing Page (`index.php`)
* **Hero Banner:** Bold fitness aesthetic with modern typography (`Teko` and `Plus Jakarta Sans`), high-energy imagery, and clear calls to action (Join Now, View Pricing).
* **Live Stats Counters:** Displays active members count, certified fitness coaches, gym floor area (sq ft), and modern equipment units.
* **Interactive Program Showcase:** Highlights high-intensity training, bodybuilding, boxing conditioning, and athletic mobility programs.
* **Featured Membership Tiers:** Pulls real-time packages directly from MySQL database with automatic "Most Popular" tags and pricing in PKR.
* **Client Testimonials & Transformation Highlights:** Carousel and social proof cards demonstrating member success stories.
* **Newsletter Signup Form:** Seamless AJAX subscription connected to `api/subscribe.php`.

### 2.2 Dedicated Information Pages
* **About Us (`about.php`):** Philosophy, facility history, core mission, safety protocols, and certified trainer profiles.
* **Programs (`programs.php`):** Deep dive into training programs (HIIT, Strength & Hypertrophy, Functional Cross-training, Yoga & Recovery), schedule intensity indicators, and trainer assignments.
* **Facilities (`facilities.php`):** Visual tour of the Olympic lifting zones, cardio cinema, Finnish sauna, cold plunge, recovery lounge, and locker rooms.
* **Pricing & Plans (`pricing.php`):** Clean matrix comparing 1-Month, 3-Month, and 6-Month plans, inclusive perks (sauna, guest passes, personal trainer assessment), and FAQs.
* **Contact & Inquiries (`contact.php`):** Interactive inquiry form with program selection, front desk operating hours, location details, interactive map integration, and direct WhatsApp contact.
* **Legal Compliance (`privacy.php`, `terms.php`):** Standard privacy protocols and member terms and conditions.

---

## 3. Member Portal (Web Dashboard)

Located at `/user/index.php`, the Member Portal provides authorized gym members with an interactive workspace:

### 3.1 Digital Membership Pass
* **Live Status Badge:** Highlights current status (`Active`, `Expired`, `Pending Renewal`, `Suspended`).
* **Member Identification:** Shows Member Code (e.g., `2026-4`), joining date, and subscription expiration date.
* **Turnstile Access QR Generator:** Dynamic visual QR pass encoding the member's credentials for fast check-in.

### 3.2 Attendance & Consistency Tracking
* **One-Click Daily Check-in:** Members can record their daily session directly from the portal.
* **Duplicate Prevention:** Enforces one valid check-in per calendar day to preserve audit accuracy.
* **Consistency Streaks:** Automatic calculation of consecutive training days and total monthly workouts.
* **Historical Access Log:** Tabulated history displaying check-in timestamps, entry methods (`qr_scanner`, `manual`), and status (`granted`).

### 3.3 Financial & Subscription Summary
* **Active Plan Details:** Price (PKR), duration, and renewal countdown.
* **Payment History:** Complete record of historical invoices, transaction reference IDs, payment methods (Cash, Card, JazzCash, EasyPaisa), and paid dates.

---

## 4. Standalone Mobile Web App (PWA)

Located at `/app/index.php`, GymFlow features a dedicated mobile web application interface optimized for handheld devices:

### 4.1 Progressive Web App Capabilities
* **Installable Application:** Registered via `manifest.json` and `sw.js` (Service Worker) allowing users to install GymFlow as a native app on iOS and Android home screens.
* **Offline Fallback:** Caches vital shell assets, styles, and fonts for instant loading in poor connectivity environments.
* **Bottom Navigation Bar:** Native app navigation bar (Home, Workout/QR Pass, Coach Support, Account).

### 4.2 Interactive Coach & Front Desk Support (`app/coach.php`)
* **Topic-Based Consultation:** Members can request assistance on Workout Plans, Nutrition blueprints, Membership Renewals, or Locker Room feedback.
* **Instant WhatsApp Integration:** One-tap action that pre-populates messages and launches WhatsApp direct chat with the assigned gym trainer or front desk manager.
* **Direct Inquiry Logging:** Automatically records conversations into the `contact_messages` table for administrative review.

---

## 5. Administrative Suite & Control Center

Located under `/admin/`, this suite empowers staff and gym owners with tools for day-to-day operations:

### 5.1 Dashboard Metrics & Analytics (`admin/index.php`)
* **Revenue Metrics:** Total revenue collected in PKR, monthly cash flow, and outstanding receivables.
* **Member Statistics:** Total registered members, active memberships, expiring memberships within 7 days, and pending renewals.
* **Attendance Flow:** Real-time headcount of members checked in today.
* **Quick Actions:** Instant modal for "Quick Add Member" and "Quick Record Payment" directly from the dashboard.

### 5.2 Member Directory & CRM (`admin/members.php`, `members-add.php`, `members-edit.php`)
* **Search & Filters:** Search by Name, Member Code, Email, or Phone; filter by Membership Plan and Status (`active`, `inactive`, `suspended`).
* **Automatic Member Code Generation:** Automatically sequences sequential alphanumeric codes (e.g., `2026-1`, `2026-2`).
* **Profile Management:** Edit contact information, reset passwords, update membership plans, or adjust status.
* **Quick "Mark as Paid" Modal:** One-click modal allowing desk staff to record fee collection directly from the member list without leaving the page.

### 5.3 Billing, Invoicing & Cutoff Schedules (`admin/billing.php`)
* **10th Cutoff Schedule:** Implements the monthly gym billing cycle where fees are billed on the 10th of every month.
* **Audit Ledgers:** Detailed breakdown of paid, pending, and overdue invoices across all members.
* **Overdue Highlights:** Visual warning indicators for past-due balances.
* **Payment Method Tracking:** Segregates Cash, Card, JazzCash, EasyPaisa, and Bank Transfers for simple end-of-day cash reconciliation.

### 5.4 Payment Processing & History (`admin/payments.php`)
* **Transaction Ledger:** Chronological audit log of all financial transactions with unique system IDs (e.g., `TXN-98234-A101`, `TXN-FD-XXXX`).
* **Receipt Verification:** Detailed view of transaction amount, user reference, paid timestamp, and optional desk notes.

### 5.5 Membership Plans & Packages (`admin/plans.php`)
* **Dynamic Package Builder:** Create, edit, and toggle fitness plans without touching database code.
* **JSON-Powered Feature Bullets:** Manage itemized bullets (e.g., "Full 24/7 Gym Access", "Sauna Access", "1 Free Trainer Assessment") parsed dynamically.
* **Promotional Flags:** Mark specific tiers as "Popular" or "Best Value" to render highlighted cards on the public pricing page.

### 5.6 Inquiries & Message Processing (`admin/messages.php`)
* **Unified Inbox:** Receives prospect inquiries from public contact forms and member support requests from the mobile app.
* **Status Flags:** Mark inquiries as `read`, `unread`, or delete spam entries.
* **Direct Outreach:** Click-to-email or click-to-WhatsApp links to immediately respond to prospects.

### 5.7 Attendance & Turnstile Control (`admin/attendance.php`)
* **Turnstile Access Monitoring:** Foundation for hardware turnstiles and QR scan kiosks.
* *Note:* The front desk hardware turnstile interface is toggleable via configuration to support both digital-first and manual front desk environments.

### 5.8 Automated Messaging & Reminders Engine (`config/notifications.php`, `cron/payment_reminders.php`)
* **Automated Cron Jobs:** Scheduled CLI or webhook task (`php cron/payment_reminders.php`) that scans for memberships expiring or due today/overdue.
* **Dynamic Template Substitution:** Replaces variables like `{{member_name}}`, `{{amount}}`, `{{due_date}}`, and `{{payment_url}}`.
* **Multi-Channel Dispatch:** Ready for SMS gateways and WhatsApp Business API endpoints, with database logging into `notifications_log`.

### 5.9 Newsletter & Email Broadcasts (`admin/newsletter.php`, `api/subscribe.php`)
* **Subscriber Directory:** Captures emails, subscription timestamps, and client IP addresses.
* **Duplicate Protection:** Automatically updates or reactivates returning subscribers.

---

## 6. Security, Authentication & Role-Based Access Control

* **Bcrypt Password Encryption:** Passwords hashed with Bcrypt (cost factor 10) with fallback support for legacy hash verification.
* **CSRF Token Guard:** All POST forms and state-mutating requests validate cryptographic CSRF tokens stored in the user session.
* **Role-Based Access Control (RBAC):**
  * `admin`: Complete access to all financial, member, and configuration modules.
  * `staff`: Access to front desk check-in, payment recording, and member directories.
  * `member`: Restricted to personal dashboard, workout tracking, and profile information.
* **Session Hardening:** Cookies enforced with `HttpOnly`, `SameSite=Lax`, and automated regeneration upon login to eliminate session fixation.
* **Input Sanitization & Prepared Statements:** PDO prepared statements utilized across all database interactions to prevent SQL injection vulnerabilities.

---

## 7. Feature Summary Matrix

| Module | Features Included | Primary User |
| :--- | :--- | :--- |
| **Marketing Site** | Hero, Programs, Facilities, Pricing in PKR, Contact Forms, Testimonials | Guests, Prospects |
| **Member Portal** | QR Pass, Check-in tracker, Streak counter, Invoice history | Gym Members |
| **Mobile PWA** | Offline app shell, Coach support, WhatsApp integration, Turnstile access | Members on Mobile |
| **Admin Console** | Revenue stats, active members count, overdue alerts, recent transactions | Admins, Staff |
| **Member CRM** | Alphanumeric Member Code generation, CRUD, status management | Admins, Staff |
| **Billing Hub** | 10th cutoff billing cycle, mark-as-paid modals, cash reconciliation | Admins, Staff |
| **Package Builder** | Dynamic plan pricing in PKR, JSON feature lists, popular badges | Admins |
| **Cron Notifications** | Automated daily scan, overdue alerts, SMS/WhatsApp templates | System Daemon |
| **Inquiries Inbox** | Contact form submissions, status management, quick replies | Staff, Desk Managers |
