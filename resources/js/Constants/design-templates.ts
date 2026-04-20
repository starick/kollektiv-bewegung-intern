import { TimeTableDesignConfig } from '@/Types/time-table';

export interface DesignTemplate {
  name: string;
  preview: string;
  config: TimeTableDesignConfig;
}

export const DESIGN_TEMPLATES: DesignTemplate[] = [
  {
    name: 'Abstract Warm',
    preview: '/img/abstract-1.png',
    config: {
      background: { image: '/img/abstract-1.png' },
      body: {
        fontSize: '1em',
        color: '#424242',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: 'Figtree, sans-serif'
      },
      overlay: { hasOverlay: false, color: '#000000', opacity: 0.3 },
      highlight: {
        color: '#B8611A',
        background: '#F5DCB6',
        registrationNoteAsOverlay: true
      },
      header: { color: '#A14800', scale: 1.15, fontFamily: 'Oswald, sans-serif' }
    }
  },
  {
    name: 'Abstract Cool',
    preview: '/img/abstract-2.png',
    config: {
      background: { image: '/img/abstract-2.png' },
      body: {
        fontSize: '1em',
        color: '#231c18',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: 'Montserrat, sans-serif'
      },
      overlay: { hasOverlay: true, color: '#fff', opacity: 0.3 },
      highlight: { color: '#ac5f01', background: '#f8d498', registrationNoteAsOverlay: true },
      header: { color: '#854414', scale: 1.15, fontFamily: 'Montserrat, sans-serif' }
    }
  },
  {
    name: 'Soft',
    preview: '/img/soft-1.png',
    config: {
      background: { image: '/img/soft-1.png' },
      body: {
        fontSize: '1em',
        color: '#2b2b2b',
        scale: 0.98,
        'line-height': 1.6,
        'margin-top': '0.3em',
        fontFamily: 'Raleway, sans-serif'
      },
      overlay: { hasOverlay: true, color: '#ffffff', opacity: 0.55 },
      highlight: { color: '#FFFFFF', background: '#EAE7F5', registrationNoteAsOverlay: false },
      header: { color: '#ffffff', scale: 1.2, fontFamily: '"Playfair Display", serif' }
    }
  },
  {
    name: 'Münster Hafen',
    preview: '/img/meunster-hafen.png',
    config: {
      background: { image: '/img/meunster-hafen.png' },
      body: {
        fontSize: '1em',
        color: '#1a1a1a',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: 'Inter, sans-serif'
      },
      overlay: { hasOverlay: true, color: '#ffffff', opacity: 0.6 },
      highlight: { color: '#c0392b', background: '#fadbd8', registrationNoteAsOverlay: true },
      header: { color: '#922b21', scale: 1.15, fontFamily: 'Oswald, sans-serif' }
    }
  },
  {
    name: 'Münster Hafen Dark',
    preview: '/img/meunster-hafen-dark.png',
    config: {
      background: { image: '/img/meunster-hafen-dark.png' },
      body: {
        fontSize: '1em',
        color: '#f0f0f0',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: '"Playfair Display", serif'
      },
      overlay: { hasOverlay: true, color: '#000000', opacity: 0.4 },
      highlight: { color: '#e0c070', background: '#5a4a20', registrationNoteAsOverlay: true },
      header: { color: '#f0d080', scale: 1.15, fontFamily: '"Playfair Display", serif' }
    }
  },
  {
    name: 'Pastel',
    preview: '/img/pastel-1.png',
    config: {
      background: { image: '/img/pastel-1.png' },
      body: {
        fontSize: '1em',
        color: '#4a4a4a',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: 'Figtree, sans-serif'
      },
      overlay: { hasOverlay: false, color: '#000000', opacity: 0.2 },
      highlight: { color: '#7d3c98', background: '#e8daef', registrationNoteAsOverlay: true },
      header: { color: '#6c3483', scale: 1.15, fontFamily: 'Raleway, sans-serif' }
    }
  }
];
