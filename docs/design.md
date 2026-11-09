# GymFlow &mdash; Design System & Visual Identity Specification (`design.md`)
## Nexora Dark Athletic Aesthetic, Capital Case Typography & Component Standards

> **Master UI/UX Design System Specification for GymFlow**  
> **Theme:** Nexora Dark Obsidian Athletic &bull; Crimson Accent Red (`#ff2a2a`) &bull; Glassmorphic Panels &bull; Capital Case Typography  
> **Technical Stack:** Synchronized directly with [`src/app/globals.css`](file:///src/app/globals.css), [`tailwind.config.ts`](file:///tailwind.config.ts), and [`src/app/layout.tsx`](file:///src/app/layout.tsx).

---

## 1. Aesthetic Ethos & Brand Character

GymFlow is engineered for high-performance athletic environments. Its visual identity balances modern luxury, kinetic energy, and operational clarity:
* **Dark Obsidian Foundation:** Deep blacks (`bg-black`) and dark zinc surfaces (`bg-zinc-950`) provide a clean, glare-free aesthetic for front desks and mobile devices in gym lighting.
* **Athletic Crimson Red Accents:** Energetic red elements (`#ff2a2a`, `#e01f1f`) draw immediate focus to primary actions, active tabs, and key metrics.
* **Modern Glassmorphism & Fluted Pleats:** Semi-translucent panels (`backdrop-blur-md`) with subtle zinc borders (`border-zinc-900`) and vertical pleated dark texture overlays give depth to cards and headers.
* **Capital Case Typographical Elegance:** Eliminates forced all-caps screaming text in favor of clean, authoritative **Title Case / Capital Case** across all headlines, section headers, badges, and buttons.

---

## 2. Color Palette & Design Tokens

### 2.1 Core Brand Colors (Exact Code Values)

| Token Name | HEX / RGBA | Tailwind Utility | Semantic Usage |
| :--- | :--- | :--- | :--- |
| **Pure Obsidian Black** | `#000000` | `bg-black` | Primary page backdrop, high-contrast athletic canvas |
| **Dark Charcoal Surface** | `#070709` / `#09090b` | `bg-[#070709]`, `bg-zinc-950` | Card surfaces, modal dialogues, mobile shell backdrop |
| **Zinc Border & Separator** | `#18181b` / `#27272a` | `border-zinc-900`, `border-zinc-800` | Card borders, dividers, subtle table separators |
| **Athletic Brand Red** | `#ff2a2a` | `bg-brand-red`, `text-brand-red` | Primary CTA buttons, brand logo accents, active indicators |
| **Brand Red Hover** | `#e01f1f` | `hover:bg-brand-red-hover` | Interactive button hover states, active menu selections |
| **Brand Red Dark** | `#991111` | `bg-brand-red-dark` | Gradient stops, pressed button states |
| **Brand Neon Glow** | `rgba(255, 42, 42, 0.35)` | `shadow-pass-glow`, `shadow-brand-red/30` | Glowing halo on badges, active passes, and sparklines |
| **Active / Success Emerald** | `#10b981` | `text-emerald-400`, `bg-emerald-500` | Active member badges, granted turnstile access, completed dues |
| **Warning / Cutoff Amber** | `#f59e0b` | `text-amber-400`, `bg-amber-500` | 10th-cutoff deadlines, pending coach review flags |

---

## 3. Typographical Hierarchy & Casing Rules

### 3.1 Strict Casing Standards (Zero Forced All-Caps)
> [!IMPORTANT]
> **Strict Capital Case / Title Case Requirement:**  
> All headlines, navigation links, section titles, card headers, and button labels must use **Title Case / Capital Case** (e.g. *"The Strength Sanctuary Built for Peak Performance"*, *"Start Free Trial"*, *"Apply for 1-on-1 Coaching"*).  
> Never use forced CSS uppercase (`uppercase`) or raw ALL-CAPS strings on user-facing headings or buttons.

### 3.2 Font Families & Role Mapping
* **Headlines & Metric Accents (`font-heading`):** `Teko` (Google Fonts, sans-serif, weights 500, 600, 700). Provides tall, powerful athletic authority without needing uppercase.
* **Body & Supporting Copy (`font-sans`):** `Plus Jakarta Sans` (Google Fonts, sans-serif, weights 400 to 800). Delivers exceptional legibility on dark backgrounds.
* **Technical & Operational Data (`font-mono`):** `JetBrains Mono` / Monospace for Member IDs (`2026-4`), reference codes (`COACH-4821`), and timestamps.

---

## 4. Component Geometry & Layout Guidelines

### 4.1 Primary CTA Buttons
* **Fixed Height:** Exactly 48px (`h-12`) on primary landing page hero CTAs to prevent visual layout jumps.
* **Padding & Radius:** `px-8 rounded-full` with subtle shadow (`shadow-lg shadow-brand-red/30`).
* **Gap Spacing:** Always maintain `gap-4` between dual primary and secondary CTAs (`Start Free Trial` and `Book Gym Tour`).

### 4.2 Footer Spacing & Column Gaps
* **Upper Footer Padding:** Generous padding of `pt-24 pb-20` on [`PublicFooter`](file:///src/components/public-footer.tsx).
* **Grid Gaps:** Multi-column grid with `gap-12 sm:gap-14 lg:gap-16 xl:gap-20` to prevent visual crowding.
* **Header Capital Case:** Clean section headers: `"Explore"`, `"Portals"`, `"Metropolis HQ"`.

### 4.3 Authentic Gym Visuals vs. Excessive QR Codes
* **Hero & Landing Page Rule:** Do not clutter public landing pages with repetitive raw QR codes.
* **Authentic Athletic Indicators:** Use authentic performance indicators:
  * Total Iron Moved (e.g. `2.45 M kg`)
  * Workouts Logged (`18,200+`)
  * Gym Floor Volume with glowing red SVG area wave sparklines.
* **Where Real QR Codes Belong:** Reserve scannable 2D QR matrices strictly for:
  1. The member's smartphone PWA pass (`/app`)
  2. The optical camera turnstile verifier (`/scan`)
  3. The print voucher slip during live admission generation (`/admission`)

---

## 5. Screen Layout & Navigation Matrix

### 5.1 Global Public Header (`PublicHeader`)
* **Height & Backdrop:** Sticky header with `bg-black/95 backdrop-blur-2xl border-b border-zinc-900`.
* **Static Public Links:** `Home`, `About`, `Programs`, `Coaching`, `Facilities`, `Memberships`, `Admission`, `Contact`.
* **Zero Privilege Leakage:** No Admin Director button in the public navbar; strictly public links and auth actions (`Log In`, `Sign Up`, `Profile`).

### 5.2 Mobile Bottom Navigation Bar (PWA Shell &bull; `/app`)
* **Fixed Safe-Area Bar:** `fixed bottom-0 inset-x-0 z-50 bg-[#070709]/95 backdrop-blur-xl border-t border-zinc-800/80 px-4 py-2 safe-bottom`.
* **Active State:** Crimson red icon (`text-brand-red`) with glowing dot indicator.

---

## 6. Zero Contradiction Cross-Reference Checklist

- [x] **Primary Brand Accent:** Always Athletic Red (`#ff2a2a`), never cyan, teal, or purple.
- [x] **Background Palette:** Pure Obsidian Black (`#000000`) and Dark Zinc (`#070709`, `#09090b`, `#18181b`).
- [x] **Casing:** 100% Capital Case / Title Case across all public headlines, cards, and buttons.
- [x] **Currency Display:** Always format in Pakistani Rupee (PKR): `₨ 8,500`, `₨ 22,500`, `₨ 42,000`.
- [x] **Database & Stack:** Next.js 14 App Router, Neon Serverless PostgreSQL, Drizzle ORM, and Tailwind CSS.
