import type PptxGenJS from 'pptxgenjs';
import { collectTimetableLayers, Layer } from '@/Helpers/timetable-layer-collector';
import { pxToIn, pxToPt } from '@/Helpers/pptx-units';

export type SlideLike = Pick<PptxGenJS.Slide, 'addImage' | 'addShape' | 'addText'>;
export type LoadedImage = { data: string; w: number; h: number };
export type ImageLoader = (url: string) => Promise<LoadedImage | null>;

/** Extra width for text boxes, so slightly different font metrics don't cause new line breaks. */
const TEXT_SLACK_PX = 4;

export async function loadImageAsDataUrl(url: string): Promise<LoadedImage | null> {
  try {
    const response = await fetch(url);
    if (!response.ok) return null;
    const blob = await response.blob();
    const data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result as string);
      reader.onerror = () => reject(reader.error);
      reader.readAsDataURL(blob);
    });
    const { w, h } = await new Promise<{ w: number; h: number }>((resolve, reject) => {
      const img = new Image();
      img.onload = () => resolve({ w: img.naturalWidth, h: img.naturalHeight });
      img.onerror = () => reject(new Error(`Could not decode ${url}`));
      img.src = data;
    });
    return { data, w, h };
  } catch {
    return null;
  }
}

export async function writeLayers(
  slide: SlideLike,
  layers: Layer[],
  loadImage: ImageLoader = loadImageAsDataUrl
): Promise<void> {
  for (const layer of layers) {
    const x = pxToIn(layer.box.x);
    const y = pxToIn(layer.box.y);
    const w = pxToIn(layer.box.w);
    const h = pxToIn(layer.box.h);

    switch (layer.kind) {
      case 'image': {
        const image = await loadImage(layer.url);
        if (!image) break;
        slide.addImage({
          data: image.data,
          x,
          y,
          w: pxToIn(image.w),
          h: pxToIn(image.h),
          sizing: { type: 'cover', w, h }
        });
        break;
      }
      case 'shape': {
        const radius = Math.min(layer.radius, layer.box.w / 2, layer.box.h / 2);
        slide.addShape(radius > 0 ? 'roundRect' : 'rect', {
          x,
          y,
          w,
          h,
          fill: { color: layer.fill, transparency: layer.transparency },
          line: { type: 'none' },
          rectRadius: radius > 0 ? pxToIn(radius) : undefined,
          rotate: layer.rotate || undefined
        });
        break;
      }
      case 'line':
        slide.addShape('line', {
          x,
          y,
          w,
          h: 0,
          line: { color: layer.color, transparency: layer.transparency, width: pxToPt(layer.widthPx) }
        });
        break;
      case 'text':
        slide.addText(layer.text, {
          x,
          y,
          w: pxToIn(layer.box.w + TEXT_SLACK_PX),
          h,
          fontFace: layer.fontFace,
          fontSize: pxToPt(layer.fontSizePx),
          bold: layer.bold,
          color: layer.color,
          transparency: layer.transparency,
          align: layer.align,
          valign: 'top',
          margin: 0,
          wrap: true,
          fit: 'none',
          rotate: layer.rotate || undefined,
          charSpacing: layer.letterSpacingPx ? pxToPt(layer.letterSpacingPx) : undefined,
          lineSpacing: layer.lineHeightPx ? pxToPt(layer.lineHeightPx) : undefined
        });
        break;
    }
  }
}

export async function exportTimetablePptx(root: HTMLElement, filename: string): Promise<void> {
  await document.fonts.ready;
  const { default: PptxGenJS } = await import('pptxgenjs');

  const rect = root.getBoundingClientRect();
  const pptx = new PptxGenJS();
  pptx.defineLayout({ name: 'TIMETABLE', width: pxToIn(rect.width), height: pxToIn(rect.height) });
  pptx.layout = 'TIMETABLE';

  const slide = pptx.addSlide();
  await writeLayers(slide, collectTimetableLayers(root));
  await pptx.writeFile({ fileName: filename });
}
