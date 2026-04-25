import { TimeTableDesignConfig } from '@/Types/time-table';

export interface DesignTemplate {
  name: string;
  preview: string;
  config: TimeTableDesignConfig;
}

export const DESIGN_TEMPLATES: DesignTemplate[] = [
  {
    name: 'Abstract 1',
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
    name: 'Abstract 2',
    preview: '/img/abstract-2.png',
    config: {
      background: { image: '/img/abstract-2.png' },
      body: {
        fontSize: '1em',
        color: '#231c18',
        scale: 1,
        'line-height': 1.4,
        'margin-top': '0',
        fontFamily: 'Montserrat, sans-serif'
      },
      overlay: { hasOverlay: true, color: '#fff', opacity: 0.3 },
      highlight: { color: '#ac5f01', background: '#f8d498', registrationNoteAsOverlay: true },
      header: { color: '#854414', scale: 0.95, fontFamily: 'Montserrat, sans-serif' }
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
      highlight: { color: '#791BD1', background: '#EAE7F5', registrationNoteAsOverlay: false },
      header: { color: '#4C288F', scale: 1.2, fontFamily: '"Playfair Display", serif' }
    }
  },
  {
    name: 'Münster Hafen',
    preview: '/img/meunster-hafen.png',
    config: {
      background: { image: '/img/meunster-hafen.png' },
      body: {
        fontSize: '1em',
        color: '#FAFAFA',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0em',
        fontFamily: 'Inter, sans-serif'
      },
      overlay: { hasOverlay: true, color: '#1E3142', opacity: 0.5 },
      highlight: { color: '#66BAFF', background: '#304961', registrationNoteAsOverlay: true },
      header: { color: '#90CAF9', scale: 1.15, fontFamily: 'Oswald, sans-serif' }
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
      background: {
        image: '/img/pastel-1.png'
      },
      body: {
        fontSize: '1em',
        color: '#4a4a4a',
        scale: 1,
        'line-height': 1.6,
        'margin-top': '0.5em',
        fontFamily: 'Raleway, sans-serif'
      },
      overlay: {
        hasOverlay: false,
        color: '#000000',
        opacity: 0.2
      },
      highlight: {
        color: '#B3522E',
        background: '#F0C48D',
        registrationNoteAsOverlay: true
      },
      header: {
        color: '#9C5230',
        scale: 1.1,
        fontFamily: '"Playfair Display", serif'
      }
    }
  }
];
