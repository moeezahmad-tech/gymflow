# GymFlow &mdash; RESTful API & Serverless Route Handlers

This document describes the serverless route handlers, request payloads, response schemas, and normalizers available in the Next.js 15 App Router backend of **GymFlow**.

---

## Table of Contents
1. [General API Standards](#1-general-api-standards)
2. [Turnstile Verification Route (`/api/attendance`)](#2-turnstile-verification-route-apiattendance)
   - [GET /api/attendance (Camera & URL Scan Verification)](#21-get-apiattendance)
   - [POST /api/attendance (Hardware & Webhook Entry Log)](#22-post-apiattendance)
   - [Smart Member Code Normalizer](#23-smart-member-code-normalizer)
3. [Automated Billing Cron Worker (`/api/cron/payment-reminders`)](#3-automated-billing-cron-worker-apicronpayment-reminders)
4. [HTTP Status Codes & Error Formats](#4-http-status-codes--error-formats)

---

## 1. General API Standards

* **Base URL:** `http://localhost:3000/` (Local) or `https://your-domain.vercel.app/` (Production)
* **Response Content-Type:** `application/json; charset=utf-8`
* **Database Connection:** Serverless pooled query against **Neon PostgreSQL** via **Drizzle ORM**
* **Cross-Origin Requests (CORS):** Fully supported for mobile PWAs, external hardware bridges, and kiosk tablets.

---

## 2. Turnstile Verification Route (`/api/attendance`)

Located at `src/app/api/attendance/route.ts`. Handles real-time credential validation, status checks, dues cutoff verification, and event logging.

---

### 2.1 GET /api/attendance

Used by smartphone cameras (iOS Camera, Google Lens), browser taps, and direct URL lookups.

* **Method:** `GET`
* **URL:** `/api/attendance?code={memberCode}&log={true|false}`
* **Parameters:**
  * `code` (string, required): Member ID (e.g. `2026-4`) or full scan URL.
  * `log` (boolean, optional, default `true`): Whether to record an attendance record in the database.

#### Example Request:
```bash
curl -X GET "http://localhost:3000/api/attendance?code=2026-4&log=true"
```

#### Successful Access Granted Response (`HTTP 200`):
```json
{
  "success": true,
  "access": "granted",
  "timestamp": "2026-03-09T18:45:00.000Z",
  "member": {
    "id": 4,
    "fullName": "Moeez Ahmad",
    "memberCode": "2026-4",
    "email": "moeezahmad.tech@gmail.com",
    "phone": "+92 325 9491349",
    "status": "active",
    "avatar": null,
    "planName": "3-Month Pro Athlete Plan",
    "subscriptionStatus": "active",
    "expiryDate": "10/05/2026"
  },
  "message": "Access Granted. Welcome to GymFlow, Moeez Ahmad!"
}
```

#### Access Denied Response (`HTTP 403` - Delinquent / Suspended):
```json
{
  "success": false,
  "access": "denied",
  "member": {
    "id": 99,
    "fullName": "Expired Athlete",
    "memberCode": "2026-99",
    "status": "suspended",
    "avatar": null
  },
  "message": "Access Denied: Membership status is SUSPENDED. Visit front desk."
}
```

#### Unknown Member Code (`HTTP 404`):
```json
{
  "success": false,
  "access": "denied",
  "memberCode": "INVALID-99",
  "message": "Unknown member code \"INVALID-99\". Not registered in system."
}
```

---

### 2.2 POST /api/attendance

Used by physical USB barcode scanner guns, turnstile relay controllers, and kiosk devices.

* **Method:** `POST`
* **URL:** `/api/attendance`
* **Headers:** `Content-Type: application/json`

#### Request Payload:
```json
{
  "memberCode": "2026-4",
  "entryType": "qr_scanner"
}
```

#### Field Specifications:
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `memberCode` | `string` | **Yes** | Scanned text, QR URL, or raw member code |
| `entryType` | `string` | No | `qr_scanner`, `optical_cam`, or `manual` (Default: `qr_scanner`) |

#### Successful Response (`HTTP 200`):
```json
{
  "success": true,
  "access": "granted",
  "timestamp": "2026-03-09T18:45:00.000Z",
  "member": {
    "id": 4,
    "fullName": "Moeez Ahmad",
    "memberCode": "2026-4",
    "status": "active",
    "planName": "3-Month Pro Athlete Plan"
  },
  "message": "Welcome, Moeez Ahmad! Turnstile gate unlocked."
}
```

---

### 2.3 Smart Member Code Normalizer

The route handler includes an automated normalizer (`cleanMemberCode`) allowing it to parse any QR format effortlessly:
* **Raw Member ID:** `"2026-4"` $\rightarrow$ `"2026-4"`
* **Full Verification URL:** `"http://localhost:3000/scan?code=2026-4"` $\rightarrow$ `"2026-4"`
* **Structured Payload Token:** `"GYMFLOW:PASS:2026-4"` $\rightarrow$ `"2026-4"`
* **Encoded URI Components:** `"2026%2D4"` $\rightarrow$ `"2026-4"`

This ensures physical 2D barcode scanner guns, camera frame decoders, and direct smartphone cameras all succeed reliably.

---

## 3. Automated Billing Cron Worker (`/api/cron/payment-reminders`)

Located at `src/app/api/cron/payment-reminders/route.ts`. Executes daily at 08:00 AM UTC via Vercel Cron.

* **Method:** `GET`
* **URL:** `/api/cron/payment-reminders`
* **Headers:** `Authorization: Bearer <CRON_SECRET>`

#### Action Lifecycle:
1. Queries Neon PostgreSQL for subscriptions with renewal dates on the upcoming **10th of the month**.
2. Identifies accounts past the 10th cutoff with unrecorded payments.
3. Automatically locks gate clearance for delinquent accounts in the `attendance` validator.
4. Generates formatted WhatsApp reminder payloads for automated notification dispatch.

#### Successful Response (`HTTP 200`):
```json
{
  "success": true,
  "timestamp": "2026-03-09T08:00:00.000Z",
  "accountsChecked": 142,
  "upcomingCutoffNotices": 14,
  "delinquentLockoutsEnforced": 3,
  "message": "Daily 10th-cutoff billing worker completed successfully."
}
```

---

## 4. HTTP Status Codes & Error Formats

| Status Code | Reason | Meaning |
| :--- | :--- | :--- |
| **`200 OK`** | Success | Request succeeded; turnstile access granted or data retrieved |
| **`400 Bad Request`** | Validation Error | Member code was omitted or invalid payload format |
| **`403 Forbidden`** | Access Denied | Member account is `inactive`, `suspended`, or past 10th cutoff |
| **`404 Not Found`** | Unknown Member | Scanned code does not exist in Neon PostgreSQL |
| **`500 Internal Error`** | Server Error | Database connection timeout or unexpected runtime exception |

#### Standardized Error Envelope:
```json
{
  "success": false,
  "access": "denied",
  "message": "Human-readable explanation of error or denial reason."
}
```
