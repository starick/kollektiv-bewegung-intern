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
  const keyed: Array<{ layer: Layer; key: number }> = [];
  let currentKey = Number.NEGATIVE_INFINITY;
  const layers = { push: (layer: Layer) => keyed.push({ layer, key: currentKey }) };

  /**
   * Approximates CSS paint order: in-flow content (0) < positioned with z-index auto/0 (0.5)
   * < positive z-index (n). The nearest ancestor that sets a z-index decides the bucket.
   */
  const stackingKey = (el: Element): number => {
    let positioned = false;
    for (let cur: Element | null = el; cur && cur !== root; cur = cur.parentElement) {
      const style = getComputedStyle(cur);
      if (style.position && style.position !== 'static') {
        positioned = true;
        const z = parseInt(style.zIndex, 10);
        if (!Number.isNaN(z)) return z === 0 ? 0.5 : z;
      }
    }
    return positioned ? 0.5 : 0;
  };

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

  /**
   * A text node that wraps has a bounding rect spanning all its lines. If it starts mid-line,
   * that rect starts at the container's left edge and the text would overlap its predecessor,
   * so wrapped text is split into one rect per rendered line.
   */
  const textLines = (node: Text): Array<{ text: string; rect: DOMRect }> => {
    const range = document.createRange();
    range.selectNodeContents(node);
    const rect = range.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) return [];
    const content = node.textContent ?? '';
    const lineCount = typeof range.getClientRects === 'function' ? range.getClientRects().length : 1;
    if (lineCount <= 1) return [{ text: content, rect }];

    const lines: Array<{ words: string[]; left: number; top: number; right: number; bottom: number }> = [];
    for (const match of content.matchAll(/\S+/g)) {
      const wordRange = document.createRange();
      wordRange.setStart(node, match.index!);
      wordRange.setEnd(node, match.index! + match[0].length);
      const r = wordRange.getBoundingClientRect();
      const last = lines[lines.length - 1];
      if (last && Math.abs(r.top - last.top) < r.height / 2) {
        last.words.push(match[0]);
        last.left = Math.min(last.left, r.left);
        last.right = Math.max(last.right, r.right);
        last.bottom = Math.max(last.bottom, r.bottom);
      } else {
        lines.push({ words: [match[0]], left: r.left, top: r.top, right: r.right, bottom: r.bottom });
      }
    }
    return lines.map((l) => ({
      text: l.words.join(' '),
      rect: new DOMRect(l.left, l.top, l.right - l.left, l.bottom - l.top)
    }));
  };

  const visit = (el: HTMLElement) => {
    const style = getComputedStyle(el);
    if (isHidden(style)) return;

    currentKey = el === root ? Number.NEGATIVE_INFINITY : stackingKey(el);
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
        currentKey = el === root ? 0 : stackingKey(el);
        for (const line of textLines(child as Text)) {
          const layer = textLayer(el, line.text, relative(line.rect), 0);
          if (layer) layers.push(layer);
        }
      } else if (child instanceof HTMLElement) {
        visit(child);
      }
    });
  };

  visit(root);
  // Array.prototype.sort is stable, so DOM order is kept within each stacking bucket.
  return keyed.sort((a, b) => a.key - b.key).map((k) => k.layer);
}
