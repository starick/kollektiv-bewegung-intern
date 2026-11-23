export const PX_PER_IN = 96;

export function pxToIn(px: number): number {
  return px / PX_PER_IN;
}

export function pxToPt(px: number): number {
  return px * 0.75;
}

const toHex = (n: number) => Math.round(n).toString(16).padStart(2, '0').toUpperCase();

export function parseCssColor(css: string): { hex: string; alpha: number } | null {
  const value = css.trim().toLowerCase();
  if (!value || value === 'transparent' || value === 'none') return null;

  let r: number, g: number, b: number;
  let alpha = 1;

  const fn = value.match(/^rgba?\(([^)]+)\)$/);
  if (fn) {
    const parts = fn[1].split(/[\s,/]+/).filter(Boolean).map(parseFloat);
    if (parts.length < 3 || parts.some(Number.isNaN)) return null;
    [r, g, b] = parts;
    if (parts.length > 3) alpha = parts[3];
  } else if (value.startsWith('#')) {
    let hex = value.slice(1);
    if (hex.length === 3 || hex.length === 4) hex = [...hex].map((c) => c + c).join('');
    if (!/^[0-9a-f]{6}([0-9a-f]{2})?$/.test(hex)) return null;
    r = parseInt(hex.slice(0, 2), 16);
    g = parseInt(hex.slice(2, 4), 16);
    b = parseInt(hex.slice(4, 6), 16);
    if (hex.length === 8) alpha = parseInt(hex.slice(6, 8), 16) / 255;
  } else {
    return null;
  }

  if (alpha <= 0) return null;
  return { hex: toHex(r) + toHex(g) + toHex(b), alpha };
}

export function toTransparency(alpha: number, opacity: number): number {
  const t = Math.round((1 - alpha * opacity) * 100);
  return Math.min(100, Math.max(0, t));
}

export function rotationFromTransform(transform: string): number {
  const m = transform.match(/^matrix\(([^)]+)\)$/);
  if (!m) return 0;
  const [a, b] = m[1].split(',').map(parseFloat);
  const deg = Math.round(((Math.atan2(b, a) * 180) / Math.PI) * 100) / 100;
  return deg === 0 ? 0 : deg;
}

export function firstFontFamily(stack: string): string {
  return stack.split(',')[0].trim().replace(/^["']|["']$/g, '');
}
