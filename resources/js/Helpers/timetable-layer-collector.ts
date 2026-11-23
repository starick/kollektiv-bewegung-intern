import { firstFontFamily, parseCssColor, rotationFromTransform, toTransparency } from '@/Helpers/pptx-units';

export type Box = { x: number; y: number; w: number; h: number };
export type ImageLayer = { kind: 'image'; box: Box; url: string };
export type ShapeLayer = {
  kind: 'shape';
  box: Box;
  fill: string;
  transparency: number;
  radius: number;
  rotate: number;
};
export type LineLayer = { kind: 'line'; box: Box; color: string; transparency: number; widthPx: number };
export type TextLayer = {
  kind: 'text';
  box: Box;
  text: string;
  fontFace: string;
  fontSizePx: number;
  bold: boolean;
  color: string;
  transparency: number;
  align: 'left' | 'center' | 'right';
  lineHeightPx: number | null;
  letterSpacingPx: number;
  rotate: number;
};
export type Layer = ImageLayer | ShapeLayer | LineLayer | TextLayer;

const INLINE_DISPLAYS = new Set(['inline', 'inline-block', 'contents', '']);

export function collectTimetableLayers(root: HTMLElement): Layer[] {
  const origin = root.getBoundingClientRect();
  const layers: Layer[] = [];

  const relative = (r: DOMRect): Box => ({
    x: r.left - origin.left,
    y: r.top - origin.top,
    w: r.width,
    h: r.height
  });

  const opacityOf = (el: Element): number => {
    let product = 1;
    for (let cur: Element | null = el; cur; cur = cur.parentElement) {
      const o = parseFloat(getComputedStyle(cur).opacity);
      if (!Number.isNaN(o)) product *= o;
      if (cur === root) break;
    }
    return product;
  };

  const isHidden = (style: CSSStyleDeclaration) =>
    style.display === 'none' || style.visibility === 'hidden';

  const pushOwnLayers = (el: HTMLElement, style: CSSStyleDeclaration, box: Box, rotate: number) => {
    const opacity = opacityOf(el);

    const url = style.backgroundImage?.match(/url\((['"]?)(.*?)\1\)/)?.[2];
    if (url) layers.push({ kind: 'image', box, url });

    const bg = parseCssColor(style.backgroundColor);
    if (bg) {
      layers.push({
        kind: 'shape',
        box,
        fill: bg.hex,
        transparency: toTransparency(bg.alpha, opacity),
        radius: parseFloat(style.borderTopLeftRadius) || 0,
        rotate
      });
    }

    const borderWidth = parseFloat(style.borderTopWidth) || 0;
    const borderColor = parseCssColor(style.borderTopColor);
    if (borderWidth > 0 && style.borderTopStyle !== 'none' && borderColor) {
      layers.push({
        kind: 'line',
        box: { x: box.x, y: box.y, w: box.w, h: 0 },
        color: borderColor.hex,
        transparency: toTransparency(borderColor.alpha, opacity),
        widthPx: borderWidth
      });
    }
  };

  const textLayer = (el: Element, text: string, box: Box, rotate: number): TextLayer | null => {
    const style = getComputedStyle(el);
    let content = text.replace(/\s+/g, ' ').trim();
    if (!content) return null;
    if (style.textTransform === 'uppercase') content = content.toUpperCase();
    if (style.textTransform === 'lowercase') content = content.toLowerCase();

    const color = parseCssColor(style.color) ?? { hex: '000000', alpha: 1 };
    const weight = style.fontWeight === 'bold' ? 700 : parseInt(style.fontWeight, 10) || 400;
    const align = style.textAlign === 'center' ? 'center' : ['right', 'end'].includes(style.textAlign) ? 'right' : 'left';
    const lineHeight = parseFloat(style.lineHeight);

    return {
      kind: 'text',
      box,
      text: content,
      fontFace: firstFontFamily(style.fontFamily || 'Arial'),
      fontSizePx: parseFloat(style.fontSize) || 16,
      bold: weight >= 600,
      color: color.hex,
      transparency: toTransparency(color.alpha, opacityOf(el)),
      align,
      lineHeightPx: Number.isNaN(lineHeight) ? null : lineHeight,
      letterSpacingPx: parseFloat(style.letterSpacing) || 0,
      rotate
    };
  };

  /** Text of a rotated element, one line per block-level descendant. */
  const blockLines = (el: Element): string => {
    const lines: string[] = [];
    let current = '';
    const flush = () => {
      if (current.trim()) lines.push(current.replace(/\s+/g, ' ').trim());
      current = '';
    };
    const walk = (node: Node) => {
      if (node.nodeType === Node.TEXT_NODE) {
        current += node.textContent ?? '';
        return;
      }
      if (!(node instanceof Element)) return;
      const style = getComputedStyle(node);
      if (isHidden(style)) return;
      const block = node !== el && !INLINE_DISPLAYS.has(style.display);
      if (block) flush();
      node.childNodes.forEach(walk);
      if (block) flush();
    };
    walk(el);
    flush();
    return lines.join('\n');
  };

  const visit = (el: HTMLElement) => {
    const style = getComputedStyle(el);
    if (isHidden(style)) return;

    const rotate = rotationFromTransform(style.transform || 'none');
    if (rotate !== 0) {
      // Bounding rects of rotated elements are axis-aligned; rebuild the unrotated box around the center.
      const r = relative(el.getBoundingClientRect());
      const w = el.offsetWidth || r.w;
      const h = el.offsetHeight || r.h;
      const box = { x: r.x + r.w / 2 - w / 2, y: r.y + r.h / 2 - h / 2, w, h };
      pushOwnLayers(el, style, box, rotate);
      const layer = textLayer(el, blockLines(el).replace(/\n/g, ' '), box, rotate);
      if (layer) {
        // Keep the line breaks; textLayer only collapses whitespace and applies text-transform.
        const lines = blockLines(el);
        layer.text = style.textTransform === 'uppercase' ? lines.toUpperCase() : lines;
        layer.align = 'center';
        layers.push(layer);
      }
      return;
    }

    pushOwnLayers(el, style, relative(el.getBoundingClientRect()), 0);

    el.childNodes.forEach((child) => {
      if (child.nodeType === Node.TEXT_NODE) {
        if (!child.textContent?.trim()) return;
        const range = document.createRange();
        range.selectNodeContents(child);
        const rect = range.getBoundingClientRect();
        if (rect.width === 0 && rect.height === 0) return;
        const layer = textLayer(el, child.textContent, relative(rect), 0);
        if (layer) layers.push(layer);
      } else if (child instanceof HTMLElement) {
        visit(child);
      }
    });
  };

  visit(root);
  return layers;
}
