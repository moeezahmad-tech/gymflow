# GymFlow &mdash; Design System & Visual Identity Specification (`design.md`)

> **Master UI/UX Design System Specification for GymFlow**  
> **Aesthetic Theme:** Dark Obsidian Athletic &bull; Kinetic Red Accents &bull; Glassmorphism &bull; Clean Typography  
> **Code Reference:** Synchronized directly with [`assets/css/style.css`](file:///e:/Coding/GYM%20management%20system/assets/css/style.css) and [`components/head.php`](file:///e:/Coding/GYM%20management%20system/components/head.php).

---

## 1. Aesthetic Ethos & Brand Character

GymFlow is designed for high-performance athletic environments. Its visual identity balances modern luxury, kinetic energy, and operational clarity:
* **Dark Obsidian Foundation:** Deep blacks and charcoal surfaces reduce eye fatigue in dimly lit front desks and create high-contrast focus for dynamic metrics.
* **Athletic Crimson Red Accents:** Energetic red elements (`#ff2a2a`) draw immediate focus to primary actions, overdue billing flags, and active buttons.
* **Modern Glassmorphism:** Semi-translucent panels (`backdrop-blur-md`) with crisp zinc borders give depth to cards, navigation bars, and modals.
* **Kinetic Typography:** Bold, condensed uppercase headers (`Teko`) evoke strength and athletic urgency, paired with ultra-legible modern body sans-serif (`Plus Jakarta Sans`).

---

## 2. Color Palette & Design Tokens

### 2.1 Core Brand Colors (Exact Code Values)

| Token Name | HEX / RGBA | CSS Variable | Tailwind Utility | Semantic Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Pure Obsidian Black** | `#000000` | `--color-pure-black` | `bg-black`, `bg-dark-950` | Primary page backdrop, high-contrast base |
| **Dark Background** | `#070708` | `--color-dark-bg` | `bg-[#070708]`, `bg-dark-900` | Secondary backgrounds, drawer sidebars |
| **Dark Surface** | `#0f1013` | `--color-dark-surface` | `bg-[#0f1013]`, `bg-dark-850` | Navigation bars, table containers, inputs |
| **Dark Card Surface** | `#15161b` | `--color-dark-card` | `bg-[#15161b]`, `bg-dark-800` | Card surfaces, modal dialogues, tiles |
| **Athletic Brand Red** | `#ff2a2a` | `--color-brand-red` | `bg-brand-red`, `text-red-500` | Primary buttons, active tabs, brand logo |
| **Brand Red Hover** | `#e01f1f` | `--color-brand-red-hover`| `hover:bg-brand-red-hover` | Button hover state, interactive highlights |
| **Brand Red Dark** | `#991111` | `--color-brand-red-dark` | `bg-brand-red-dark` | Gradient ends, pressed button states |
| **Brand Neon Glow** | `rgba(255, 42, 42, 0.35)` | `--color-brand-glow` | `glow-red`, `shadow-red-500/25` | Soft halo on badges, active QR pass glow |
| **Zinc Border & Line** | `#22252c` | `--color-border` | `border-dark-700`, `border-zinc-800` | Card borders, dividers, subtle separators |

---

### 2.2 Semantic & Status Indicator Colors

| Status Role | HEX Code | Tailwind Utility | UI Application |
| :--- | :--- | :--- | :--- |
| **Active / Granted / Success** | `#10b981` / `#059669` | `text-emerald-400`, `bg-emerald-500` | Active member badge, granted turnstile access, completed payments |
| **Warning / Pending / Streaks** | `#f59e0b` / `#d97706` | `text-amber-400`, `bg-amber-500` | Expiring memberships, workout streak counters, pending invoices |
| **Alert / Delinquent / Denied** | `#ef4444` / `#dc2626` | `text-red-400`, `bg-red-600` | Overdue balances, turnstile access denied, form validation errors |
| **Primary Body Text** | `#f3f4f6` / `#ffffff` | `text-white`, `text-zinc-100` | High-contrast headlines, primary card values |
| **Muted & Secondary Text** | `#9ca3af` / `#71717a` | `text-zinc-400`, `text-zinc-500` | Timestamps, subtitles, helper hints, placeholders |

---

## 3. Typography Hierarchy

GymFlow imports Google Fonts directly in [`components/head.php`](file:///e:/Coding/GYM%20management%20system/components/head.php) and [`assets/css/style.css`](file:///e:/Coding/GYM%20management%20system/assets/css/style.css):

```html
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
```

### 3.1 Font Families

| Role | Font Family | Fallback | CSS Variable | Characteristics |
| :--- | :--- | :--- | :--- | :--- |
| **Display & Headings** | **Teko** | `sans-serif` | `--font-heading` | Condensed athletic grotesque, uppercase, bold tracking (`0.05em`) |
| **Body & Interface** | **Plus Jakarta Sans** | `sans-serif` | `--font-sans` | Geometric modern sans-serif, high legibility, clean letterform |
| **Identifiers & Numbers** | **Monospace** | `JetBrains Mono, monospace` | `font-mono` | Monospace digits for Member Codes (`2026-4`), Currency, and Transaction IDs |

---

### 3.2 Type Scale & Applications

| Level | Desktop Size | Mobile Size | Font Family | Weight & Transform | Example Application |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Hero Display** | 64px – 84px | 38px – 48px | `Teko` | 700 &bull; UPPERCASE | Landing page hero statements |
| **Heading 1 (H1)** | 36px – 48px | 28px – 34px | `Teko` | 700 &bull; UPPERCASE | Page titles, module headers |
| **Heading 2 (H2)** | 24px – 32px | 20px – 24px | `Teko` | 600 &bull; UPPERCASE | Section titles, modal titles |
| **Heading 3 (H3)** | 18px – 22px | 16px – 18px | `Teko` | 600 &bull; UPPERCASE | Card titles, drawer category labels |
| **Body Large** | 16px / 1.6 | 15px / 1.5 | `Plus Jakarta Sans` | 400 – 500 &bull; Sentence case | Lead paragraphs, marketing summaries |
| **Body Standard** | 14px / 1.5 | 13px / 1.5 | `Plus Jakarta Sans` | 400 – 500 &bull; Sentence case | Table data, form labels, card copy |
| **Small / Micro** | 11px – 12px | 10px – 11px | `Plus Jakarta Sans` | 600 – 700 &bull; UPPERCASE | Status pills, table headers, breadcrumbs |

---

## 4. UI Components & Visual Patterns

### 4.1 Buttons & Interactive Controls

#### Primary Action Button (`.btn-primary`)
```html
<button class="bg-[#ff2a2a] hover:bg-[#e01f1f] text-white font-bold uppercase tracking-wider text-xs px-6 py-3.5 rounded-2xl shadow-lg shadow-red-600/30 hover:-translate-y-0.5 active:scale-95 transition-all">
    Confirm Action
</button>
```
* **Visual Style:** Solid crimson red, rounded-2xl, soft red drop shadow, subtle hover lift.

#### Outline Button (`.btn-outline`)
```html
<button class="bg-transparent border border-zinc-700/80 hover:border-red-500 text-white font-bold uppercase tracking-wider text-xs px-6 py-3.5 rounded-2xl transition-all">
    Cancel / Secondary
</button>
```

---

### 4.2 Glassmorphism Cards & Panels

#### Standard Content Glass Card (`.glass-card`)
```css
.glass-card {
    background: linear-gradient(135deg, rgba(21, 22, 27, 0.7) 0%, rgba(12, 13, 16, 0.9) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid #22252c;
    border-radius: 1rem;
}
.glass-card:hover {
    border-color: rgba(255, 42, 42, 0.45);
    box-shadow: 0 10px 30px -10px rgba(255, 42, 42, 0.25);
}
```

---

### 4.3 Status Indicators & Pulsing Badges

* **Active / Valid Pass Badge:**
  ```html
  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
      Active Member
  </span>
  ```
* **Pending / Due Badge:**
  ```html
  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-wider">
      <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
      Due on 10th
  </span>
  ```
* **Overdue / Suspended Badge:**
  ```html
  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold uppercase tracking-wider">
      <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
      Overdue
  </span>
  ```

---

### 4.4 Digital Turnstile Pass (Mobile PWA & Member Portal)

* **Container Style:** High-contrast dark tile (`bg-black/90 border border-red-500/40 rounded-3xl p-6`).
* **Visual Glow Effect:** `box-shadow: 0 0 35px rgba(255, 42, 42, 0.25)`.
* **Dynamic QR Code:** White square high-contrast barcode background with centered member code watermark (`e.g., 2026-4`).
* **Consistency Streak Pill:** Positioned directly above the pass (`🔥 14-Day Streak &bull; 18 Visits This Month`).

---

### 4.5 Mobile Bottom Navigation Bar (PWA Shell)

Designed for single-thumb navigation on iOS and Android devices:
```html
<nav class="fixed bottom-0 inset-x-0 z-50 bg-[#070709]/95 backdrop-blur-xl border-t border-zinc-800/80 px-4 py-2 safe-bottom flex items-center justify-around">
    <!-- Tab 1: Home Dashboard -->
    <!-- Tab 2: Turnstile QR Pass -->
    <!-- Tab 3: Coach / WhatsApp Chat -->
    <!-- Tab 4: Account Profile -->
</nav>
```
* **Active State:** Red icon (`text-red-500`) with small dot indicator underneath.
* **Inactive State:** Muted icon (`text-zinc-500`).

---

## 5. Iconography & Media Guidelines

### 5.1 Font Awesome 6 CDN Standards
* Standardized on **Font Awesome 6.5.1 Free Solid and Regular** (`fa-solid`, `fa-regular`).
* Standard size ratios:
  * Metric card icons: `text-xl sm:text-2xl`
  * Menu navigation icons: `text-sm w-4 text-center`
  * Action button icons: `text-xs`

### 5.2 Brand Logos & Assets
* **Horizontal Brand Logo:** `assets/images/logo.png` (displayed with red accented text: `GYM<span class="text-red-500">FLOW</span>`).
* **App Icon & Favicons:**
  * Vector Favicon: `assets/images/favicon.svg` (SVG format)
  * PNG Favicons: `favicon-16x16.png`, `favicon-32x32.png`, `favicon.png`
  * Touch Icons: `apple-touch-icon.png` (180x180)
  * PWA Android Icons: `icon-192x192.png`, `icon-512x512.png`, `maskable-icon-512x512.png`

---

## 6. Responsive Breakpoint Matrix

| Breakpoint | Width (px) | Layout Adjustments |
| :--- | :--- | :--- |
| **Mobile (`default`)** | `< 640px` | Single-column cards, bottom fixed navigation for mobile app, collapsible mobile drawer for public site. |
| **Tablet (`sm` to `md`)** | `640px – 1023px` | 2-column KPI metric grids, compact top navbars, responsive table scrolling. |
| **Desktop (`lg` to `xl`)** | `1024px – 1439px` | Full fixed sidebar for Admin Console (`w-64`), multi-column analytics, full desktop headers. |
| **Ultra-Wide (`2xl`)** | `≥ 1536px` | Centered maximum container widths (`max-w-7xl` or `max-w-screen-2xl`) with consistent gutters. |

---

## 7. Zero Contradiction Cross-Reference Checklist

To prevent inconsistencies, all developers and AI coding assistants must verify against these standards:
- [x] **Brand Primary Color:** Must always be **Athletic Red (`#ff2a2a`)**, never Teal or Cyan.
- [x] **Background Palette:** Must always use Pure Obsidian (`#000000`) and Dark Charcoal (`#070708`, `#0f1013`, `#15161b`).
- [x] **Currency Display:** Must always format in Pakistani Rupee using `formatPrice($val)` &bull; Output: `PKR 8,500` or `₨ 8,500`.
- [x] **Headlines Font:** Must always use `Teko` (uppercase, tracked).
- [x] **Body Font:** Must always use `Plus Jakarta Sans`.
- [x] **Database Schema:** 9 InnoDB tables strictly named: `users`, `membership_plans`, `memberships`, `payments`, `attendance`, `notifications_log`, `contact_messages`, `newsletter_subscribers`, `email_campaigns`.

