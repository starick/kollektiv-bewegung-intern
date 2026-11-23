import { describe, expect, it } from 'vitest';
import {
  firstFontFamily,
  parseCssColor,
  pxToIn,
  pxToPt,
  rotationFromTransform,
  toTransparency
} from '@/Helpers/pptx-units';

describe('pptx-units', () => {
  it('converts px to inches and points', () => {
    expect(pxToIn(96)).toBe(1);
    expect(pxToPt(16)).toBe(12);
  });

  it('parses css colors', () => {
    expect(parseCssColor('rgba(255, 0, 0, 0.5)')).toEqual({ hex: 'FF0000', alpha: 0.5 });
    expect(parseCssColor('rgb(247, 200, 143)')).toEqual({ hex: 'F7C88F', alpha: 1 });
    expect(parseCssColor('#f7c88f55')).toEqual({ hex: 'F7C88F', alpha: 0x55 / 255 });
    expect(parseCssColor('#fff')).toEqual({ hex: 'FFFFFF', alpha: 1 });
    expect(parseCssColor('transparent')).toBeNull();
    expect(parseCssColor('rgba(0, 0, 0, 0)')).toBeNull();
  });

  it('computes transparency from alpha and opacity', () => {
    expect(toTransparency(0.5, 0.8)).toBe(60);
    expect(toTransparency(1, 1)).toBe(0);
  });

  it('reads rotation from a transform matrix', () => {
    expect(rotationFromTransform('matrix(0.866025, 0.5, -0.5, 0.866025, 0, 0)')).toBe(30);
    expect(rotationFromTransform('none')).toBe(0);
    expect(rotationFromTransform('rotate(30deg)')).toBe(30);
  });

  it('extracts the first font family', () => {
    expect(firstFontFamily('"Open Sans", sans-serif')).toBe('Open Sans');
    expect(firstFontFamily('Oswald, sans-serif')).toBe('Oswald');
  });
});
