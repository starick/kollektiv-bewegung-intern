import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { collectTimetableLayers, Layer, TextLayer } from '@/Helpers/timetable-layer-collector';

type Rect = { x: number; y: number; w: number; h: number };
const rects = new WeakMap<Element, Rect>();

function domRect({ x, y, w, h }: Rect): DOMRect {
  return { x, y, width: w, height: h, left: x, top: y, right: x + w, bottom: y + h, toJSON() {} } as DOMRect;
}

function rectOf(el: Element | null): Rect {
  while (el) {
    const r = rects.get(el);
    if (r) return r;
    el = el.parentElement;
  }
  return { x: 0, y: 0, w: 0, h: 0 };
}

function mount(html: string, layout: Record<string, Rect> = {}): HTMLElement {
  document.body.innerHTML = `<div id="root">${html}</div>`;
  const root = document.getElementById('root')!;
  rects.set(root, { x: 10, y: 20, w: 800, h: 800 });
  for (const [selector, r] of Object.entries(layout)) {
    document.querySelectorAll(selector).forEach((el) => rects.set(el, r));
  }
  return root;
}

const texts = (layers: Layer[]) => layers.filter((l): l is TextLayer => l.kind === 'text');

describe('collectTimetableLayers', () => {
  const origEl = Element.prototype.getBoundingClientRect;
  const origRange = (Range.prototype as any).getBoundingClientRect;

  beforeEach(() => {
    Element.prototype.getBoundingClientRect = function () {
      return domRect(rects.get(this) ?? rectOf(this));
    };
    (Range.prototype as any).getBoundingClientRect = function (this: Range) {
      return domRect(rectOf(this.startContainer.parentElement));
    };
  });

  afterEach(() => {
    Element.prototype.getBoundingClientRect = origEl;
    (Range.prototype as any).getBoundingClientRect = origRange;
  });

  it('emits background image first, relative to the root', () => {
    const root = mount('<h1>KOLLEKTIV:BEWEGUNG</h1>');
    root.style.backgroundImage = 'url("/img/soft-1.png")';
    const layers = collectTimetableLayers(root);
    expect(layers[0]).toMatchObject({ kind: 'image', box: { x: 0, y: 0, w: 800, h: 800 } });
    expect((layers[0] as { url: string }).url).toMatch(/\/img\/soft-1\.png$/);
  });

  it('emits shape before its text', () => {
    const root = mount(
      '<div id="b" style="background-color: rgba(255, 0, 0, 0.5); border-top-left-radius: 12px"><span>Yoga</span></div>',
      { '#b': { x: 110, y: 120, w: 100, h: 40 } }
    );
    const layers = collectTimetableLayers(root);
    expect(layers.map((l) => l.kind)).toEqual(['shape', 'text']);
    expect(layers[0]).toMatchObject({ fill: 'FF0000', transparency: 50, radius: 12, rotate: 0, box: { x: 100, y: 100, w: 100, h: 40 } });
    expect(layers[1]).toMatchObject({ text: 'Yoga' });
  });

  it('multiplies ancestor opacity', () => {
    const root = mount('<div style="opacity: 0.5"><div style="opacity: 0.8"><p style="color: rgb(0, 0, 0)">Hi</p></div></div>');
    expect(texts(collectTimetableLayers(root))[0].transparency).toBe(60);
  });

  it('applies uppercase text-transform', () => {
    const root = mount('<h2 style="text-transform: uppercase">mo</h2>');
    expect(texts(collectTimetableLayers(root))[0].text).toBe('MO');
  });

  it('exports a rotated sticker as one shape and one text box', () => {
    const root = mount(
      '<div id="s" style="transform: rotate(30deg); background-color: #ffffff"><p>Bitte vorher bei den</p><p>Kursleitenden anmelden</p></div>',
      { '#s': { x: 600, y: 40, w: 200, h: 60 } }
    );
    const layers = collectTimetableLayers(root);
    expect(layers.map((l) => l.kind)).toEqual(['shape', 'text']);
    expect(layers[0]).toMatchObject({ rotate: 30 });
    expect(layers[1]).toMatchObject({ rotate: 30, text: 'Bitte vorher bei den\nKursleitenden anmelden' });
  });

  it('skips whitespace-only text and hidden elements', () => {
    const root = mount('<div>   </div><p style="display: none">hidden</p><div style="visibility: hidden">invisible</div><p>shown</p>');
    expect(texts(collectTimetableLayers(root)).map((t) => t.text)).toEqual(['shown']);
  });

  it('extracts font face and bold', () => {
    const root = mount('<p style="font-family: &quot;Open Sans&quot;, sans-serif; font-weight: 700">x</p>');
    expect(texts(collectTimetableLayers(root))[0]).toMatchObject({ fontFace: 'Open Sans', bold: true });
  });

  it('emits a line for top borders', () => {
    const root = mount('<div style="border-top: 1px solid rgba(255, 255, 255, 0.3)">row</div>');
    const line = collectTimetableLayers(root).find((l) => l.kind === 'line');
    expect(line).toMatchObject({ color: 'FFFFFF', transparency: 70, widthPx: 1 });
  });

  it('still yields header and footer without courses', () => {
    const root = mount('<h1>KOLLEKTIV:BEWEGUNG</h1><div><p>Alle Angebote sind nach Pay-What-You-Can-Prinzip.</p></div>');
    expect(texts(collectTimetableLayers(root))).toHaveLength(2);
  });
});
