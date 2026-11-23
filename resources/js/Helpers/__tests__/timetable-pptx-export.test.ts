import { afterEach, describe, expect, it, vi } from 'vitest';
import { loadImageAsDataUrl, SlideLike, writeLayers } from '@/Helpers/timetable-pptx-export';
import { Layer } from '@/Helpers/timetable-layer-collector';

function fakeSlide() {
  const calls: Array<{ method: string; args: unknown[] }> = [];
  const record =
    (method: string) =>
    (...args: unknown[]) => {
      calls.push({ method, args });
      return slide;
    };
  const slide = {
    addImage: record('addImage'),
    addShape: record('addShape'),
    addText: record('addText')
  } as unknown as SlideLike;
  return { slide, calls };
}

const box = { x: 0, y: 0, w: 800, h: 800 };
const text: Layer = {
  kind: 'text',
  box: { x: 96, y: 0, w: 96, h: 20 },
  text: 'Yoga',
  fontFace: 'Figtree',
  fontSizePx: 16,
  bold: true,
  color: 'FFFFFF',
  transparency: 0,
  align: 'left',
  lineHeightPx: null,
  letterSpacingPx: 0,
  rotate: 0
};
const shape: Layer = { kind: 'shape', box: { x: 0, y: 0, w: 96, h: 48 }, fill: 'F7C88F', transparency: 0, radius: 12, rotate: 0 };
const image: Layer = { kind: 'image', box, url: '/img/soft-1.png' };

describe('writeLayers', () => {
  afterEach(() => vi.unstubAllGlobals());

  it('writes layers in order', async () => {
    const { slide, calls } = fakeSlide();
    const loader = async () => ({ data: 'data:image/png;base64,AAA', w: 1600, h: 900 });
    await writeLayers(slide, [image, shape, text], loader);
    expect(calls.map((c) => c.method)).toEqual(['addImage', 'addShape', 'addText']);
    expect(calls[0].args[0]).toMatchObject({ w: 1600 / 96, h: 900 / 96, sizing: { type: 'cover', w: 800 / 96, h: 800 / 96 } });
  });

  it('skips background when the image fails to load', async () => {
    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('CORS')));
    const { slide, calls } = fakeSlide();
    await expect(writeLayers(slide, [image, text])).resolves.toBeUndefined();
    expect(calls.map((c) => c.method)).toEqual(['addText']);
  });

  it('converts units for text', async () => {
    const { slide, calls } = fakeSlide();
    await writeLayers(slide, [text]);
    expect(calls[0].args[0]).toBe('Yoga');
    expect(calls[0].args[1]).toMatchObject({ x: 1, fontSize: 12, fontFace: 'Figtree', bold: true, margin: 0 });
  });

  it('uses roundRect for rounded shapes', async () => {
    const { slide, calls } = fakeSlide();
    await writeLayers(slide, [shape]);
    expect(calls[0].args[0]).toBe('roundRect');
    expect(calls[0].args[1]).toMatchObject({ rectRadius: 0.125, fill: { color: 'F7C88F', transparency: 0 } });
  });
});

describe('loadImageAsDataUrl', () => {
  afterEach(() => vi.unstubAllGlobals());

  it('returns null on a failed response', async () => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(new Response('', { status: 404 })));
    expect(await loadImageAsDataUrl('/missing.png')).toBeNull();
  });
});
