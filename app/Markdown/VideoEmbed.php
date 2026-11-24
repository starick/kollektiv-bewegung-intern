<?php

namespace App\Markdown;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * A YouTube or Vimeo video that only loads once the reader clicks it.
 */
class VideoEmbed extends AbstractBlock
{
    public function __construct(
        public readonly string $provider,
        public readonly string $embedUrl,
        public readonly string $url,
        public readonly string $label,
    ) {
        parent::__construct();
    }

    /**
     * The video behind a link, or null for links to anything else.
     * The player address is rebuilt from the video id alone, so nothing else
     * from the link reaches the page.
     */
    public static function fromUrl(string $url, string $label): ?self
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $youtubeId = match (true) {
            $host === 'youtu.be' => ltrim($path, '/'),
            in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) => $path === '/watch'
                ? (is_string($query['v'] ?? null) ? $query['v'] : '')
                : (preg_match('~^/(?:shorts|embed)/([^/]+)$~', $path, $m) ? $m[1] : ''),
            default => null,
        };

        if ($youtubeId !== null) {
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId)
                ? new self('YouTube', "https://www.youtube-nocookie.com/embed/{$youtubeId}?autoplay=1", $url, $label)
                : null;
        }

        $vimeo = match ($host) {
            'vimeo.com', 'www.vimeo.com' => '~^/(\d+)(?:/([0-9a-f]+))?/?$~',
            'player.vimeo.com' => '~^/video/(\d+)/?$~',
            default => null,
        };

        if ($vimeo !== null && preg_match($vimeo, $path, $m)) {
            // Unlisted videos need their hash to play
            $hash = isset($m[2]) ? "&h={$m[2]}" : '';

            return new self('Vimeo', "https://player.vimeo.com/video/{$m[1]}?autoplay=1&dnt=1{$hash}", $url, $label);
        }

        return null;
    }
}
