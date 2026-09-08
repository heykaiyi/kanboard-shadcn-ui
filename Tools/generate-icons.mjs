/**
 * Regenerates Assets/icons/hugeicons.svg and Assets/css/icons.css.
 *
 *   npm i @hugeicons/core-free-icons
 *   node Tools/generate-icons.mjs
 *
 * Run it from the plugin directory. To change an icon, edit MAP below and
 * re-run; to add one, look the name up in the package's dist/esm listing.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const ESM = path.join(process.cwd(), 'node_modules/@hugeicons/core-free-icons/dist/esm');
const OUT = path.resolve(HERE, '../Assets');

// Font Awesome 4 class (as used by Kanboard core templates) -> HugeIcons free icon
const MAP = {
  'address-card-o':      'IdentityCardIcon',
  'archive':             'Archive02Icon',
  'arrow-down':          'ArrowDown02Icon',
  'arrow-right':         'ArrowRight02Icon',
  'arrow-up':            'ArrowUp02Icon',
  'arrows-alt':          'ArrowAllDirectionIcon',
  'arrows-h':            'ArrowLeftRightIcon',
  'ban':                 'UnavailableIcon',
  'bars':                'Menu01Icon',
  'bell-o':              'Notification02Icon',
  'bell-slash-o':        'NotificationOff02Icon',
  'bookmark':            'Bookmark02Icon',
  'calendar':            'Calendar03Icon',
  'calendar-times-o':    'CalendarRemove02Icon',
  'caret-down':          'ArrowDown01Icon',
  'check-circle-o':      'CheckmarkCircle02Icon',
  'check-square-o':      'CheckmarkSquare02Icon',
  'chevron-circle-down': 'CircleArrowDown02Icon',
  'chevron-circle-up':   'CircleArrowUp02Icon',
  'clock-o':             'Clock01Icon',
  'cloud':               'CloudIcon',
  'code-fork':           'GitBranchIcon',
  'cog':                 'Settings02Icon',
  'comments-o':          'Comment01Icon',
  'external-link':       'LinkSquare02Icon',
  'eye':                 'EyeIcon',
  'file-archive-o':      'Zip01Icon',
  'file-audio-o':        'FileAudioIcon',
  'file-code-o':         'FileScriptIcon',
  'file-excel-o':        'Xls01Icon',
  'file-image-o':        'FileImageIcon',
  'file-o':              'File02Icon',
  'file-pdf-o':          'Pdf01Icon',
  'file-powerpoint-o':   'Ppt01Icon',
  'file-text-o':         'File01Icon',
  'file-video-o':        'FileVideoIcon',
  'file-word-o':         'Doc01Icon',
  'filter':              'FilterIcon',
  'flag':                'Flag02Icon',
  'floppy-o':            'FloppyDiskIcon',
  'folder-open':         'FolderOpenIcon',
  'gears':               'Settings01Icon',
  'group':               'UserMultiple02Icon',
  'info-circle':         'InformationCircleIcon',
  'life-ring':           'CustomerSupportIcon',
  'lock':                'LockIcon',
  'minus-square':        'MinusSignSquareIcon',
  'newspaper-o':         'News01Icon',
  'paperclip':           'Attachment01Icon',
  'plus':                'PlusSignIcon',
  'plus-square':         'PlusSignSquareIcon',
  'refresh':             'RefreshIcon',
  'share-alt':           'Share01Icon',
  'shield':              'Shield01Icon',
  'sort':                'ArrowDataTransferVerticalIcon',
  'spinner':             'Loading03Icon',
  'square-o':            'SquareIcon',
  'star':                'StarIcon',
  'star-half-o':         'StarHalfIcon',
  'star-o':              'StarIcon',
  'tasks':               'CheckListIcon',
  'th':                  'DashboardSquare01Icon',
  'trophy':              'ChampionIcon',
  'user':                'UserIcon',
  'users':               'UserMultipleIcon',

  /* Passed as a string to $this->url->icon() / $this->modal->*Icon(),
   * so they never appear as a literal "fa fa-x" in the templates. */
  'bar-chart':           'ChartBarBigIcon',
  'bell':                'Notification02Icon',
  'cloud-download':      'CloudDownloadIcon',
  'compress':            'ArrowShrink02Icon',
  'cubes':               'PuzzleIcon',
  'dashboard':           'DashboardSpeed01Icon',
  'download':            'Download04Icon',
  'expand':              'ArrowExpand02Icon',
  'folder':              'Folder01Icon',
  'hand-o-right':        'HandPointingRight01Icon',
  'home':                'Home01Icon',
  'link':                'Link02Icon',
  'list':                'ListViewIcon',
  'pause':               'PauseIcon',
  'play':                'PlayIcon',
  'play-circle-o':       'PlayCircleIcon',
  'reply':               'ArrowTurnBackwardIcon',
  'rss-square':          'RssIcon',
  'search':              'Search01Icon',
  'sign-out':            'Logout01Icon',
  'sort-alpha-asc':      'ArrowDownAZIcon',
  'sort-alpha-desc':     'ArrowUpAzIcon',
  'sort-amount-asc':     'SortByDown02Icon',
  'sort-amount-desc':    'SortByUp02Icon',
  'sort-numeric-asc':    'SortByDown01Icon',
  'sort-numeric-desc':   'SortByUp01Icon',
  'tachometer':          'DashboardSpeed01Icon',
  'toggle-off':          'ToggleOffIcon',
  'toggle-on':           'ToggleOnIcon',
  'trash-o':             'Delete02Icon',

  /* Not referenced by core today, but standard Font Awesome 4 names that
   * third-party plugins reach for. Mapping them keeps the theme coherent
   * once other plugins are installed. */
  'check':               'Tick02Icon',
  'chevron-down':        'ArrowDown01Icon',
  'chevron-left':        'ArrowLeft01Icon',
  'chevron-right':       'ArrowRight01Icon',
  'chevron-up':          'ArrowUp01Icon',
  'circle':              'CircleIcon',
  'copy':                'Copy01Icon',
  'ellipsis-h':          'MoreHorizontalIcon',
  'ellipsis-v':          'MoreVerticalIcon',
  'envelope-o':          'Mail01Icon',
  'exclamation-triangle':'Alert02Icon',
  'hourglass-half':      'HourglassIcon',
  'key':                 'Key01Icon',
  'magic':               'MagicWand01Icon',
  'pencil':              'Edit02Icon',
  'question-circle':     'HelpCircleIcon',
  'sign-in':             'Login01Icon',
  'table':               'Table01Icon',
  'tag':                 'Tag01Icon',
  'times':               'Cancel01Icon',
  'trash':               'Delete02Icon',
  'upload':              'Upload04Icon',

  /* Also emitted by core and by the official Calendar / Gantt plugins. */
  'align-justify':       'TextAlignJustifyLeftIcon',
  'camera':              'Camera01Icon',
  'clone':               'Copy01Icon',
  'close':               'Cancel01Icon',
  'comment':             'Comment01Icon',
  'database':            'Database01Icon',
  'edit':                'Edit02Icon',
  'file':                'File02Icon',
  'globe':               'GlobeIcon',
  'id-badge':            'IdentityCardIcon',
  'legal':               'JusticeScale01Icon',
  'line-chart':          'ChartLineData01Icon',
  'paper-plane':         'SentIcon',
  'rocket':              'RocketIcon',
  'sliders':             'SlidersHorizontalIcon',
  'smile-o':             'SmileIcon',
  'user-circle-o':       'UserCircleIcon',
  'user-plus':           'UserAdd01Icon',
};

const camelToKebab = (s) => s.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();

function loadIcon(name) {
  const src = fs.readFileSync(path.join(ESM, name + '.js'), 'utf8');
  const body = src.slice(src.indexOf('['), src.lastIndexOf(']') + 1);
  // The module is a plain JS literal array of [tag, attrs] pairs.
  return (0, eval)('(' + body + ')');
}

function toInner(nodes) {
  return nodes.map(([tag, attrs]) => {
    const a = Object.entries(attrs)
      .filter(([k]) => k !== 'key')
      .map(([k, v]) => `${camelToKebab(k)}="${v}"`)
      .join(' ');
    return `<${tag} ${a}/>`;
  }).join('');
}

const SVG_OPEN = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">';

// Data URI that survives a CSS url() without needing base64.
function dataUri(inner) {
  const svg = SVG_OPEN + inner + '</svg>';
  const encoded = svg
    .replace(/"/g, "'")
    .replace(/%/g, '%25')
    .replace(/#/g, '%23')
    .replace(/</g, '%3C')
    .replace(/>/g, '%3E')
    .replace(/\{/g, '%7B')
    .replace(/\}/g, '%7D');
  return `url("data:image/svg+xml,${encoded}")`;
}

const entries = Object.entries(MAP).map(([fa, icon]) => {
  const inner = toInner(loadIcon(icon));
  return { fa, icon, inner };
});

/* ---------- 1. SVG sprite (for templates that want <svg><use>) ---------- */
const sprite = [
  '<?xml version="1.0" encoding="UTF-8"?>',
  '<!-- HugeIcons (free, MIT) stroke-rounded subset used by the Shadcn theme for Kanboard. -->',
  '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">',
  ...entries.map(e =>
    `  <symbol id="hi-${e.fa}" viewBox="0 0 24 24" fill="none">${e.inner}</symbol>`),
  '</svg>',
  '',
].join('\n');
fs.writeFileSync(path.join(OUT, 'icons/hugeicons.svg'), sprite);

/* ---------- 2. CSS masks that swap the Font Awesome glyphs in place ------ */
const chunk = (list, size = 4) => {
  const out = [];
  for (let i = 0; i < list.length; i += size) out.push(list.slice(i, i + size).join(', '));
  return out.join(',\n');
};
const chunked = chunk(entries.map(e => `.fa-${e.fa}`));
const chunkedBefore = chunk(entries.map(e => `.fa-${e.fa}::before`), 3);

const css = `/*!
 * Shadcn theme for Kanboard — icons
 *
 * Replaces the bundled Font Awesome 4 glyphs with HugeIcons (free set, MIT,
 * stroke-rounded, 24x24) without touching a single core template.
 *
 * How it works: every mapped .fa-* class keeps its markup (<i class="fa fa-cog">)
 * but the ::before glyph is dropped and the box is painted with currentColor
 * masked by the HugeIcons path. Icons therefore inherit color, font-size and
 * every fa modifier (fa-2x, fa-spin, fa-rotate-90, fa-inverse) for free, and
 * markup injected later by Ajax is styled automatically.
 *
 * Unmapped .fa-* classes are left alone and keep rendering the original font.
 *
 * Generated by Tools/generate-icons.mjs — edit the MAP there, not this file.
 */

${chunked} {
    --hi-icon: none;
    display: inline-block;
    width: 1em;
    height: 1em;
    vertical-align: -0.135em;
    background-color: currentColor;
    -webkit-mask-image: var(--hi-icon);
    mask-image: var(--hi-icon);
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
    -webkit-mask-size: contain;
    mask-size: contain;
}

${chunkedBefore} {
    content: none;
}

/* Font Awesome sizing modifiers still apply to the masked box. */
.fa.fa-fw {
    width: 1.28571429em;
}

.fa.fa-lg {
    vertical-align: -0.2em;
}

${entries.map(e => `/* ${e.icon} */\n.fa-${e.fa} {\n    --hi-icon: ${dataUri(e.inner)};\n}`).join('\n\n')}
`;
fs.writeFileSync(path.join(OUT, 'css/icons.css'), css);

console.log(`icons: ${entries.length}`);
console.log(`sprite: ${(sprite.length / 1024).toFixed(1)} KB`);
console.log(`css:    ${(css.length / 1024).toFixed(1)} KB`);
