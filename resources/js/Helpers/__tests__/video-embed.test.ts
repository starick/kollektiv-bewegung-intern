import { beforeEach, describe, expect, it } from 'vitest';
import { loadVideoEmbed } from '@/Helpers/video-embed';

const placeholder = (src: string) => `
  <figure>
    <button type="button" data-video-embed="${src}" data-video-title="Anleitung">
      <i class="pi pi-play-circle"></i><span>Load video</span>
    </button>
  </figure>`;

const clickOn = (selector: string) => {
  const target = document.querySelector(selector)!;
  const event = new MouseEvent('click', { bubbles: true });
  Object.defineProperty(event, 'target', { value: target });
  loadVideoEmbed(event);
};

describe('loadVideoEmbed', () => {
  beforeEach(() => (document.body.innerHTML = ''));

  it('replaces the placeholder with the player when clicked', () => {
    document.body.innerHTML = placeholder(
      'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'
    );

    clickOn('span');

    const iframe = document.querySelector('iframe')!;
    expect(document.querySelector('button')).toBeNull();
    expect(iframe.src).toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1');
    expect(iframe.title).toBe('Anleitung');
    expect(iframe.allowFullscreen).toBe(true);
  });

  it('loads Vimeo players', () => {
    document.body.innerHTML = placeholder(
      'https://player.vimeo.com/video/76979871?autoplay=1&dnt=1'
    );

    clickOn('button');

    expect(document.querySelector('iframe')?.src).toContain(
      'https://player.vimeo.com/video/76979871'
    );
  });

  it('ignores addresses that are not a known player', () => {
    document.body.innerHTML = placeholder('https://evil.example/embed');

    clickOn('button');

    expect(document.querySelector('iframe')).toBeNull();
    expect(document.querySelector('button')).not.toBeNull();
  });

  it('ignores clicks elsewhere', () => {
    document.body.innerHTML = `<p>Text</p>${placeholder('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')}`;

    clickOn('p');

    expect(document.querySelector('iframe')).toBeNull();
  });
});
