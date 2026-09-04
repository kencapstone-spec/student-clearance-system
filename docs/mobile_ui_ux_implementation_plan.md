# Mobile UI/UX Thumbbar & Navigation Redesign

Implement an ergonomic, mobile-first bottom navigation bar (thumbbar) featuring an elevated, centered focal button for the most important page (Dashboard / Approvals), capped button distribution (maximum 5 items), active dot indicators, a slide-up "More" action sheet for secondary pages, and a top-header avatar badge matching the user's design reference.

---

## User Review & Button Mappings

### Proposed Button Mappings by Role

To maintain ergonomics and avoid clutter on mobile screens (< 400px wide), the bottom bar is limited to an odd number (3 or 5 items) so the primary action is mathematically centered:

1. **Admin (5 items)**:
   - `[👥 Users]` `[📋 Clearances]` • **`[🏠 Dashboard]` (Centered & Elevated)** • `[📊 Reports]` `[⋯ More]`
   - *Secondary pages inside `More ⋯` sheet*: Course Modules, Office Prerequisites, System Settings, Account & Log Out.

2. **Student (5 items)**:
   - `[✅ Status]` `[🔔 Alerts]` • **`[🏠 Dashboard]` (Centered & Elevated)** • `[🧾 Receipt]` `[⋯ More]`
   - *Secondary actions inside `More ⋯` sheet*: Student Info (ID, Course), Clearance Verification, Log Out.

3. **Staff (3 items - Symmetrical)**:
   - `[🔔 Alerts]` • **`[📋 Pending Requests]` (Centered & Elevated)** • `[⋯ More]`
   - *Inside `More ⋯` sheet*: Office assignments, Account Info, Log Out.

4. **President (3 items - Symmetrical)**:
   - `[🔔 Alerts]` • **`[🛡️ Final Approvals]` (Centered & Elevated)** • `[⋯ More]`
   - *Inside `More ⋯` sheet*: Account Info, Log Out.

---

## Proposed Changes

### Navigation & Layout Components

#### 1. [NEW] resources/js/components/MobileBottomNav.vue
- **Fixed Position**: Docked at `bottom-0 inset-x-0` with `z-30` and `backdrop-blur-md` background (`bg-white/95`).
- **Elevated Center Button**:
  - Floats above the bar (`-mt-6`) with `rounded-2xl` squircle container.
  - Cutout effect with `ring-4 ring-white` and elevated shadow (`shadow-xl shadow-slate-900/15`).
  - Active state: Rich gradient (`bg-gradient-to-tr from-blue-900 via-blue-800 to-indigo-600`), bright icon with drop shadow, and bold label below.
  - Inactive state: Soft subtle container tint, matching slate border and icon.
- **Active Dot Indicator**:
  - Regular active buttons display a colored dot indicator directly underneath the icon.
- **"More ⋯" Bottom Sheet**:
  - Triggered by the ⋯ button.
  - Displays remaining role-specific navigation links, account details, and quick logout.
- **Safe Area Inset**:
  - Includes `pb-safe` / `env(safe-area-inset-bottom)` padding to respect iOS Home Indicator and Android gesture bars.

---

#### 2. [MODIFY] resources/js/layouts/app/AppSidebarLayout.vue
- Mount `<MobileBottomNav />` inside the layout.
- Add bottom padding (`pb-24 md:pb-0`) to `<AppContent>` so page content, tables, and buttons are never obstructed by the fixed thumbbar on mobile.

---

#### 3. [MODIFY] resources/js/components/AppSidebarHeader.vue
- Add user initial avatar circle (e.g. bold initial like "K" or initials on deep blue/indigo background) in the top-right header for mobile viewports (`md:hidden`).
- Tapping the avatar badge opens the Account & Log Out menu or sheet.

---

## Verification Plan

### Automated / Build Verification
- Compile Vue/TypeScript assets via `npm run build` to guarantee 0 syntax or type errors.

### Manual / Visual Verification
- Test across mobile viewport widths (360px, 375px, 414px):
  - Verify centered elevated squircle button alignment.
  - Verify active dot indicator underneath active icons.
  - Verify `More ⋯` sheet opens smoothly with secondary links.
  - Verify top header avatar opens account actions.
  - Verify page content has sufficient bottom clearance.
