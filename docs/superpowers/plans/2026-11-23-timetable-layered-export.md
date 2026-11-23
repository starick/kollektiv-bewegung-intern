# Timetable Layered Export (PPTX for Canva) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "Download for Canva (PPTX)" action that exports the rendered timetable as a single slide in which every visual part is its own element and all text is editable.

**Architecture:** A DOM collector walks the live timetable element and returns a flat, paint-ordered list of layers (image / shape / line / text), with geometry relative to the root in px. A writer maps those layers to `pptxgenjs` calls. Small helpers handle unit and color conversion. The layout logic stays in the Vue components; the export only reads the rendered result.

**Tech Stack:** Vue 3 + TypeScript, `pptxgenjs` (loaded with dynamic import), `vitest` + `jsdom` for unit tests (new; the repo has no JS test runner yet).

**Spec:** `docs/superpowers/specs/2026-11-23-timetable-layered-export-design.md`

## Global Constraints

- The JPEG export stays unchanged; the new export sits next to it.
- `pptxgenjs` is loaded only when the user clicks (`await import('pptxgenjs')`).
- Slide size = root size at 96 px/in (800 px → 8.333 in).
- Filename: `timetable_{year}-{week}.pptx`.
- German translations use "du". Key `Download for Canva (PPTX)` → `Für Canva herunterladen (PPTX)`.
- Commit messages: `feat: <one sentence>`, without a Co-Authored-By trailer.
- A pixel-perfect match is not required. Do not duplicate the layout logic.

## Review Focus

1. Background image cannot be fetched (404, CORS) → the export still succeeds, just without the background picture (Task 3 test).
2. Rotated registration sticker → one rotated shape plus one text box containing both lines, not scattered fragments (Task 2 test).
3. `text-transform: uppercase` (day names) → the exported text is uppercase (Task 2 test).
4. Timetable with no courses → the header, date range and footer are still exported (Task 2 test).
5. A font stack with quoted names (`"Open Sans", sans-serif`) → `fontFace` is `Open Sans` (Task 1 test).

---

### Task 1: Unit and color helpers + test runner

**Files:**
- Modify: `package.json` (devDependencies `vitest`, `jsdom`; script `"test": "vitest run"`)
- Create: `vitest.config.ts` (alias `@` → `resources/js`, environment `jsdom`)
- Create: `resources/js/Helpers/pptx-units.ts`
- Test: `resources/js/Helpers/__tests__/pptx-units.test.ts`

**Interfaces:**
- Produces:
  - `PX_PER_IN = 96`
  - `pxToIn(px: number): number`
  - `pxToPt(px: number): number` (px × 0.75)
  - `parseCssColor(css: string): { hex: string; alpha: number } | null`: accepts `rgb(...)`, `rgba(...)`, `#rgb`, `#rrggbb`, `#rrggbbaa`; returns `null` for `transparent` and for alpha 0; `hex` is 6 uppercase chars without `#`
  - `toTransparency(alpha: number, opacity: number): number`: `Math.round((1 - alpha * opacity) * 100)`, clamped to 0–100
  - `rotationFromTransform(transform: string): number`: degrees from `matrix(a,b,c,d,e,f)` = `atan2(b, a)`, rounded to 2 decimals; `none` → 0
  - `firstFontFamily(stack: string): string`: first entry, trimmed, quotes stripped

- [ ] **Step 1: Install the test runner and write the failing tests**

`npm i -D vitest jsdom`. Tests:
```ts
expect(pxToIn(96)).toBe(1);
expect(pxToPt(16)).toBe(12);
expect(parseCssColor('rgba(255, 0, 0, 0.5)')).toEqual({ hex: 'FF0000', alpha: 0.5 });
expect(parseCssColor('rgb(247, 200, 143)')).toEqual({ hex: 'F7C88F', alpha: 1 });
expect(parseCssColor('#f7c88f55')).toEqual({ hex: 'F7C88F', alpha: 0x55 / 255 });
expect(parseCssColor('#fff')).toEqual({ hex: 'FFFFFF', alpha: 1 });
expect(parseCssColor('transparent')).toBeNull();
expect(parseCssColor('rgba(0, 0, 0, 0)')).toBeNull();
expect(toTransparency(0.5, 0.8)).toBe(60);
expect(rotationFromTransform('matrix(0.866025, 0.5, -0.5, 0.866025, 0, 0)')).toBe(30);
expect(rotationFromTransform('none')).toBe(0);
expect(firstFontFamily('"Open Sans", sans-serif')).toBe('Open Sans');
expect(firstFontFamily('Oswald, sans-serif')).toBe('Oswald');
```

- [ ] **Step 2: Run the tests and check that they fail**

Run: `npx vitest run resources/js/Helpers/__tests__/pptx-units.test.ts`
Expected: FAIL (the module cannot be resolved).

- [ ] **Step 3: Implement the helpers in `resources/js/Helpers/pptx-units.ts`**

- [ ] **Step 4: Run the tests and check that they pass**

Run: `npx vitest run`, then `npm run tsc`
Expected: all tests pass; no type errors.

- [ ] **Step 5: Commit**

`git commit -m "feat: add pptx unit and color helpers with vitest setup"`

---

### Task 2: DOM layer collector

**Files:**
- Create: `resources/js/Helpers/timetable-layer-collector.ts`
- Test: `resources/js/Helpers/__tests__/timetable-layer-collector.test.ts`

**Interfaces:**
- Consumes: everything from Task 1.
- Produces:
```ts
type Box = { x: number; y: number; w: number; h: number }; // px, relative to root top-left
type ImageLayer = { kind: 'image'; box: Box; url: string };
type ShapeLayer = { kind: 'shape'; box: Box; fill: string; transparency: number; radius: number; rotate: number };
type LineLayer  = { kind: 'line'; box: Box; color: string; transparency: number; widthPx: number };
type TextLayer  = { kind: 'text'; box: Box; text: string; fontFace: string; fontSizePx: number; bold: boolean;
                    color: string; transparency: number; align: 'left' | 'center' | 'right';
                    lineHeightPx: number | null; letterSpacingPx: number; rotate: number };
export type Layer = ImageLayer | ShapeLayer | LineLayer | TextLayer;
export function collectTimetableLayers(root: HTMLElement): Layer[];
```

Rules (the behavior the tests pin):
- If the root's computed `background-image` is `url(...)`, the first layer is an `ImageLayer` that covers the root box.
- Elements are visited depth-first in DOM order. Each element's layers are emitted before its children's, so the result is in paint order. Elements with `display:none` or `visibility:hidden` are skipped together with their subtrees.
- An element whose `background-color` passes `parseCssColor` → `ShapeLayer` (radius = `parseFloat(border-top-left-radius)`).
- An element with `border-top-width > 0` and a visible border color → `LineLayer` along its top edge.
- Every non-whitespace text node → `TextLayer`. Its box is the rect of a `Range` over that node; its style comes from the parent element's computed style. The text is whitespace-collapsed and upper-cased when `text-transform: uppercase`. `bold` = `font-weight >= 600`.
- Transparency uses the product of `opacity` over the element and all its ancestors up to the root.
- An element with a non-zero rotation (`rotationFromTransform`): emit its `ShapeLayer` (if it has a background) and a single `TextLayer` with `text = el.innerText` (lines kept with `\n`). Both use the unrotated box: `offsetWidth`/`offsetHeight`, centered on the center of its bounding rect, with `rotate` set. Skip its descendants.

In jsdom, stub `getBoundingClientRect` on elements and `Range.prototype.getBoundingClientRect`; `innerText` may need a stub as well.

- [ ] **Step 1: Write the failing tests**

- `emits background image first`: root with `style="background-image:url(/img/soft-1.png)"` → `layers[0]` is `{ kind: 'image', url: '/img/soft-1.png' }`.
- `emits shape before its text`: `<div style="background-color:rgba(255,0,0,0.5);border-radius:12px"><span>Yoga</span></div>` → `[shape{fill:'FF0000',transparency:50,radius:12}, text{text:'Yoga'}]`, in that order.
- `multiplies ancestor opacity`: text inside `opacity:0.5` > `opacity:0.8`, color `rgb(0,0,0)` → `transparency` 60.
- `applies uppercase`: `<h2 style="text-transform:uppercase">mo</h2>` → text `MO`.
- `rotated sticker is one text box`: `<div style="transform:rotate(30deg);background-color:#fff"><p>Bitte vorher bei den</p><p>Kursleitenden anmelden</p></div>` (stub the computed transform as the 30° matrix and `innerText` as the joined lines) → exactly one shape and one text, both `rotate: 30`, text `'Bitte vorher bei den\nKursleitenden anmelden'`.
- `skips whitespace-only text and hidden elements`.
- `extracts font face`: parent `font-family: "Open Sans", sans-serif` → `fontFace: 'Open Sans'`.
- `no courses still yields header`: root containing only `<h1>KOLLEKTIV:BEWEGUNG</h1>` and a footer `<p>` → two text layers.

- [ ] **Step 2: Run the tests and check that they fail**

Run: `npx vitest run resources/js/Helpers/__tests__/timetable-layer-collector.test.ts`
Expected: FAIL (the module is missing).

- [ ] **Step 3: Implement `collectTimetableLayers` in `resources/js/Helpers/timetable-layer-collector.ts`**

- [ ] **Step 4: Run the tests and check that they pass**

Run: `npx vitest run && npm run tsc`
Expected: PASS; no type errors.

- [ ] **Step 5: Commit**

`git commit -m "feat: collect timetable layers from the rendered DOM"`

---

### Task 3: PPTX writer, menu action and translation

**Files:**
- Modify: `package.json` (dependency `pptxgenjs`)
- Create: `resources/js/Helpers/timetable-pptx-export.ts`
- Modify: `resources/js/Pages/TimeTables/Partials/TimeTableDesign.vue` (menu item; reuse the unsaved-changes flow)
- Modify: `lang/de.json`
- Test: `resources/js/Helpers/__tests__/timetable-pptx-export.test.ts`

**Interfaces:**
- Consumes: `collectTimetableLayers`, `Layer`, and the Task 1 helpers.
- Produces:
  - `export async function exportTimetablePptx(root: HTMLElement, filename: string): Promise<void>`
  - `export async function writeLayers(pptx: PptxLike, slide: SlideLike, layers: Layer[], size: { w: number; h: number }): Promise<void>`. This is separate from the export so it can be tested with a fake slide. `SlideLike` is `Pick<PptxGenJS.Slide, 'addImage' | 'addShape' | 'addText'>`.
  - `export async function loadImageAsDataUrl(url: string): Promise<{ data: string; w: number; h: number } | null>`. Returns `null` on any fetch or decode failure.

Mapping (all px → in via `pxToIn`):
- Image → `slide.addImage({ data, x:0, y:0, w, h, sizing: { type: 'cover', w, h } })`, using the natural image size as the base `w`/`h` that pptxgenjs expects. If `loadImageAsDataUrl` returns `null`, skip the layer.
- Shape → `addShape(radius > 0 ? 'roundRect' : 'rect', { x, y, w, h, fill: { color, transparency }, line: { type: 'none' }, rectRadius: pxToIn(radius), rotate })`.
- Line → `addShape('line', { x, y, w, h: 0, line: { color, transparency, width: pxToPt(widthPx) } })`.
- Text → `addText(text, { x, y, w: w + 4px, h, fontFace, fontSize: pxToPt(fontSizePx), bold, color, transparency, align, valign: 'top', margin: 0, wrap: true, fit: 'none', rotate, charSpacing: pxToPt(letterSpacingPx), lineSpacing: lineHeightPx ? pxToPt(lineHeightPx) : undefined })`.
- `exportTimetablePptx`: `await document.fonts.ready`; `const { default: PptxGenJS } = await import('pptxgenjs')`; `defineLayout({ name: 'TIMETABLE', width, height })`; `layout = 'TIMETABLE'`; one slide; `writeLayers`; `writeFile({ fileName })`.

- [ ] **Step 1: Install and write the failing tests**

`npm i pptxgenjs`. Tests with a fake slide that records calls:
- `writes layers in order`: an image, a shape and a text layer → the calls are `addImage`, `addShape`, `addText`, in that order.
- `skips background when image fails`: mock `fetch` to reject → there is no `addImage` call, the other layers are still written, and no error is thrown.
- `converts units`: a text layer at `x: 96`, `fontSizePx: 16` → `x: 1`, `fontSize: 12`.
- `rounded shape uses roundRect`: radius 12 → `'roundRect'` with `rectRadius: 0.125`.

- [ ] **Step 2: Run the tests and check that they fail**

Run: `npx vitest run resources/js/Helpers/__tests__/timetable-pptx-export.test.ts`
Expected: FAIL (the module is missing).

- [ ] **Step 3: Implement `timetable-pptx-export.ts`**

- [ ] **Step 4: Wire up the UI**

In `TimeTableDesign.vue`, generalize `onDownload` to `onDownload(kind: 'jpeg' | 'pptx' = 'jpeg')`, so both downloads go through the same unsaved-changes confirmation. Add a `doDownloadPptx`, which calls `exportTimetablePptx(timetableRef.value, \`timetable_${year}-${week}.pptx\`)` and shows `alert.error(trans('Error exporting file'), e)` if it fails. Add the menu item `{ label: trans('Download for Canva (PPTX)'), icon: 'pi pi-file-export', command: () => onDownload('pptx') }` after "Download Image". The existing emit `@onDownload` keeps the JPEG behavior.

Add to `lang/de.json`: `"Download for Canva (PPTX)": "Für Canva herunterladen (PPTX)"` and `"Error exporting file": "Fehler beim Exportieren der Datei"`.

- [ ] **Step 5: Verify**

Run: `npx vitest run && npm run tsc && npm run build`
Expected: all of them pass.

Manual check: run `npm run dev` and open a timetable's Design tab. Export once with the classic layout and once with the bubble layout, with the overlay and the registration sticker turned on. Open both files in Keynote/PowerPoint (or import them into Canva) and check:
- the background is a separate picture;
- the overlay is a separate semi-transparent rectangle;
- each text is editable and uses Figtree/Oswald;
- the sticker is rotated.

- [ ] **Step 6: Commit**

`git commit -m "feat: export timetables as layered pptx for canva"`
