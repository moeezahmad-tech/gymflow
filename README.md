# GymFlow &mdash; Modern Gym & Fitness Management Platform

<p align="center">
  <img src="assets/images/favicon.svg" alt="GymFlow Logo" width="96" height="96" />
</p>

<p align="center">
  <strong>Elevate Your Strength & Peak Performance</strong><br>
  A comprehensive, high-performance gym management ecosystem featuring public marketing, member self-service portals, administrative billing & CRM, and an installable Progressive Web App (PWA).
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8+" />
  <img src="https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/TailwindCSS-v3.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white" alt="Progressive Web App" />
  <img src="https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge" alt="License" />
</p>

---

## 📌 Table of Contents
1. [Overview](#-overview)
2. [Key Capabilities & Modules](#-key-capabilities--modules)
3. [Technology Stack](#-technology-stack)
4. [Quick Start (Local Development)](#-quick-start-local-development)
5. [Default Login Credentials](#-default-login-credentials)
6. [Repository Structure](#-repository-structure)
7. [Comprehensive Documentation Index](#-comprehensive-documentation-index)
8. [Automated Background Reminders](#-automated-background-reminders)
9. [License & Acknowledgments](#-license--acknowledgments)

---

## 🚀 Overview

**GymFlow** is an all-in-one software solution engineered for commercial gyms, private fitness studios, and health clubs. It eliminates disparate spreadsheets and fragmented software by consolidating:

* **Public Marketing Site:** Responsive showcase for fitness programs, facility amenities, membership pricing in PKR, and prospective inquiries.
* **Member Self-Service Portal (`/user`):** Real-time membership passes, streak tracking, turnstile access QR codes, and personal billing history.
* **Progressive Web App (`/app`):** Installable mobile-first web app with offline capabilities, direct coach communication, and one-tap WhatsApp integration.
* **Administrative Operations Console (`/admin`):** Real-time financial analytics, 10th-cutoff monthly billing workflows, member lifecycle management, package CRUD builder, and inquiries inbox.

---

## ⚡ Key Capabilities & Modules

### 1. Public Marketing Portal
* **Programs & Facilities:** Highlight HIIT, bodybuilding, Olympic lifting zones, Finnish saunas, and cold plunge recovery.
* **Transparent Pricing in PKR:** Dynamic tiers pulled from MySQL with highlighted "Most Popular" badges.
* **Prospect Lead Capture:** Contact forms and newsletter subscriptions connected to the administrative CRM.

### 2. Member Portal (`/user`)
* **Digital Turnstile Pass:** Dynamic QR pass and Member Code (e.g., `2026-4`) for entry.
* **Consistency Streak Engine:** Tracks consecutive daily workouts and logs monthly gym visits.
* **Fee Status & Receipts:** Instant visibility into paid invoices and upcoming renewals.

### 3. Mobile Progressive Web App (`/app`)
* **Installable Native Feel:** Works offline and installs directly to Android and iOS home screens via `manifest.json` and `sw.js`.
* **Coach Marcus & Front Desk Chat:** Topic-oriented inquiry dispatch with automated WhatsApp deep links.

### 4. Administrative Control Suite (`/admin`)
* **KPI Metrics Dashboard:** Revenue metrics, active member tallies, and upcoming expiration warnings.
* **Member Directory & Quick Actions:** Search and filter members, edit subscriptions, or use the "Mark as Paid" modal.
* **Cutoff Billing Schedule:** Enforces standard monthly 10th-cutoff fee schedules with audit ledgers for cash, cards, JazzCash, and EasyPaisa.
* **Dynamic Plan Builder:** Configure plans, pricing in PKR, durations, and JSON-based feature bullets directly from the UI.
* **Message Inquiries Center:** Process and resolve incoming customer contacts and coaching requests.

---

## 🛠 Technology Stack

* **Backend:** Native PHP 8.0+ (No heavy third-party framework overhead, blazing fast execution).
* **Database:** MySQL 5.7+ / MariaDB 10.3+ (InnoDB with strict foreign key constraints and indexed queries).
* **Styling & UI:** Tailwind CSS, custom glassmorphism design tokens (`assets/css/style.css`), Font Awesome 6.5.1, and Google Fonts (`Teko` & `Plus Jakarta Sans`).
* **Mobile / PWA:** Service Worker caching (`sw.js`) and Web App Manifest (`manifest.json`).
* **Architecture:** Modular Monolithic MVC pattern with Singleton PDO database abstraction and tokenized CSRF security.

---

## 🏃 Quick Start (Local Development)

### Prerequisites
* PHP 8.0+ (`pdo_mysql`, `mbstring`, `json`, `openssl`, `session` extensions enabled)
* MySQL / MariaDB Server

### Step-by-Step Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/moeezahmad-tech/gymflow.git
   cd gymflow
   ```

2. **Configure Environment Variables:**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Open `.env` and configure your local database credentials:
   ```ini
   GYMFLOW_DB_HOST=127.0.0.1
   GYMFLOW_DB_PORT=3306
   GYMFLOW_DB_NAME=gym_flow
   GYMFLOW_DB_USERNAME=root
   GYMFLOW_DB_PASSWORD=
   ```

3. **Import Database Schema:**
   Import the schema and seed data into your MySQL server:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS gym_flow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p gym_flow < schema.sql
   ```

4. **Launch the Development Server:**
   Start the PHP built-in server with the bundled router emulator:
   ```bash
   php -S localhost:8080 router.php
   ```

5. **Visit GymFlow in your Browser:**
   * **Public Website:** [http://localhost:8080/](http://localhost:8080/)
   * **Admin Dashboard:** [http://localhost:8080/admin/](http://localhost:8080/admin/)
   * **Member Portal:** [http://localhost:8080/user/](http://localhost:8080/user/)
   * **Mobile Web App:** [http://localhost:8080/app/](http://localhost:8080/app/)

---

## 🔐 Default Login Credentials

The initial database seed provides pre-configured verified credentials:

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@gymflow.com` | `Admin123!` | `/admin/` (Full Administrative Suite) |
| **Staff / Coach** | `staff@gymflow.com` | `Staff123!` | `/admin/` (Front Desk & Member CRM) |
| **Active Member** | `member@gymflow.com` | `Member123!` | `/user/` and `/app/` (Member Dashboard) |
| **Active Member** | `moeezahmad.tech@gmail.com` | `password123` | `/user/` and `/app/` (Member Dashboard) |

---

## 📁 Repository Structure

```
GYM management system/
├── admin/               # Administrative suite (dashboard, members, billing, plans)
├── api/                 # REST API gateway endpoints (plans, status, newsletter)
├── app/                 # Standalone Mobile Progressive Web App (PWA)
├── assets/              # Static styling (CSS, JavaScript, brand images, icons)
├── components/          # Reusable UI partials (header, footer, admin sidebar)
├── config/              # Core configurations (app, database PDO, auth, notifications)
├── cron/                # Automated CLI/webhook payment reminder scripts
├── docs/                # Comprehensive technical documentation
├── scripts/             # Database seeder and maintenance scripts
├── user/                # Member self-service portal
├── index.php            # Public landing page
├── router.php           # Built-in PHP server router (.htaccess emulator)
├── schema.sql           # Complete MySQL database schema & seed data
├── manifest.json        # PWA application manifest
└── sw.js                # Service Worker for offline app shell
```

---

## 📚 Comprehensive Documentation Index

Explore the in-depth documentation inside the [`docs/`](file:///e:/Coding/GYM%20management%20system/docs/) folder:

* 📖 **[Features Specification (`docs/features.md`)](file:///e:/Coding/GYM%20management%20system/docs/features.md)**: Exhaustive breakdown of public web features, member portal, mobile PWA, administrative billing, CRM, and security matrices.
* ⚙️ **[System & Technical Requirements (`docs/requirements.md`)](file:///e:/Coding/GYM%20management%20system/docs/requirements.md)**: PHP versions, required extensions, web server configs (Apache/Nginx/PHP CLI), and hardware sizing.
* 🏛️ **[System Architecture (`docs/architecture.md`)](file:///e:/Coding/GYM%20management%20system/docs/architecture.md)**: Architectural diagrams (Mermaid), URL routing mechanics, RBAC layer, and notification pipeline.
* 🗄️ **[Database Dictionary & ERD (`docs/database.md`)](file:///e:/Coding/GYM%20management%20system/docs/database.md)**: Complete 9-table schema dictionary, Mermaid ERD, column data types, foreign keys, and indexes.
* 🔌 **[RESTful API Reference (`docs/api.md`)](file:///e:/Coding/GYM%20management%20system/docs/api.md)**: JSON endpoint contracts, query parameters, request payloads, and status codes.
* 📦 **[Installation & Deployment Manual (`docs/installation-guide.md`)](file:///e:/Coding/GYM%20management%20system/docs/installation-guide.md)**: Production deployment instructions for cPanel, VPS, Apache, Nginx, SSL setup, and troubleshooting.

---

## ⏰ Automated Background Reminders

GymFlow features an automated notification scanner that detects upcoming and overdue payments:

### Running via CLI:
```bash
php cron/payment_reminders.php
```

### Running via Automated Crontab:
```cron
0 8 * * * /usr/bin/php /path/to/gymflow/cron/payment_reminders.php >> /path/to/gymflow/cron/cron.log 2>&1
```

### Running via Webhook / External Cron Service:
```
GET https://yourdomain.com/cron/payment_reminders.php?token=GYMFLOW_CRON_SECRET
```

---

## 📄 License & Acknowledgments

* **GymFlow** &copy; 2026. All rights reserved.
* Built with modern PHP, Tailwind CSS, Font Awesome, and love for peak athletic performance.
