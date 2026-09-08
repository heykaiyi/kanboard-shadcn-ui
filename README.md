# Shadcn — a shadcn/ui theme for Kanboard

Restyles the whole Kanboard interface with the [shadcn/ui](https://ui.shadcn.com)
design language and swaps the bundled Font Awesome glyphs for
[HugeIcons](https://hugeicons.com). Light and dark, following each user's own
Kanboard theme preference.

**No core file is modified and no template is overridden**, so Kanboard
upgrades cleanly underneath it. Almost all of it is stylesheets; the one
exception is the sidebar, which is markup the plugin renders into a hook
Kanboard already provides.

---

## Install

A Kanboard plugin is a directory under `plugins/`, and the directory name is
the plugin's namespace — so this one has to land at `plugins/Shadcn`,
whichever way it gets there.

**From git** — the version that is easy to update:

```
cd /path/to/kanboard/plugins
git clone https://github.com/kaiyichen0421/kanboard-shadcn-ui.git Shadcn
```

`git -C plugins/Shadcn pull` later takes the next version.

**From a zip** — for a host with no shell:

```
unzip Shadcn-x.y.z.zip -d /path/to/kanboard/plugins
```

The archive already contains the `Shadcn/` directory, so it unpacks straight
into `plugins/`. The same file works in **Settings → Plugins → Install from
URL** on an instance with `PLUGIN_INSTALLER` enabled: Kanboard downloads the
archive and extracts it into `plugins/` exactly like the command above.

Then reload the page — Kanboard picks the plugin up on the next request, with
no cache to clear and no migration to run. Each user's existing **Settings →
My profile → Theme** choice (Light / Dark / Auto) drives the palette, and an
untouched install looks exactly as it ships.

To make it your own, go to **Settings → Appearance** (`/settings/brand`) —
see below.

To uninstall, delete the directory. Nothing outside it is touched: the
branding lives in Kanboard's own `settings` table and the two uploads in
`data/files/shadcn/`, so removing the plugin leaves them, and putting it back
finds them again.

### Requirements

Kanboard 1.2.0 or later, and a browser from 2023 or later — `oklch()`,
`color-mix()` and `:has()` are used throughout. On an older browser the
sidebar degrades to a plain block above the page, which is the stock
stacked layout and still completely usable.

---

## How it works

Everything hangs off two facts about Kanboard.

**1. Kanboard already themes through CSS custom properties.** `assets/css/src/themes/*.css`
defines ~90 variables (`--button-primary-background-color`, `--dropdown-border-color`, …)
that the other ~60 core stylesheets consume. `Assets/css/tokens.css` declares the
shadcn token set and then *redefines every Kanboard variable in terms of it*:

```css
--primary: oklch(0.205 0 0);          /* shadcn */
--button-primary-background-color: var(--primary);   /* bridge */
```

One bridge file re-skins board, analytics, gantt, calendar and every other
screen this plugin never mentions by name.

**2. Plugin stylesheets load last.** `app/Template/layout.php` renders the
`template:layout:css` hook after vendor CSS, after the theme CSS and after the
instance stylesheet, so equal-specificity rules win without `!important`:

```
vendor.min.css → light|dark|auto.min.css → customCss() → [tokens, components, icons] → [theme-dark|auto]
```

The dark tokens are the one thing that cannot be a static file — the choice is
per user — so `Template/layout/head.php` picks them on the
`template:layout:head` hook.

**3. `template:layout:top` renders inside `<body>`, above everything else.**
That is where the sidebar goes, so it becomes a sibling of Kanboard's own
`<header>` and `<section class="page">` — added, never overridden.

### A sidebar out of a header

Kanboard has no global navigation to restyle. What it has is a header:

```html
<header>
  <div class="title-container">…            the page title
  <div class="board-selector-container">…   jump to another project
  <div class="menus-container">…            notifications, new, user
```

`sidebar.css` makes `<body>` a grid and then sets `header { display: contents }`,
which drops the `<header>` box and promotes those three divs into that grid.
Each can then be placed anywhere on the page — the user menu into the sidebar
footer, the rest into a top bar — while keeping its own markup, its own
JavaScript and its own extension hooks. Nothing is duplicated and nothing is
reimplemented.

The nav itself is the part Kanboard cannot supply, so `Template/layout/sidebar.php`
renders it: the dashboard, the current project's views (only the ones that are
actually installed and permitted), and the user's project list. `Helper/SidebarHelper.php`
resolves the current project and the active route, because `template:layout:top`
is rendered without any parameters.

`Assets/js/sidebar.js` is the only script the theme ships. It collapses the
sidebar to a 3rem icon rail, remembers that choice in `localStorage`, turns it
into an over-the-page drawer below 768px, and binds Cmd/Ctrl-B — ignored while
you are typing, so it never steals "bold" from the editor. It sets its state on
`<html>` from `<head>`, before `<body>` is parsed, so a collapsed sidebar never
flashes open on load.

### Icons without touching a template

Kanboard emits `<i class="fa fa-cog">` and paints it from an icon font. Half of
those class names never appear in a template at all; they are strings passed to
`$this->url->icon('cog', …)`. So rather than rewrite templates, `icons.css`
keeps the markup and repaints the box:

```css
.fa-cog::before { content: none }
.fa-cog {
    background-color: currentColor;
    mask-image: url("data:image/svg+xml,…");   /* the HugeIcons path */
}
```

Icons inherit `color` and `font-size`, every Font Awesome modifier keeps working
(`fa-2x`, `fa-fw`, `fa-spin`, `fa-rotate-90`, `fa-inverse`), and markup injected
later by Ajax is styled with no JavaScript at all. Unmapped `fa-*` classes are
left alone and still render the original font, so nothing can go missing.

135 icons are mapped: every one core uses, plus the common Font Awesome 4 names
third-party plugins reach for.

---

## Branding

**Settings → Appearance** (`/settings/brand`), admin only. Eight things, and
deliberately only eight:

| | |
|---|---|
| **Application name** | The sidebar mark's label, the login heading, and the sender name on every email. |
| **Tagline** | The small line under the name in the sidebar. |
| **Accent color** | One hex value, picked with a swatch or typed. Buttons, links, the active sidebar item, focus borders and the email header. |
| **Secondary color** | The plate under the quiet controls: the top bar's search box and bell, and the chips inside a multi-select. |
| **Logo** | PNG, JPG, GIF, WebP or SVG, up to 1 MB. Drawn in the sidebar, on the login screen and at the head of every email. |
| **Lockup** | How much of it is drawn: the mark alone, the mark and the name, or the mark, the name and the tagline. |
| **Mark size** | 50–200% of the size the theme draws the mark at. |
| **Favicon** | The same formats. Leave it empty and the logo is used as the tab icon. |

Everything is optional; an empty field means the bundled default, so
"reset" and "never set" are the same state.

The screen answers before the save does: the swatch and the hex field are two
views of one value, a preview row shows the colour as a button, a badge and a
link — including the black-or-white label the theme will compute for it — and
a chosen image is drawn in its plate before it is uploaded. The lockup and the
mark size have a better preview still: the sidebar standing beside the screen
*is* the preview, and it follows the radio and the slider live. All of that is
`Assets/js/branding.js`, and none of it is required: without JavaScript the
swatch is simply a second input that is not submitted.

### Lockup and size

A mark that already contains its own wordmark does not want the name printed
beside it; an instance whose name is the point does not want a tagline under
it. So the three spans are always rendered and the choice is one stylesheet
fragment that hides what is not wanted — which is why the brand links carry an
`aria-label`, and why the preview costs nothing but a `<style>` element. Email
is the exception: it always carries the name, because a notification from a
picture is not a notification from anyone.

**Mark size** is a multiplier, not a length. Each place draws the mark at its
own base — 2rem in the sidebar, 2.25rem on the login screen — and
`--sc-brand-scale` moves all of them without flattening them to one size. The
sidebar's brand row is a `min-height`, so at 200% the row grows instead of
clipping.

### Why two colours and not forty

The accent is written into `--primary`, `--sidebar-primary` and the badge
fill, and the text on top of it is *computed* — the greater of the two WCAG
contrast ratios against black and white — rather than assumed to be white.
So a pale brand colour produces dark button labels instead of an unreadable
button. The same guard runs on the email body: links only take the accent
when it clears 4.5:1 on white, and keep the default blue when it does not.

The secondary is not a second brand colour competing with the first. It is
`--secondary`, the plate under the controls that are *not* the page's action:
the search box and the bell in the top bar, and the chips inside a
multi-select. Its foreground is computed the same way, and the search box's
label is mixed from that foreground toward the plate rather than fixed to
grey, so it stays legible on a strong colour as well as on the near-white the
theme ships.

The rest of the palette stays derived. A settings screen with a picker for
every token is a way to build an ugly instance, not a branded one.

### Where the images live

In `data/files/shadcn/`, next to every other upload — the plugin folder is
replaced wholesale on upgrade and is not writable on a sane install. That
directory is not reachable over HTTP, so `BrandingController::image` serves
them. It is the one public route the plugin adds, because the login screen
has a mark on it and nobody is signed in there yet.

Every URL carries a hash that changes on each upload. That makes a year of
caching safe, and it is also what makes a replaced logo actually appear in
email: Gmail caches an image by URL and never asks again.

An uploaded SVG is screened for script on the way in and served under
`default-src 'none'` with `nosniff` on the way out.

### Beating Kanboard's own favicon

Core declares `adaptive-favicon.svg` a few lines above the hook this plugin
renders into, and a browser handed both an SVG and a PNG takes the SVG no
matter which came last. So a raster upload is not declared as a PNG — it is
wrapped in a one-element SVG generated by the plugin and declared as that.
`apple-touch-icon` still gets the raw file, since it ignores SVG entirely.

---

## Files

```
Shadcn/
├── Plugin.php                        registers everything below
├── Controller/
│   ├── ConfigController.php          Settings → Appearance
│   └── BrandingController.php        serves the two uploaded images
├── Model/BrandingModel.php           branding values, uploads, colour maths
├── Helper/
│   ├── SidebarHelper.php             current project, project list, active route
│   └── BrandHelper.php               branding, as URLs and custom properties
├── Mail/
│   ├── LayoutClient.php              wraps every outgoing mail in one shell
│   ├── LoggingSmtpTransport.php      logs the messages the server refuses
│   └── SendLogger.php
├── Locale/zh_TW/translations.php     the strings this plugin introduces
├── Template/
│   ├── config/settings.php           the Appearance screen
│   ├── config/sidebar.php            its entry in the settings menu
│   ├── layout/sidebar.php            the sidebar markup
│   ├── layout/head.php               per-user dark tokens, icons, brand colour
│   ├── layout/command.php            the command palette
│   ├── layout/auth_strings.php       strings the auth screens need in CSS
│   ├── auth/header.php               the login heading
│   ├── dashboard/welcome.php         the greeting and four numbers
│   ├── mail/layout.php               the mail shell
│   └── notification/footer.php       one call to action for every mail
├── Tools/generate-icons.mjs          regenerates the two icon assets
└── Assets/
    ├── css/
    │   ├── tokens.css                shadcn tokens + the Kanboard bridge
    │   ├── components.css            the component work
    │   ├── sidebar.css               the page grid and the sidebar shell
    │   ├── icons.css                 generated — 135 HugeIcons masks
    │   ├── theme-dark.css            dark tokens
    │   └── theme-auto.css            dark tokens behind prefers-color-scheme
    ├── js/
    │   ├── sidebar.js                collapse, drawer, Cmd/Ctrl-B
    │   ├── modal.js                  marks the notification sheet
    │   ├── command.js                the command palette
    │   ├── navigation.js             page-load progress, one title format
    │   ├── auth.js                   the auth screens' legal footer
    │   └── branding.js               live previews on Settings → Appearance
    ├── img/                          the bundled mark, at four sizes
    └── icons/hugeicons.svg           generated — the same 135 as a sprite
```

### Re-theming

One colour is a setting (**Settings → Appearance**). A whole palette is a
file: `Assets/css/tokens.css` holds the stock shadcn "neutral" set verbatim.
Paste any theme from [ui.shadcn.com/themes](https://ui.shadcn.com/themes) over
the `:root` block (and the matching dark values into `theme-dark.css` /
`theme-auto.css`) and the whole application follows. Nothing else needs
editing — but note that an accent set in Settings is emitted after these
files and will keep winning over whatever `--primary` they declare.

### Changing an icon

```
cd plugins/Shadcn
npm i @hugeicons/core-free-icons
node Tools/generate-icons.mjs
```

Edit the `MAP` object at the top of the generator; keys are Font Awesome 4
names, values are HugeIcons module names from the package's `dist/esm`
directory.

---

## Component coverage

Every component in the shadcn registry, against the Kanboard surface it maps to.
✅ done · ◐ partial · ➖ deferred to the board/task pass · — no counterpart in Kanboard.

| shadcn | Kanboard surface | |
|---|---|---|
| Accordion | `.accordion-title` / `.accordion-content` | ✅ |
| Alert | `.alert`, `.alert-success/error/info/normal` | ✅ |
| Alert Dialog | confirmation views inside `#modal-box` | ✅ |
| Aspect Ratio | `.thumbnails`, file previews | ➖ |
| Attachment | file upload dropzone | ➖ |
| Avatar | `.avatar`, `.avatar-letter`, `.avatar-20/48` | ✅ |
| Badge | category, priority and the estimate/spent chips | ✅ |
| Breadcrumb | rebuilt from the route, in the top bar | ✅ |
| Bubble | — | — |
| Button | `.btn`, `.btn-blue` (default), `.btn-red` (destructive) | ✅ |
| Button Group | `.form-actions`, `.buttons-header` | ✅ |
| Calendar | the fullcalendar view | ✅ |
| Card | `.panel`, `.form-login`, `.table-list`, `.task-board` | ✅ |
| Carousel | `.slideshow` screenshot viewer | ➖ keeps its own dark chrome |
| Chart | the c3 analytics canvases | ✅ |
| Checkbox | `input[type=checkbox]` — checked and indeterminate | ✅ |
| Collapsible | `.accordion`, `.board-column-collapsed` | ✅ |
| Combobox | `.select-dropdown-input-container` + `#select-dropdown-menu` | ✅ |
| Command | `#suggest-menu` (@mentions, filter suggestions) | ✅ |
| Context Menu | `.dropdown-submenu-open` on table rows | ✅ |
| Data Table | `table.table-striped`, `.table-fixed`, `.table-list`, `.subtasks-table` | ✅ |
| Date Picker | the jQuery UI datepicker | ➖ vendor widget |
| Dialog | `#modal-box` — presented as a Sheet, see below | ✅ |
| Direction | `dir="rtl"` on `<html>` | ◐ inherits; no logical-property pass yet |
| Drawer | the same sheet below 768px, docked to the bottom edge | ✅ |
| Dropdown Menu | `.dropdown`, `ul.dropdown-submenu-open` | ✅ |
| Empty | "There is nothing assigned to you." | ◐ rendered as Alert |
| Field | `label` + input + `.form-help` + `.form-errors` | ✅ |
| Hover Card | `#tooltip-container` | ✅ |
| Input | `input[type=text\|email\|password\|number\|date]` | ✅ |
| Input Group | `.input-addon`, `.input-addon-item` | ✅ |
| Input OTP | the 2FA code field | ◐ plain Input |
| Item | `.table-list-row`, `.sidebar > ul li`, sub-task rows | ✅ |
| Kbd | keyboard shortcut reference | ➖ |
| Label | `label` | ✅ |
| Marker | — | — |
| Menubar | `.page-header ul`, `.menu-inline` | ✅ |
| Message | comment threads, the activity stream | ✅ |
| Message Scroller | — | — |
| Native Select | `select` (chevron drawn inline) | ✅ |
| Navigation Menu | `header .menus-container` | ✅ |
| Pagination | `.pagination` | ✅ |
| Popover | `#tooltip-container` and the menu surfaces | ✅ |
| Progress | the gantt bar's fill | ◐ core has no progress element |
| Questionnaire | — | — |
| Radio Group | `input[type=radio]` — drawn, not tinted | ✅ |
| Resizable | — | — |
| Scroll Area | scrollbar styling, `.board-task-list-compact` | ✅ |
| Select | the bundled select2 widget | ✅ |
| Separator | `hr`, `fieldset`/`legend`, the `.page-header h2` rule | ✅ |
| Sheet | every dialog Kanboard opens, plus the off-canvas sidebar below 768px | ✅ |
| Sidebar | the sidebar-07 shell this plugin ships, plus `.sidebar` | ✅ |
| Skeleton | `#app-loading-icon` | ◐ floating indicator |
| Slider | `input[type=range]` | ➖ |
| Spinner | `.fa-spinner.fa-spin` → HugeIcons `Loading03` | ✅ |
| Switch | no native switch; `toggle-on`/`toggle-off` icons | ◐ icon level only |
| Table | `table`, `th`, `td` | ✅ |
| Tabs | `.views` view switcher | ✅ |
| Textarea | `textarea` | ✅ |
| Toast | `.alert-fade-out` flash message | ✅ |
| Toggle | `.board-swimlane-toggle` | ◐ |
| Toggle Group | `.views` | ✅ |
| Tooltip | `.tooltip`, `#tooltip-container` | ✅ |
| Typography | `h1`–`h4`, `.markdown` | ✅ |

---

## Notes and known gaps

- **Task colours are preserved, not discarded.** Kanboard writes
  `.task-board.color-yellow { background-color: … ; border-color: … }` into an
  inline `<style>`. This theme neutralises only the *background* and the top /
  right / bottom border, leaving the colour as a 3px left accent on a shadcn
  Card. Colour coding still reads at a glance without washing the board.
- **A float bug in `.project-header` is fixed here.** Kanboard floats the
  settings dropdown and the view switcher and never clears them, so anything
  taller than the stock 26px switcher pushes the whole page sideways. The header
  is converted to flex.
- **Round 1 covered the global frame** — layout, header, sidebar, buttons,
  forms, menus, dialogs, tables, tabs, toasts. The board and task-detail
  surfaces got a first pass: card chrome, colour handling, column headers.
- **Round 2 rebuilt the five surfaces that were still stock geometry** —
  comment threads and the activity stream (float + `margin-left: 55px`
  replaced by a grid, so the message is a bubble beside a 32px avatar),
  the sub-task table, the FullCalendar view, the gantt grid and the c3
  analytics charts.
- **Vendor CSS is beaten on specificity, not on load order.** FullCalendar
  and the gantt plugin register their own stylesheets on the same
  `template:layout:css` hook, and Kanboard walks the plugin directory with
  `DirectoryIterator`, whose order is the filesystem's. So every rule that
  replaces one of theirs is written one class deeper than the rule it
  replaces (`.fc.fc-unthemed td`, `#gantt-chart .ganttview-block`) and wins
  no matter which plugin loads last.
- **One `!important` block, in the charts.** c3 writes each series colour as
  an inline `style` attribute, so nothing else can reach it. The six
  `!important` declarations set `stroke` / `fill` from a `--c3-series`
  custom property, which the index rules above them point at `--chart-1`…
  `--chart-5`; the palette still lives in `tokens.css`.
- **Calendar events and gantt bars keep the task colour**, exactly like the
  board. Both come from an inline style, so only the geometry and the label
  are restyled — the label switches from FullCalendar's white to dark ink,
  which is what Kanboard's pastel task palette actually needs.
- **Round 3 added the sidebar** — shadcn's `sidebar-07`, collapsible to icons,
  with the top bar scaled up now that it only carries a breadcrumb and three
  menus. Checkbox and Radio Group stopped being `accent-color` and became real
  drawn controls, the Dialog centres on both axes, and below 768px it docks to
  the bottom edge as a Drawer.
- **The sidebar is fixed, not a grid item.** In flow it was as tall as the
  document, so on any page taller than the window the nav scrolled away and
  the user menu ended up at the bottom of the *page* rather than the bottom of
  the *screen*. Column one of the grid stays behind as an explicit track,
  which is what keeps the content clear of the fixed column.
- **Dialogs are capped at 40rem.** `core/modal.js` hands every "medium" dialog
  1024px, which is far wider than the single-column forms inside them. The cap
  is a `max-width`, so it out-ranks the inline `width` without `!important`.
  Dialogs opened at size `large` are laid out in columns and do use the room,
  so the two width values `modal.js` writes for that size are let through by
  attribute selector; if those numbers ever change, those dialogs just inherit
  the cap, which is the safe direction to fail in.
- **The dialog footer is a real button pair.** `submit-buttons.js` emits
  `[Save] " or " [cancel]` — a button, a text node and a link. The link is
  promoted to an outline Button and moved in front of the action, matching
  shadcn's `DialogFooter`; the stray " or " is collapsed with `font-size: 0`.
  The pair fills the dialog at 3:7, cancel first.
- **A dialog is a single-column form**, so its fields fill it. `form.css` pins
  most of them to 70px / 150px / 300px / 400px, which leaves a ragged right
  edge at any dialog width. A required field keeps room for the bare
  `<span>*</span>` that follows it, so the marker stays on the field's line.
- **The close button sits on the title's row.** `modal.js` renders
  `#modal-header` as a block *above* `#modal-content`, which puts the ✕ on a
  row of its own over the title. Lifting it out of the flow lands it where a
  shadcn `DialogHeader` keeps it.
- **The asterisk belongs to the label.** `FormHelper` emits `<label>`,
  `<input>` and `<span class="form-required">*</span>` as three siblings, so
  the marker lands after the control — and a full-width control pushes it onto
  a line of its own. Where that exact shape is present the label takes the
  asterisk and the stray span is dropped; anywhere it does not hold, the
  original span is left alone.
- **The dialog footer has no `gap`.** The `" or "` between the two controls is
  a bare text node, so the flex container wraps it in an anonymous item at
  order 0 — it takes the first slot *and one gap* with it, which pushed the
  whole row 8px right of the fields. The pair is spaced with a margin instead,
  and every edge now lines up with the fields above it.
- **The rule under a dialog title is the `h2`'s own border**, so the room for
  the close button has to be padding on the `h2`. Putting it on `.page-header`
  instead stopped the rule 2.5rem short of the dialog's edge.
- **Traditional Chinese.** `Locale/zh_TW` covers the strings this plugin
  introduces — the sidebar's own labels, plus `Calendar` and `Gantt`, which
  the sidebar renders itself and which core has no entry for. Everything else
  comes from Kanboard's own `zh_TW`, which is complete and uses Taiwanese
  vocabulary (專案, 儲存, 看板). The Calendar and Gantt plugins ship
  Simplified Chinese only, so their own settings screens need a `zh_TW` file
  of their own — that lives in those plugins, not here.
- **The theme is flat.** Every drop shadow is gone: elevation is carried by
  borders and surface colour alone. The `--shadow-*` scale is kept in
  `tokens.css` but defined as `none`, so restoring depth is a four-line change
  there rather than an edit to every component. Core paints a few shadows
  directly (dropdown, tooltip, suggest menu, drag ghost, modal) and those are
  neutralised by name.
- **Focus is an outline, not a ring.** With box shadows gone the focus
  indicator had to move off `box-shadow`; interactive controls get
  `outline: 2px solid var(--ring)` with a 2px offset. Fields still take no
  focus indicator beyond a border-colour change.
- **Pretty URLs.** Kanboard registers slug routes either way, but only emits
  them when `ENABLE_URL_REWRITE` is on — see `config.php`. `KANBOARD_URL` has
  to be set alongside it, because `UrlHelper::dir()` otherwise guesses the base
  path from `dirname(PHP_SELF)`, which is wrong the moment the path *is* a
  route. This is instance configuration, not part of the plugin.
- **Round 4 covered the entry points.** The login and password-reset screens
  became shadcn's `login-02` — two columns, brand mark, centred form, cover
  panel that disappears below `lg`. Kanboard renders them with `no_layout`, so
  `<body>` holds nothing but `.form-login` and the whole layout hangs off it;
  the cover panel is `body::after`, which needs no markup. The heading comes
  from a template on `template:auth:login-form:before` rather than CSS
  `content()`, so it goes through `t()`.
- **Real breadcrumbs.** Kanboard puts one pre-joined string in `<h1>`
  ("Project > Swimlane > Column"), which no stylesheet can split. The trail is
  rebuilt from the route in `SidebarHelper::getBreadcrumb()` and the original
  title steps out of the grid.
- **Every dialog is a Sheet.** Kanboard renders all of its dynamic screens
  into one `#modal-box`; the theme docks that box to the right edge, full
  height, with its own scroll — the title held at the head and the form's
  actions held at the foot, so a long form scrolls under both instead of
  burying Save below the fold. Below 768px the same box takes the bottom
  edge as a Drawer. The notification panel is the one sheet that differs, and
  only inside: it is a list to skim rather than a form to fill in, so the list
  takes the scroll and "mark all as read" moves to the foot. Every modal
  shares that one `#modal-box` with nothing to tell them apart, so
  `Assets/js/modal.js` marks `<html>` when the link that opened it was the
  notification one — matched on the `.notification` wrapper rather than the
  href, which carries no controller name once URL rewriting is on.
- **The scrim is 80% black in both palettes.** A sheet takes the page out of
  play; a 10% wash did not say so.
- **Nothing takes a focus ring.** Kanboard puts `autofocus` on the first
  input of most dialogs, so an offset halo fired the moment a dialog opened,
  before anyone had touched anything. What marks focus is the element's own
  border, in the accent colour — `--ring` is defined as `var(--primary)`, so
  a branded instance colours its focus states too. A link, having no border
  to colour, takes the accent as an underline instead. (Fields never had a
  *hover* state either: hover and idle compute to the same `shadow-xs`.)
- **Icon-only buttons are the glyph and nothing else** — no plate, no padding,
  and only the colour moves on hover. That covers the dialog's close button,
  the sidebar trigger and the two top-bar menus.
- **Kanboard's view switcher now says the same thing as the sidebar.** Both
  offer Overview / Board / List / Calendar / Gantt. The switcher is left alone
  because it also carries the search box and the filter dropdowns, which have
  no other home.
- **One `!important` outside the charts.** `core/modal.js` measures the viewport
  and writes the dialog width straight onto the element. A sheet is as wide as
  the sheet decides, not as wide as a dialog of that size would have been, so
  the inline value has to lose — and only `width: … !important` can beat it.
- `color-mix()` and `oklch()` are used throughout. Both need a 2023-or-later
  browser (Chrome 111+, Safari 16.4+, Firefox 113+). `:has()` carries more weight
  now: the whole page grid hangs off `body:has(> .sc-sb)`, so on Firefox before
  121 the sidebar renders as a plain block above the page — the stock stacked
  layout, still completely usable. Elsewhere `:has()` is used in three
  places — to mute a completed sub-task row, and to hold a comment's action
  menu and a sub-task's drag handle visible while their dropdown is open —
  but only ever to add an effect, so on Firefox before 121 those rows simply
  keep their normal appearance.

---

## License

MIT — see [LICENSE](LICENSE). Same licence as everything it is built on.

---

## Credits

- [shadcn/ui](https://ui.shadcn.com) — design language and token names (MIT)
- [HugeIcons](https://hugeicons.com) free set, stroke-rounded, via
  `@hugeicons/core-free-icons` (MIT)
- [Kanboard](https://kanboard.org) (MIT)
