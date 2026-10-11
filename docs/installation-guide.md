# GymFlow &mdash; Installation & Deployment Guide

This guide walks you through setting up GymFlow in both local development environments and production hosting servers.

---

## Table of Contents
1. [Prerequisites](#1-prerequisites)
2. [Local Development Setup](#2-local-development-setup)
   - [Method A: Standalone PHP CLI & MySQL (Recommended)](#method-a-standalone-php-cli--mysql-recommended)
   - [Method B: XAMPP / Laragon / WampServer](#method-b-xampp--laragon--wampserver)
3. [Environment Configuration (`.env`)](#3-environment-configuration-env)
4. [Database Initialization & Seeding](#4-database-initialization--seeding)
5. [Production Deployment Guide](#5-production-deployment-guide)
   - [cPanel / StackCP / Shared Hosting](#cpanel--stackcp--shared-hosting)
   - [Virtual Private Server (VPS / Ubuntu / Nginx)](#virtual-private-server-vps--ubuntu--nginx)
   - [Enabling HTTPS for PWA Compliance](#enabling-https-for-pwa-compliance)
6. [Automating Scheduled Cron Reminders](#6-automating-scheduled-cron-reminders)
7. [Default Test Credentials](#7-default-test-credentials)
8. [Troubleshooting Guide](#8-troubleshooting-guide)

---

## 1. Prerequisites

Before installing, ensure your machine or server has:
* **PHP 8.0+** (PHP 8.1 or 8.2 recommended)
* **MySQL 5.7+** or **MariaDB 10.3+**
* Enabled PHP extensions: `pdo_mysql`, `mbstring`, `json`, `openssl`, `session`
* Web server: Apache (with `mod_rewrite`), Nginx, or the native PHP CLI server.

---

## 2. Local Development Setup

### Method A: Standalone PHP CLI & MySQL (Recommended)

This method requires no bulky server suites and boots up in seconds.

1. **Clone or Download the Repository:**
   ```bash
   git clone https://github.com/moeezahmad-tech/gymflow.git
   cd gymflow
   ```

2. **Configure the Environment:**
   Copy the example environment template:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` with your local MySQL credentials:
   ```ini
   GYMFLOW_DB_HOST=127.0.0.1
   GYMFLOW_DB_PORT=3306
   GYMFLOW_DB_NAME=gym_flow
   GYMFLOW_DB_USERNAME=root
   GYMFLOW_DB_PASSWORD=
   ```

3. **Import Database Schema:**
   Create the database and import `schema.sql`:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS gym_flow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p gym_flow < schema.sql
   ```

4. **Launch the Built-in Server with the Router:**
   GymFlow includes a built-in router emulator (`router.php`) supporting clean, extensionless URLs:
   ```bash
   php -S localhost:8080 router.php
   ```

5. **Open in Browser:**
   * Landing Page: `http://localhost:8080/`
   * Admin Portal: `http://localhost:8080/admin/`
   * Member Portal: `http://localhost:8080/user/`
   * Mobile PWA: `http://localhost:8080/app/`

---

### Method B: XAMPP / Laragon / WampServer

1. **Move Project Files:**
   Copy the `GYM management system` directory into your web server's public root:
   * **XAMPP:** `C:\xampp\htdocs\gymflow`
   * **Laragon:** `C:\laragon\www\gymflow`

2. **Create the Database in phpMyAdmin:**
   * Open `http://localhost/phpmyadmin`
   * Create a new database named `gym_flow` with collation `utf8mb4_unicode_ci`.
   * Click **Import** &rarr; Select `schema.sql` from the project folder &rarr; Click **Go**.

3. **Configure `.env`:**
   Ensure `.env` matches your XAMPP settings:
   ```ini
   GYMFLOW_DB_HOST=127.0.0.1
   GYMFLOW_DB_PORT=3306
   GYMFLOW_DB_NAME=gym_flow
   GYMFLOW_DB_USERNAME=root
   GYMFLOW_DB_PASSWORD=
   ```

4. **Access the Project:**
   Navigate to `http://localhost/gymflow/` in your browser.

---

## 3. Environment Configuration (`.env`)

The `.env` file should never be committed to public version control. Configure it according to your hosting target:

```ini
# Database Connection Settings
GYMFLOW_DB_HOST=127.0.0.1
GYMFLOW_DB_PORT=3306
GYMFLOW_DB_NAME=gym_flow
GYMFLOW_DB_USERNAME=root
GYMFLOW_DB_PASSWORD=your_secure_password
```

---

## 4. Database Initialization & Seeding

If you need to re-seed or verify default user passwords and accounts:
```bash
php scripts/seed_users.php
```
This utility ensures all default accounts exist, hashes passwords with fresh Bcrypt salts, and verifies member subscriptions.

---

## 5. Production Deployment Guide

### cPanel / StackCP / Shared Hosting

1. **Upload Files:**
   Upload project files to `public_html` (or a subdomain directory like `public_html/gymflow`).
2. **Import MySQL Database:**
   * Open cPanel **MySQL Databases** &rarr; create a new database and user &rarr; grant All Privileges.
   * Open **phpMyAdmin** &rarr; select the database &rarr; import `schema.sql`.
3. **Configure `.env`:**
   In cPanel File Manager, ensure hidden files are shown. Create or edit `.env`:
   ```ini
   GYMFLOW_DB_HOST=localhost
   GYMFLOW_DB_NAME=your_cpanel_dbname
   GYMFLOW_DB_USERNAME=your_cpanel_dbuser
   GYMFLOW_DB_PASSWORD=your_cpanel_dbpass
   ```
4. **Apache Verification:**
   Ensure `mod_rewrite` is active and `.htaccess` is present in the root folder.

---

### Virtual Private Server (VPS / Ubuntu / Nginx)

1. **Install PHP 8.2 and Extensions:**
   ```bash
   sudo apt update
   sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-curl php8.2-xml nginx mysql-server
   ```
2. **Configure Nginx Server Block:**
   ```nginx
   server {
       listen 80;
       server_name gymflow.yourdomain.com;
       root /var/www/gymflow;
       index index.php;

       location / {
           try_files $uri $uri/ $uri.php?$args;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
       }

       location ~ /\. {
           deny all;
       }
   }
   ```

---

### Enabling HTTPS for PWA Compliance

Progressive Web App features (such as Service Worker caching and install prompts) **require HTTPS**:
* On VPS: Install Let's Encrypt with Certbot:
  ```bash
  sudo apt install -y certbot python3-certbot-nginx
  sudo certbot --nginx -d gymflow.yourdomain.com
  ```
* On cPanel: Enable **AutoSSL** in the cPanel SSL/TLS Status tab.

---

## 6. Automating Scheduled Cron Reminders

To automatically trigger daily payment reminder scans and overdue notifications, configure a recurring cron job:

### Linux / cPanel Crontab
Run `crontab -e` and add the following entry to execute daily at 8:00 AM:
```cron
0 8 * * * /usr/bin/php /path/to/gymflow/cron/payment_reminders.php >> /path/to/gymflow/cron/cron.log 2>&1
```

### Webhook / Cron-Job.org Alternative
If CLI access is unavailable, configure an external cron service (like cron-job.org) to ping the secure webhook URL once daily:
```
GET https://gymflow.yourdomain.com/cron/payment_reminders.php?token=GYMFLOW_CRON_SECRET
```

---

## 7. Default Test Credentials

| Portal | Email | Password | Role |
| :--- | :--- | :--- | :--- |
| **Admin Console** | `admin@gymflow.com` | `Admin123!` | Administrator |
| **Staff & Coach** | `staff@gymflow.com` | `Staff123!` | Staff / Trainer |
| **Member Portal** | `member@gymflow.com` | `Member123!` | Member |
| **Member Portal** | `moeezahmad.tech@gmail.com` | `password123` | Member |

---

## 8. Troubleshooting Guide

### 1. "Database Connection Notice"
* Verify MySQL is running (`mysqladmin -u root status`).
* Check credentials in `.env` (ensure correct host, port, db name, and user).
* Make sure `schema.sql` has been imported into your target database.

### 2. 404 Not Found on Clean URLs (`/about`, `/pricing`)
* When using the PHP built-in server, ensure you launched it with `router.php`:
  ```bash
  php -S localhost:8080 router.php
  ```
* When using Apache, ensure `mod_rewrite` is enabled and `AllowOverride All` is set in your Apache config.

### 3. CSRF Token Mismatch Error
* Verify that browser sessions and cookies are enabled.
* Ensure session storage directory is writable by the PHP web process.
