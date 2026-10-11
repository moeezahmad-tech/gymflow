# GymFlow &mdash; System & Technical Requirements Specification

This document outlines the software, hardware, environment, network, and operational requirements necessary to install, run, and scale the GymFlow Gym Management Platform.

---

## Table of Contents
1. [Software Requirements](#1-software-requirements)
   - [PHP Environment](#php-environment)
   - [Database Management System](#database-management-system)
   - [Web Server Options](#web-server-options)
2. [Hardware & Hosting Requirements](#2-hardware--hosting-requirements)
3. [Client-Side & Browser Requirements](#3-client-side--browser-requirements)
4. [Environment Configuration (`.env`)](#4-environment-configuration-env)
5. [External Service Integrations](#5-external-service-integrations)
6. [Non-Functional Requirements (NFR)](#6-non-functional-requirements-nfr)
   - [Security & Access Control](#security--access-control)
   - [Performance & Latency](#performance--latency)
   - [Reliability & Scalability](#reliability--scalability)
   - [Data Integrity & Backup](#data-integrity--backup)

---

## 1. Software Requirements

### PHP Environment
GymFlow is built on vanilla PHP 8.x, ensuring high performance, zero external framework overhead, and straightforward maintenance.

| Parameter | Minimum Requirement | Recommended |
| :--- | :--- | :--- |
| **PHP Version** | `PHP 8.0.0` | `PHP 8.1` or `PHP 8.2` |
| **Execution SAPI** | CLI / FPM / FastCGI / Apache Module | PHP-FPM 8.2 with OPcache |
| **Memory Limit** | `128 MB` | `256 MB` |
| **Upload Max Size** | `10 MB` | `25 MB` (for avatars & media) |
| **Max Execution Time** | `30 seconds` | `60 seconds` |

#### Required PHP Extensions
The following PHP extensions must be enabled in `php.ini`:
* **`pdo_mysql`**: Database communication with MySQL/MariaDB.
* **`openssl`**: Cryptographically secure token generation (`random_bytes()`) and hashing.
* **`mbstring`**: Multibyte string parsing and UTF-8 collation handling.
* **`json`**: Dynamic JSON encoding and decoding for plan features and API responses.
* **`session`**: Server-side user authentication state and RBAC session storage.
* **`curl`** *(Optional)*: Dispatched SMS gateway and WhatsApp Cloud API HTTP requests.
* **`gd`** or **`imagick`** *(Optional)*: Profile avatar cropping and image optimization.

### Database Management System
GymFlow relies on relational database architecture using InnoDB engine with full ACID compliance and foreign key cascades.

| Parameter | Minimum Requirement | Recommended |
| :--- | :--- | :--- |
| **Database Engine** | MySQL `5.7+` or MariaDB `10.3+` | MySQL `8.0+` or MariaDB `10.6+` |
| **Storage Engine** | InnoDB (mandatory for foreign key constraints) | InnoDB with `innodb_file_per_table=1` |
| **Default Charset** | `utf8mb4` | `utf8mb4` |
| **Default Collation** | `utf8mb4_unicode_ci` | `utf8mb4_unicode_ci` |

### Web Server Options

GymFlow supports all standard HTTP servers with URL rewriting:

#### Option A: Apache HTTP Server (cPanel, XAMPP, LAMP)
* `mod_rewrite` module **must be enabled**.
* `.htaccess` execution must be enabled via `AllowOverride All` in Apache virtual host configuration.
* Rules already included in `.htaccess` support extensionless URLs (`/about`, `/pricing`, `/admin`, `/app`).

#### Option B: Built-in PHP Development Server (Local Dev)
* Supported out of the box using the supplied `router.php` proxy emulator:
  ```bash
  php -S localhost:8080 router.php
  ```

#### Option C: Nginx + PHP-FPM
* Nginx server block configuration required for extensionless rewriting:
  ```nginx
  server {
      listen 80;
      server_name gymflow.local;
      root /var/www/gymflow;
      index index.php;

      location / {
          try_files $uri $uri/ $uri.php?$args;
      }

      location ~ \.php$ {
          include fastcgi_params;
          fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
      }

      location ~ /\. {
          deny all;
      }
  }
  ```

---

## 2. Hardware & Hosting Requirements

### Development Environment
* **Processor:** 1.8 GHz dual-core or faster (Intel, AMD, Apple Silicon).
* **RAM:** 4 GB minimum (8 GB recommended).
* **Disk Space:** 500 MB free storage.

### Production Shared Hosting / VPS
* **CPU:** 1 vCPU minimum (2+ vCPU recommended for high concurrency).
* **RAM:** 1 GB RAM minimum (2 GB to 4 GB recommended).
* **Disk Storage:** 5 GB SSD storage (accommodates member records, avatars, database dumps, logs).
* **Bandwidth:** 100 GB+ monthly transfer.

---

## 3. Client-Side & Browser Requirements

### Supported Browsers
* **Desktop:** Google Chrome (v90+), Mozilla Firefox (v88+), Apple Safari (v14+), Microsoft Edge (v90+).
* **Mobile:** Mobile Chrome on Android (v90+), Mobile Safari on iOS (v14+), Samsung Internet (v14+).

### Progressive Web App (PWA) Requirements
* **HTTPS Protocol:** Mandatory in production environments for Service Worker (`sw.js`) registration and offline storage caching.
* **Display Mode:** `standalone` with support for high-resolution app icons (`192x192` and `512x512`).

---

## 4. Environment Configuration (`.env`)

GymFlow utilizes a streamlined `.env` configuration pattern without requiring heavy Composer autoloaders:

```ini
# Database Connection Details
GYMFLOW_DB_HOST=127.0.0.1
GYMFLOW_DB_PORT=3306
GYMFLOW_DB_NAME=gym_flow
GYMFLOW_DB_USERNAME=root
GYMFLOW_DB_PASSWORD=

# Base URL & Domain Override (Optional)
# BASE_URL=https://gymflow.yourdomain.com
```

---

## 5. External Service Integrations

| Service | Protocol / Standard | Purpose |
| :--- | :--- | :--- |
| **WhatsApp Messaging** | WhatsApp Click-to-Chat / WhatsApp Cloud API | Automated membership expiration alerts, front desk direct chat |
| **SMS Gateway** | HTTP REST Gateway / Twilio API | Instant text alerts for fee due and overdue turnstile warnings |
| **System Cron Service** | Linux `crontab` / Windows Task Scheduler | Daily automated execution of `cron/payment_reminders.php` |
| **Google Maps** | Embedded Iframe API | Interactive location map on Contact page |
| **Font Awesome CDN** | HTTPS CDN v6.5.1 | Iconography throughout admin and member dashboards |
| **Google Fonts** | Google Web Fonts API | Typography (`Plus Jakarta Sans` & `Teko`) |

---

## 6. Non-Functional Requirements (NFR)

### Security & Access Control
* **CSRF Mitigation:** Anti-CSRF token verification on all POST requests (`verifyCSRFToken()`).
* **Password Hashing:** Passwords hashed with `PASSWORD_BCRYPT` with cost factor 10.
* **SQL Injection Prevention:** 100% parameterization via PDO prepared statements.
* **Cross-Site Scripting (XSS) Prevention:** Output escaping via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
* **Privilege Separation:** Role-Based Access Control (`admin`, `staff`, `member`) enforced by `requireAdmin()` and `requireLogin()`.

### Performance & Latency
* **Page Load Time:** First Contentful Paint (FCP) < 1.0s on broadband connections.
* **Database Queries:** Optimized indexed lookups on `users(email)`, `users(role)`, `memberships(user_id, status)`, `payments(status, due_date)`.
* **PWA Caching:** Instant rendering of cached UI shell assets.

### Reliability & Scalability
* **Availability:** Target 99.9% uptime on standard LAMP/LEMP stacks.
* **Graceful Degradation:** Informative user notice if database connection fails, providing actionable troubleshooting steps.

### Data Integrity & Backup
* **Foreign Keys:** Enforced `ON DELETE CASCADE` on member records, and `ON DELETE SET NULL` on payments.
* **Transactions:** Database transactions (`beginTransaction()`, `commit()`, `rollBack()`) wrapped around payment processing and member updates.
