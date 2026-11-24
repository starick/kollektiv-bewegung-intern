<?php

namespace Tests\Unit\Markdown;

use App\Markdown\Markdown;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VideoEmbedTest extends TestCase
{
    public static function videoLinks(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'youtube watch with more parameters' => ['https://youtube.com/watch?list=x&v=dQw4w9WgXcQ&t=10', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'youtube mobile' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'youtube short link' => ['https://youtu.be/dQw4w9WgXcQ?si=abc', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'youtube over http' => ['http://youtu.be/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'youtube shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1'],
            'vimeo' => ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871?autoplay=1&dnt=1'],
            'vimeo unlisted' => ['https://vimeo.com/76979871/8272103f6e', 'https://player.vimeo.com/video/76979871?autoplay=1&dnt=1&h=8272103f6e'],
            'vimeo player' => ['https://player.vimeo.com/video/76979871', 'https://player.vimeo.com/video/76979871?autoplay=1&dnt=1'],
        ];
    }

    #[DataProvider('videoLinks')]
    public function test_a_video_link_on_its_own_line_becomes_a_click_to_load_placeholder(string $url, string $embed): void
    {
        $html = Markdown::toHtml("Davor\n\n{$url}\n\nDanach");

        $this->assertStringContainsString('data-video-embed="'.e($embed).'"', $html);
        // Nothing is loaded from the video site until the reader clicks
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringContainsString('<p>Davor</p>', $html);
        $this->assertStringContainsString('<p>Danach</p>', $html);
    }

    public function test_a_markdown_link_on_its_own_line_is_embedded_too(): void
    {
        $html = Markdown::toHtml('[Anleitung](https://youtu.be/dQw4w9WgXcQ)');

        $this->assertStringContainsString('data-video-embed="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1"', $html);
        $this->assertStringContainsString('Anleitung', $html);
    }

    public function test_the_placeholder_links_to_the_video_on_its_site(): void
    {
        $html = Markdown::toHtml('https://youtu.be/dQw4w9WgXcQ');

        $this->assertStringContainsString('href="https://youtu.be/dQw4w9WgXcQ"', $html);
    }

    public function test_a_video_link_inside_a_sentence_stays_a_link(): void
    {
        $html = Markdown::toHtml('Schau dir https://youtu.be/dQw4w9WgXcQ an.');

        $this->assertStringNotContainsString('data-video-embed', $html);
        $this->assertStringContainsString('<a href="https://youtu.be/dQw4w9WgXcQ">', $html);
    }

    public function test_a_video_link_in_a_list_stays_a_link(): void
    {
        $html = Markdown::toHtml('- https://youtu.be/dQw4w9WgXcQ');

        $this->assertStringNotContainsString('data-video-embed', $html);
    }

    public function test_other_links_on_their_own_line_stay_links(): void
    {
        foreach ([
            'https://example.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com.evil.example/watch?v=dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=too-short',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ"onload="x',
            'https://vimeo.com/channels/staffpicks',
        ] as $url) {
            $this->assertStringNotContainsString('data-video-embed', Markdown::toHtml($url), $url);
        }
    }
}
