// Players the server builds placeholders for, see App\Markdown\VideoEmbed
const PLAYERS = ['https://www.youtube-nocookie.com/embed/', 'https://player.vimeo.com/video/'];

/**
 * Click handler for rendered Markdown: swaps a video placeholder for the player,
 * so nothing loads from the video site before the reader asks for it.
 */
export function loadVideoEmbed(event: Event): void {
  const target = event.target instanceof Element ? event.target : null;
  const button = target?.closest<HTMLElement>('button[data-video-embed]');
  const src = button?.dataset.videoEmbed ?? '';
  if (!button || !PLAYERS.some((player) => src.startsWith(player))) {
    return;
  }

  const iframe = document.createElement('iframe');
  iframe.src = src;
  iframe.title = button.dataset.videoTitle ?? '';
  iframe.allow = 'autoplay; fullscreen; picture-in-picture; encrypted-media';
  iframe.allowFullscreen = true;
  // YouTube refuses to play embeds that send no referrer
  iframe.referrerPolicy = 'strict-origin-when-cross-origin';
  iframe.className = 'aspect-video w-full rounded-xl border-0';
  button.replaceWith(iframe);
}
