# GymFlow &mdash; Database Schema & Data Dictionary

This document provides a complete reference for the GymFlow relational database architecture hosted on **Neon Serverless PostgreSQL** and managed via **Drizzle ORM**.

---

## Table of Contents
1. [Database Architecture & Serverless Specifications](#1-database-architecture--serverless-specifications)
2. [Modular Drizzle Schema Structure](#2-modular-drizzle-schema-structure)
3. [Entity-Relationship Diagram (ERD)](#3-entity-relationship-diagram-erd)
4. [Table Data Dictionary](#4-table-data-dictionary)
   - [users (`database/schema/users.ts`)](#41-users)
   - [membership_plans (`database/schema/plans.ts`)](#42-membership_plans)
   - [subscriptions (`database/schema/subscriptions.ts`)](#43-subscriptions)
   - [payments (`database/schema/payments.ts`)](#44-payments)
   - [attendance (`database/schema/attendance.ts`)](#45-attendance)
   - [notifications_log (`database/schema/notifications.ts`)](#46-notifications_log)
   - [contact_messages (`database/schema/messages.ts`)](#47-contact_messages)
   - [newsletter_subscribers (`database/schema/newsletter.ts`)](#48-newsletter_subscribers)
5. [Default Seed Data & User Credentials](#5-default-seed-data--user-credentials)
6. [Strict 10th-Cutoff Billing Logic & State Machine](#6-strict-10th-cutoff-billing-logic--state-machine)
7. [Connection Strings & Configuration](#7-connection-strings--configuration)

---

## 1. Database Architecture & Serverless Specifications

* **Database Engine:** PostgreSQL 16 (Serverless)
* **Provider:** [Neon](https://neon.tech/) &mdash; Scale-to-Zero serverless Postgres with instant branching and pooled connections.
* **ORM:** **Drizzle ORM** (`drizzle-orm`, `drizzle-kit`)
* **Driver:** `@neondatabase/serverless` with WebSocket and HTTP pipelining.
* **Connection Pooling:** PgBouncer on port `5432`.
* **Character Encoding:** UTF-8 (`UTF8mb4` fully supported).
* **Currency Standard:** Pakistani Rupee (`PKR` &bull; `numeric(10, 2)`).

---

## 2. Modular Drizzle Schema Structure

The database schema is organized into modular TypeScript files located in `database/schema/`:

```
database/
├── index.ts                 # Database client instantiation using @neondatabase/serverless
└── schema/
    ├── users.ts             # Users, authentication credentials, roles & statuses
    ├── plans.ts             # Membership packages, pricing in PKR, duration
    ├── subscriptions.ts     # Active/expired subscriptions, 10th-cutoff dates
    ├── payments.ts          # Ledger invoices, payment channels (Cash, JazzCash, EasyPaisa)
    ├── attendance.ts        # Turnstile scan logs, gate access status
    ├── notifications.ts     # System alerts and WhatsApp reminder logs
    ├── messages.ts          # Lead inquiries and coach consultation messages
    ├── newsletter.ts        # Email subscribers and campaigns
    └── index.ts             # Relations, foreign key associations & barrel exports
```

---

## 3. Entity-Relationship Diagram (ERD)

```mermaid
erDiagram
    USERS ||--o{ SUBSCRIPTIONS : "subscribes to"
    USERS ||--o{ PAYMENTS : "makes"
    USERS ||--o{ ATTENDANCE : "logs check-ins"
    USERS ||--o{ NOTIFICATIONS_LOG : "receives"
    MEMBERSHIP_PLANS ||--o{ SUBSCRIPTIONS : "defines"
    SUBSCRIPTIONS ||--o{ PAYMENTS : "billed under"

    USERS {
        serial id PK
        varchar member_code UK "e.g. 2026-4"
        varchar full_name
        varchar email UK
        varchar phone
        text password
        enum role "admin, staff, member"
        text avatar
        enum status "active, inactive, suspended"
        timestamp created_at
        timestamp updated_at
    }

    MEMBERSHIP_PLANS {
        serial id PK
        varchar name "e.g. 3-Month Pro Athlete"
        varchar slug UK
        text description
        numeric price "PKR"
        integer duration_days
        jsonb features
        boolean is_popular
        enum status "active, inactive"
        timestamp created_at
    }

    SUBSCRIPTIONS {
        serial id PK
        integer user_id FK
        integer plan_id FK
        date start_date
        date end_date
        enum status "active, expired, pending, cancelled"
        timestamp created_at
    }

    PAYMENTS {
        serial id PK
        integer user_id FK
        integer subscription_id FK
        varchar invoice_number UK
        numeric amount "PKR"
        enum payment_method "cash, card, jazzcash, easypaisa, bank_transfer"
        varchar transaction_ref
        enum status "paid, pending, overdue, refunded"
        timestamp paid_at
        timestamp created_at
    }

    ATTENDANCE {
        serial id PK
        integer user_id FK
        timestamp check_in_time
        varchar entry_type "qr_scanner, optical_cam, manual"
        varchar status "granted, denied"
        text notes
    }
```

---

## 4. Table Data Dictionary

### 4.1 users
Defined in `database/schema/users.ts`. Stores system users, staff, and enrolled gym athletes.

| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `serial` | `PRIMARY KEY` | Unique internal identifier |
| `member_code` | `varchar(30)` | `NOT NULL, UNIQUE` | Sequential athlete identifier (e.g., `2026-4`) |
| `full_name` | `varchar(100)` | `NOT NULL` | Athlete or employee full name |
| `email` | `varchar(150)` | `NOT NULL, UNIQUE` | Unique email for authentication & alerts |
| `phone` | `varchar(30)` | `NULLABLE` | WhatsApp/SMS phone number |
| `password` | `text` | `NOT NULL` | Secure password hash |
| `role` | `enum` | `DEFAULT 'member'` | Role: `admin`, `staff`, `member` |
| `avatar` | `text` | `NULLABLE` | URL or path to athlete photo |
| `status` | `enum` | `DEFAULT 'active'` | Status: `active`, `inactive`, `suspended` |
| `created_at` | `timestamp` | `DEFAULT now()` | Record creation timestamp |
| `updated_at` | `timestamp` | `DEFAULT now()` | Last update timestamp |

---

### 4.2 membership_plans
Defined in `database/schema/plans.ts`. Stores pricing tiers and inclusive perks.

| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `serial` | `PRIMARY KEY` | Plan unique ID |
| `name` | `varchar(100)` | `NOT NULL` | Package title (e.g. `3-Month Pro Athlete Plan`) |
| `slug` | `varchar(100)` | `NOT NULL, UNIQUE` | URL-friendly slug (e.g. `3-months`) |
| `description` | `text` | `NULLABLE` | Plan summary description |
| `price` | `numeric(10, 2)`| `NOT NULL` | Price in Pakistani Rupee (e.g. `22500.00`) |
| `duration_days` | `integer` | `NOT NULL` | Validity duration (`30`, `90`, `180` days) |
| `features` | `jsonb` | `NULLABLE` | Array of itemized perks and amenities |
| `is_popular` | `boolean` | `DEFAULT false` | Highlights with "Most Popular" badge |
| `status` | `enum` | `DEFAULT 'active'` | Active or retired plan |

---

### 4.3 subscriptions
Defined in `database/schema/subscriptions.ts`. Manages active memberships, start dates, and 10th-cutoff deadlines.

| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `serial` | `PRIMARY KEY` | Subscription ID |
| `user_id` | `integer` | `REFERENCES users(id)` | Foreign key to `users` |
| `plan_id` | `integer` | `REFERENCES membership_plans(id)` | Foreign key to `membership_plans` |
| `start_date` | `date` | `NOT NULL` | Subscription activation date |
| `end_date` | `date` | `NOT NULL` | Cutoff expiration date (aligned to 10th) |
| `status` | `enum` | `DEFAULT 'active'` | `active`, `expired`, `pending`, `cancelled` |

---

### 4.4 payments
Defined in `database/schema/payments.ts`. Financial audit ledger recording multi-channel transactions.

| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `serial` | `PRIMARY KEY` | Ledger transaction ID |
| `user_id` | `integer` | `REFERENCES users(id)` | Foreign key to `users` |
| `subscription_id`| `integer` | `REFERENCES subscriptions(id)` | Foreign key to `subscriptions` |
| `invoice_number` | `varchar(50)` | `NOT NULL, UNIQUE` | Format: `INV-2026-101` |
| `amount` | `numeric(10, 2)`| `NOT NULL` | Amount paid in PKR |
| `payment_method` | `enum` | `NOT NULL` | `cash`, `card`, `jazzcash`, `easypaisa`, `bank_transfer` |
| `transaction_ref`| `varchar(100)`| `NULLABLE` | Gateway Trx ID or receipt number |
| `status` | `enum` | `DEFAULT 'paid'` | `paid`, `pending`, `overdue`, `refunded` |
| `paid_at` | `timestamp` | `NULLABLE` | Timestamp payment cleared |

---

### 4.5 attendance
Defined in `database/schema/attendance.ts`. Live turnstile check-in event logs.

| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `serial` | `PRIMARY KEY` | Event log ID |
| `user_id` | `integer` | `REFERENCES users(id)` | Foreign key to `users` |
| `check_in_time` | `timestamp` | `DEFAULT now()` | Exact timestamp of turnstile unlatch |
| `entry_type` | `varchar(50)` | `DEFAULT 'qr_scanner'`| `qr_scanner`, `optical_cam`, `manual` |
| `status` | `varchar(20)` | `DEFAULT 'granted'` | `granted` or `denied` |
| `notes` | `text` | `NULLABLE` | Operational log notes |

---

## 5. Default Seed Data & User Credentials

The database comes pre-seeded with active accounts for testing and evaluation:

```
Users Table Seed Data:
┌─────┬───────────┬──────────────────┬─────────────────────────────┬────────┬────────┐
│ ID  │ Code      │ Name             │ Email                       │ Role   │ Status │
├─────┼───────────┼──────────────────┼─────────────────────────────┼────────┼────────┤
│ 1   │ 2026-1    │ Admin Director   │ admin@gymflow.com           │ admin  │ active │
│ 2   │ 2026-2    │ Front Desk Staff │ staff@gymflow.com           │ staff  │ active │
│ 3   │ 2026-3    │ Active Athlete   │ athlete@gymflow.com         │ member │ active │
│ 4   │ 2026-4    │ Moeez Ahmad      │ moeezahmad.tech@gmail.com   │ member │ active │
└─────┴───────────┴──────────────────┴─────────────────────────────┴────────┴────────┘
Default Password for all seed accounts: athlete123 (or admin123)
```

---

## 6. Strict 10th-Cutoff Billing Logic & State Machine

GymFlow enforces a universal **10th-of-the-month cutoff billing engine**:

```mermaid
stateDiagram-v2
    [*] --> Active: First Payment Received
    Active --> DueNotice: 1st - 9th of Renewal Month
    DueNotice --> Active: Dues Paid via Cash/EasyPaisa/JazzCash
    DueNotice --> LockedTurnstile: 11th of Month (Unpaid)
    LockedTurnstile --> Active: Payment Recorded at Front Desk
```

1. **Cycle Cutoff:** All recurring monthly renewals are due on the 10th of the calendar month.
2. **Grace Period Notice:** From the 1st through the 9th, members receive gentle reminder badges in their PWA app.
3. **Turnstile Enforcement:** On the 11th, any account whose status is `pending` or `overdue` has its gate clearance automatically changed to **LOCKED / DENIED**.
4. **Instant Reactivation:** As soon as staff records payment in `/admin/billing`, the account switches to `active`, unlatching turnstiles immediately.

---

## 7. Connection Strings & Configuration

Configure in `.env`:

```ini
# Pooled Connection (Next.js App Router, Server Actions, Route Handlers)
DATABASE_URL="postgresql://user:password@ep-your-endpoint-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require"

# Direct Connection (Drizzle Migrations & Drizzle Studio)
DIRECT_URL="postgresql://user:password@ep-your-endpoint.us-east-1.aws.neon.tech/neondb?sslmode=require"
```
