# HLStatsX Windmill Theme — Style Guide

## Overview

The theme has two CSS layers:
- **`web/assets/css/windmill.css`** — base styles, dark mode overrides, legacy class fixes
- **`web/styles/windmill-*.css`** — per-theme colour overrides (purple, dark-red, custom)

Tailwind CSS is **compiled and purged** — only classes used in the original codebase exist in `tailwind.output.css`. New Tailwind utility classes added to PHP/HTML files will have no effect unless also present in the compiled output. Use inline `style=""` attributes or add rules to `windmill.css` instead.

---

## Colour Reference

### Light Mode
| Element | Class / Value |
|---|---|
| Page background | `bg-gray-900` (sidebar) / white (main) |
| Table header bg | `#F9FAFB` (`bg-gray-50`) |
| Table body bg | `#F9FAFB` (`bg-gray-50`) |
| Table footer bg | `#F9FAFB` (`bg-gray-50`) |
| Table header text | `#6B7280` (gray-500) |
| Row divider | Tailwind `divide-y` → `rgb(229, 231, 235)` |

### Dark Mode
| Element | Class / Value |
|---|---|
| Page background | `#1A1C23` |
| Table header bg | `#1A1C23` |
| Table body bg | `#1A1C23` |
| Table footer bg | `#1A1C23` (from `dark:bg-gray-800` in this build) |
| Table header text | `#9CA3AF` (gray-400) |
| Row divider | Tailwind `dark:divide-gray-700` → `rgb(36, 38, 45)` |
| Header/footer border | `#374151` (gray-700) |

> **Note:** `dark:bg-gray-800` in this Tailwind build computes to `#1A1C23`, not the standard Tailwind `#1F2937`. Always sample computed values from the browser rather than assuming standard Tailwind values.

---

## Table Structure

There are two distinct table patterns in the codebase. They must be kept visually consistent.

### Pattern A — `class_table.php` (Rankings, Players, Clans, etc.)

```html
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
  <div class="w-full overflow-x-auto">
    <table class="data-table w-full whitespace-no-wrap">
      <thead>
        <tr class="data-table-head text-xs font-semibold tracking-wide text-left
                   text-gray-500 uppercase bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
          <td>Column</td>
        </tr>
      </thead>
      <tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
        <tr class="text-xs text-gray-700 dark:text-gray-400">
          <td class="bg1">value</td>  <!-- bg1/bg2 overridden by windmill.css -->
        </tr>
      </tbody>
    </table>
  </div>
  <div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50
              dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>
```

**Key points:**
- Table has class `data-table` — windmill.css targets this
- `bg1`/`bg2` on tds are overridden by `.data-table tbody td { background-color: #F9FAFB }`
- Row separation comes from `divide-y` on `<tbody>` — do NOT add `border-top` to tds
- `data-table-head` on thead tr — windmill.css styles background and text colour

### Pattern B — Custom tables (Game Servers, Voice Servers, Award Winners)

```html
<div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
  <div class="w-full overflow-x-auto">
    <table class="w-full whitespace-no-wrap">
      <tr class="text-xs font-semibold tracking-wide text-left text-gray-500
                 uppercase border-b dark:border-gray-700 bg-gray-50
                 dark:text-gray-400 dark:bg-gray-800">
        <td>Column</td>
      </tr>
      <tbody class="bg-gray-50 divide-y dark:divide-gray-700 dark:bg-gray-800">
        <tr class="text-sm font-semibold text-gray-700 dark:text-gray-400">
          <td>value</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="rounded-b-lg border-t dark:border-gray-700 bg-gray-50
              dark:text-gray-400 dark:bg-gray-800">&nbsp;</div>
</div>
```

**Key points:**
- No `data-table` class — windmill.css bg/border overrides do not apply
- Header is a `<tr>` not `<thead>` — `border-b dark:border-gray-700` provides header separator
- Row separation from `divide-y` on `<tbody>`
- td padding: browser default (`1px`)

### Keeping Both Patterns Consistent

| Property | Pattern A | Pattern B |
|---|---|---|
| td padding | `1px` (windmill.css) | `1px` (browser default) |
| Header separator | None (blends into body) | `border-b dark:border-gray-700` on tr |
| Row dividers | `divide-y dark:divide-gray-700` | `divide-y dark:divide-gray-700` |
| Body bg | `bg-gray-50 dark:bg-gray-800` | `bg-gray-50 dark:bg-gray-800` |

> **Critical:** Do not add explicit `border-top` or `border-bottom` to `td` elements. It creates double borders with `divide-y` producing bright visible lines between rows.

---

## windmill.css Rules — Purpose and Caution

```css
/* Overrides bg1/bg2 per-column alternation from class_table.php */
.data-table tbody td { background-color: #F9FAFB; }
.dark .data-table tbody td { background-color: #1A1C23; }

/* Header styling — no border-bottom (header blends into body) */
.data-table-head { background-color: #F9FAFB; color: #6B7280; }
.dark .data-table-head { background-color: #1A1C23; color: #9CA3AF; }

/* Dark scrollbar theming */
.dark { color-scheme: dark; }
```

**What NOT to add to windmill.css:**
- `border-top` or `border-bottom` on `td` — use `divide-y` on tbody instead
- `border-bottom` on `.data-table-head` — creates double border with Tailwind `border-b`
- `padding` values larger than `1px` on `td` — creates row height mismatch between table patterns

---

## Dark Mode

Dark mode is controlled by the `dark` class on `<html>`, toggled by Alpine.js via `init-alpine.js` and `localStorage`. 

- Main site pages: Alpine.js loaded, dark mode toggle available
- Ingame pages (`ingame.php`): Always light mode — no Alpine.js, no dark class

When adding new elements, always provide both light and dark variants:
```css
.my-element { background-color: #F9FAFB; color: #374151; }
.dark .my-element { background-color: #1A1C23; color: #9CA3AF; }
```

---

## Theme Colour Files (`web/styles/windmill-*.css`)

Each theme file overrides only these selectors:
```css
.windmill-title-bar { }        /* Page title bar */
.windmill-button, .btn { }     /* Buttons — always grouped, never duplicate */
.windmill-button:hover { }
.windmill-text-link { }        /* In-table links e.g. (Join), (View) */
```

> `.btn` and `.windmill-button` must always be grouped with a comma selector. Never define them separately — it creates duplicate declarations requiring double maintenance.

---

## Key Pitfalls from Development

1. **Compiled Tailwind** — `gap-2`, `w-36`, `whitespace-no-wrap`, and many other utilities don't exist in the compiled CSS. Use inline `style=""` or `windmill.css` rules. When in doubt, check `tailwind.output.css` before using a class.

   **Mobile table scrolling** — all tables use `whitespace-no-wrap` on the `<table>` element, but this class does not exist in compiled Tailwind. Replace it with `style="white-space:nowrap;"` on the `<table>` element. Without this, cells wrap their text on mobile, the table never exceeds the container width, and `overflow-x-auto` has no effect — no horizontal scroll appears. Correct pattern:
   ```html
   <table class="w-full" style="white-space:nowrap;">
   <!-- or for class_table.php: -->
   <table class="data-table w-full" style="white-space:nowrap;">
   ```

2. **Double borders** — `divide-y` on tbody + `border-top` on td = two visible lines between rows. Never add border-top to tds in these tables.

3. **Dark mode colour sampling** — `dark:bg-gray-800` in this build ≠ standard Tailwind `#1F2937`. Always use browser DevTools to sample computed colours rather than assuming Tailwind defaults.

4. **`display_page_subtitle()`** — this function already echoes. Never call it with `echo display_page_subtitle(...)`.

5. **`windmill_button_class()`** — returns a string, does not echo. Use `echo windmill_button_class()`.

6. **`display_menu_item()` and `display_menu_item_games()`** — these prepend `$g_options['scripturl']` to relative links. Pass full URLs (starting with `http` or `//`) for external links — the function detects and skips prepending.

7. **`url_get_contents()`** — defined in `inc_windmill_functions.php`. Required for Discord API calls. Do not remove.

8. **Ingame pages** — always light mode. Do not add Alpine.js or `init-alpine.js` to `ingame/header.php`.

9. **Steam group `include` in loop** — `inc_steamgroup_class.php` uses `function_exists()` guard on `downloadXml()` to allow safe inclusion in a foreach loop. Do not revert this.

10. **`theme_version.txt` path** — use `PAGE_PATH . '/../assets/theme_version.txt'`, not a relative path. Ingame pages run from a different directory.