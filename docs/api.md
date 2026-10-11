# GymFlow &mdash; RESTful API & Endpoint Documentation

This document describes the application programming interfaces (APIs), endpoints, request payloads, response schemas, and authentication methods available in GymFlow.

---

## Table of Contents
1. [General API Standards](#1-general-api-standards)
2. [Public API Gateway (`/api/index.php`)](#2-public-api-gateway-apiindexphp)
   - [System Health & Status](#21-system-health--status)
   - [Active Membership Plans](#22-active-membership-plans)
   - [Contact Inquiry Submission](#23-contact-inquiry-submission)
3. [Newsletter Subscription API (`/api/subscribe.php`)](#3-newsletter-subscription-api-apisubscribephp)
4. [Mobile App Internal API (`/app/api.php`)](#4-mobile-app-internal-api-appapiphp)
   - [Turnstile / Daily Check-in](#41-turnstile--daily-check-in)
   - [Direct Coach Consultation](#42-direct-coach-consultation)
5. [Automated Cron Webhook (`/cron/payment_reminders.php`)](#5-automated-cron-webhook-cronpayment_remindersphp)
6. [Error Handling & HTTP Status Codes](#6-error-handling--http-status-codes)

---

## 1. General API Standards

* **Base URL:** `http://localhost:8080/` (or your production domain).
* **Data Format:** JSON (`Content-Type: application/json; charset=utf-8`).
* **Cross-Origin Requests (CORS):** Supported for local and app origins.
* **Authentication:** Session-cookie based for mobile app endpoints; public endpoints require input validation.

---

## 2. Public API Gateway (`/api/index.php`)

### 2.1 System Health & Status
Checks application runtime status, version, and database readiness.

* **Endpoint:** `GET /api/index.php?action=status`
* **Auth Required:** No

#### Example Response:
```json
{
  "status": "success",
  "app": "Gym Flow",
  "version": "1.1.0",
  "database": "configured",
  "timestamp": 1728637920
}
```

---

### 2.2 Active Membership Plans
Retrieves all currently active membership tiers, pricing in PKR, and features.

* **Endpoint:** `GET /api/index.php?action=plans`
* **Auth Required:** No

#### Example Response:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "1 Month Package",
      "slug": "1-month",
      "description": "Essential gym floor & strength access for 1 full month",
      "price": "3000.00",
      "duration_days": 30,
      "features": "[\"Standard Gym Floor Access (5AM - 11PM)\", \"Locker room & shower facilities\", \"1 Free Trainer Fitness Assessment\", \"GymFlow Mobile App Workout Tracker\", \"No Long-Term Commitment\"]",
      "is_popular": 0,
      "status": "active"
    },
    {
      "id": 2,
      "name": "3 Month Package",
      "slug": "3-months",
      "description": "Our most popular quarterly package with classes & training perks",
      "price": "8000.00",
      "duration_days": 90,
      "features": "[\"Full 24/7 Gym Access for 90 Days\", \"All Fitness Classes Included (Boxing, HIIT, Yoga)\", \"Monthly 1-on-1 Personal Trainer Session\", \"Custom Nutrition & Meal Blueprint\", \"Finnish Sauna & Cold Plunge Access\", \"Save PKR 1,000 vs Monthly\"]",
      "is_popular": 1,
      "status": "active"
    }
  ]
}
```

---

### 2.3 Contact Inquiry Submission
Receives contact inquiries submitted via web or external mobile clients.

* **Endpoint:** `POST /api/index.php?action=contact`
* **Headers:** `Content-Type: application/json` or `application/x-www-form-urlencoded`
* **Auth Required:** No

#### Request Payload:
```json
{
  "full_name": "Tariq Malik",
  "email": "tariq.malik@example.com",
  "phone": "+92 301 5551234",
  "program": "HIIT Conditioning",
  "message": "I would like to inquire about evening batch timings and personal trainer availability."
}
```

#### Example Success Response (HTTP 200):
```json
{
  "status": "success",
  "message": "Thank you for reaching out! Our Gym Flow fitness advisors will contact you shortly."
}
```

#### Example Error Response (HTTP 400):
```json
{
  "status": "error",
  "message": "Please provide full name, email, and message."
}
```

---

## 3. Newsletter Subscription API (`/api/subscribe.php`)

Handles prospect email subscription to updates and fitness promotions.

* **Endpoint:** `POST /api/subscribe.php`
* **Headers:** `Content-Type: application/json`
* **Auth Required:** No

#### Request Payload:
```json
{
  "email": "member.prospect@gmail.com"
}
```

#### Example Response (HTTP 200):
```json
{
  "status": "success",
  "message": "Welcome to Gym Flow! You've successfully subscribed to our VIP updates and workout tips."
}
```

---

## 4. Mobile App Internal API (`/app/api.php`)

Protected endpoints used by the Progressive Web App (PWA) client interface.

### 4.1 Turnstile / Daily Check-in
Records daily member attendance via the mobile interface.

* **Endpoint:** `POST /app/api.php?action=check_in`
* **Auth Required:** Yes (Active member session cookie)

#### Example Success Response (First Check-in Today):
```json
{
  "success": true,
  "message": "Turnstile Gate Access Granted! Check-in recorded.",
  "status": "granted",
  "time": "03:15 PM"
}
```

#### Example Response (Already Checked In Today):
```json
{
  "success": true,
  "already_checked": true,
  "message": "You are already checked in today at 03:15 PM",
  "time": "03:15 PM"
}
```

---

### 4.2 Direct Coach Consultation
Submits a direct question to the coaching staff from the mobile dashboard.

* **Endpoint:** `POST /app/api.php?action=send_coach_message`
* **Auth Required:** Yes (Active member session cookie)

#### Request Payload:
```x-www-form-urlencoded
message=Could Coach Marcus adjust my deadlift form routine for this week?
```

#### Example Response:
```json
{
  "success": true,
  "message": "Your message has been sent directly to Coach Marcus & Front Desk! We will respond shortly."
}
```

---

## 5. Automated Cron Webhook (`/cron/payment_reminders.php`)

Executes the automated notification scanner over HTTP or CLI.

* **Endpoint:** `GET /cron/payment_reminders.php?token=GYMFLOW_CRON_SECRET`
* **Auth Required:** Webhook secret token check (`token=GYMFLOW_CRON_SECRET`)

#### Example Response:
```json
{
  "status": "success",
  "timestamp": "2026-10-11 15:00:00",
  "scan_date": "2026-10-11",
  "total_sent": 2,
  "duration_sec": 0.0421,
  "details": [
    {
      "user_id": 3,
      "member": "Alex Johnson",
      "phone": "+923001234569",
      "amount": 8000,
      "due_date": "2026-10-11",
      "is_overdue": false,
      "channels": {
        "sms": "sent",
        "whatsapp": "sent"
      }
    }
  ]
}
```

---

## 6. Error Handling & HTTP Status Codes

| HTTP Status | Meaning | Typical Trigger |
| :--- | :--- | :--- |
| `200 OK` | Success | Valid request processed successfully |
| `400 Bad Request` | Validation Error | Missing mandatory fields (name, email, message) |
| `401 Unauthorized` | Unauthenticated | Mobile action attempted without an active login |
| `403 Forbidden` | Access Denied | Invalid webhook secret token on cron endpoint |
| `405 Method Not Allowed` | Method Mismatch | GET request sent to a POST-only endpoint |
| `500 Server Error` | Database or Runtime Error | Database failure or unhandled exception |
