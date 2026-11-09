# GymFlow &mdash; System & Technical Requirements Specification

This document outlines the software, environment, network, and operational requirements necessary to install, run, and scale the modern **GymFlow** platform using **Next.js 15** and **Neon Serverless PostgreSQL**.

---

## Table of Contents
1. [Software Requirements](#1-software-requirements)
2. [Hardware & Hosting Requirements](#2-hardware--hosting-requirements)
3. [Client-Side & Browser Requirements](#3-client-side--browser-requirements)
4. [Environment Configuration (`.env`)](#4-environment-configuration-env)
5. [External Service Integrations](#5-external-service-integrations)
6. [Non-Functional Requirements (NFR)](#6-non-functional-requirements-nfr)

---

## 1. Software Requirements

### Modern Serverless Full-Stack (Next.js 15 + Neon DB)

| Component | Minimum Requirement | Recommended Specification |
| :--- | :--- | :--- |
| **Node.js Runtime** | `v18.18.0+` | `Node.js v20 LTS` or `v24 LTS` |
| **Package Manager** | `npm v9+` | `npm v10+` |
| **Full-Stack Framework** | `Next.js 15.0+` (App Router) | `Next.js 15.5+` with React 19 |
| **Database System** | `PostgreSQL 15+` | `Neon Serverless PostgreSQL 16` |
| **ORM & Migrations** | `drizzle-orm v0.38+` | `drizzle-orm` + `drizzle-kit` |
| **Driver** | `@neondatabase/serverless` | WebSocket & pooled HTTP pipelining |
| **PWA & Offline** | HTML5 Service Workers | `public/sw.js` & `manifest.json` |
| **QR Code Engine** | `qrcode v1.5+` | Level H 2D Matrix Generator |
| **Optical Frame Decoder** | `jsqr v1.4+` | Camera Frame Canvas Scanner |
| **Hosting Platform** | `Vercel (Hobby Free)` | Vercel Global Edge Network ($0/mo) |

---

## 2. Hardware & Hosting Requirements

### 2.1 Local Development Environment
* **CPU:** 64-bit multi-core processor (Intel Core i3/i5/i7/i9, AMD Ryzen, Apple Silicon M1/M2/M3/M4).
* **RAM:** 4 GB minimum (8 GB+ recommended).
* **Disk Space:** 500 MB for repository and `node_modules`.

### 2.2 Cloud Production Hosting (Free Tier Strategy)
* **Web & Edge Hosting:** [Vercel Hobby Tier](https://vercel.com/) (100% Free)
  - 100 GB Bandwidth / month
  - Unlimited serverless edge invocations within standard limits
  - Automated SSL certificate provisioning via Let's Encrypt
  - Automated Edge Cron (`vercel.json`)
* **Database Hosting:** [Neon Free Tier](https://neon.tech/) (100% Free)
  - 0.5 GB Serverless storage
  - Auto-suspend when inactive (Scale-to-Zero)
  - PgBouncer pooled connections on port `5432`

---

## 3. Client-Side & Browser Requirements

### 3.1 Supported Web Browsers

| Browser | Desktop Support | Mobile / Tablet Support | Camera / PWA Capability |
| :--- | :---: | :---: | :--- |
| **Google Chrome / Chromium** | v90+ | v90+ (Android) | Full (PWA Install, `getUserMedia`, Web Audio) |
| **Apple Safari** | v14+ | iOS 14+ | Full (Add to Home Screen, Camera, Audio) |
| **Mozilla Firefox** | v88+ | v88+ | Full (Camera, Audio, Responsive Shell) |
| **Microsoft Edge** | v90+ | v90+ | Full (`BarcodeDetector`, Camera, Audio) |
| **Samsung Internet** | N/A | v14+ | Full (Android PWA Install, Camera) |

### 3.2 Turnstile & Camera Hardware Specifications
* **Optical Scanner:** Any smartphone rear/front camera or 720p+ laptop/tablet webcam.
* **Barcode Scanner Guns:** Any standard 2D USB/Bluetooth barcode scanner gun operating in keyboard wedge mode.
* **Audio Hardware:** Built-in device speakers (uses HTML5 Web Audio API, requires 0 external audio files).

---

## 4. Environment Configuration (`.env`)

```ini
# Core Environment
NODE_ENV=development
APP_NAME="GymFlow"
APP_URL="http://localhost:3000"

# Neon Serverless PostgreSQL (Pooled for Port 5432)
DATABASE_URL="postgresql://user:password@ep-your-endpoint-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require"

# Direct Neon Connection (for Drizzle migrations)
DIRECT_URL="postgresql://user:password@ep-your-endpoint.us-east-1.aws.neon.tech/neondb?sslmode=require"

# Secrets
AUTH_SECRET="your_production_secret_key_here"
CRON_SECRET="your_cron_bearer_token_here"
```

---

## 5. External Service Integrations

* **Neon Database (PostgreSQL):** Serverless data persistence and connection pooling.
* **WhatsApp Deep Links (`wa.me`):** Direct deep-linking for front desk inquiries, admission confirmations, and coach support.
* **Vercel Cron:** Automated invocation of `/api/cron/payment-reminders` at 08:00 AM UTC.
* **Google Fonts CDN:** Pre-connected typography (`Teko` and `Plus Jakarta Sans`).
* **Font Awesome CDN:** Iconography via Font Awesome 6.5.1 Free.

---

## 6. Non-Functional Requirements (NFR)

* **Performance & Sub-Second Latency:** Turnstile verification via `/api/attendance` executes in under 200ms round-trip.
* **Security & SQL Injection Immunity:** All database operations execute through Drizzle ORM parameterized queries with strict TLS 1.3 encryption.
* **High-Contrast Dark Obsidian Aesthetic:** Meets WCAG AAA contrast ratios for front-desk and dimly lit gym environments.
* **Offline Resilience:** PWA shell and QR pass remain accessible on member devices even during cellular signal blackouts.
