# Timetable layered export (PPTX for Canva) — design

## Intent
Add a second export next to "Download Image" so a timetable can keep being designed
in Canva: swap the background, reorder layers, add images, and change fonts. The
current JPEG flattens everything into pixels, so none of that is possible.

## Goals
- One-click download of a `.pptx` file that Canva imports with every visual part as
  its own element.
- Text stays real, editable text with its font family, size, weight, and color.
- Layer order matches the on-screen stacking: background image → overlay → bubbles
  and boxes → text → footer → registration sticker.
- Works for both layouts (`classic` and `bubble`) and follows future design changes
  without extra work.

## Non-goals
- A pixel-perfect match. Small shifts in line height and letter spacing are fine.
- Importing the file back into the app.
- A PDF export.

## Anti-goals
- A second copy of the layout logic, on the server or in the browser, that has to be
  kept in sync with the Vue components.

## Approaches
- **A. PPTX generated from the rendered DOM (chosen).** Walk the live timetable
  element, read positions and computed styles, and emit `pptxgenjs` shapes, text
  boxes, and an image. Canva's PPTX import keeps elements separate and editable.
- **B. PDF with real text.** Canva's PDF import tends to merge or split text runs,
  and the font files would have to be embedded. Kept as a fallback only.
- **C. Server-side generation (PHP).** Duplicates the layout logic. Rejected.

The user was fine with A or B, depending on which is technically feasible. A is the
more reliable of the two.

## Design
- New dependency: `pptxgenjs`, imported dynamically on click so the main bundle stays
  the same size.
- New helper `resources/js/Helpers/timetable-pptx-export.ts`:
  `exportTimetablePptx(root: HTMLElement, filename: string)`.
  - Slide size = the root's size (800×800 px), mapped to inches at 96 px/in.
  - Background: the root's computed `background-image` URL is fetched, converted to
    base64, and added as a full-slide picture (cover-cropped).
  - Walk the root's descendants in DOM order (that is the paint order, because the
    later `z-*` elements are also later in the DOM; sort by z-index where needed).
    - An element with a visible background color → a rectangle or rounded rectangle
      (`rectRadius` from `border-radius`), with transparency from the alpha channel
      and `opacity`.
    - A text node → a text box at the bounding rect of its range, with font family
      (first family in the stack), size in pt, bold, color, alignment, and
      transparency from inherited opacity.
    - A rotated element (the registration sticker) → `rotate` taken from the
      transform matrix and applied to its shape and text.
  - Borders (the thin day separator in the classic layout) → line shapes.
- `TimeTableDesign.vue`: add a menu item and button, "Download for Canva (PPTX)",
  that reuses the same unsaved-changes confirmation as the JPEG download.
- Filename: `timetable_{year}-{week}.pptx`.
- Translation keys added to the German strings, using "du".

## Fonts
The defaults are Figtree and Oswald, and the font picker offers Google Fonts. Canva
has those, so the font names carry over. An unknown font is substituted by Canva.

## Left to the builder
- How exactly to merge text: one box per text node, or merge sibling inline spans in
  one line. Start with one box per element that directly contains text, so editing
  feels natural.
- Edge cases in the background image URL (relative `/img/...` vs. an uploaded URL).
