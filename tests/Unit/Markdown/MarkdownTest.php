<?php

namespace Tests\Unit\Markdown;

use App\Markdown\Markdown;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    public function test_footnotes_are_rendered(): void
    {
        $html = Markdown::toHtml("Beschlossen[^1].\n\n[^1]: Einstimmig.");

        $this->assertStringContainsString('href="#fn:1"', $html);
        $this->assertStringContainsString('class="footnotes"', $html);
        $this->assertStringContainsString('Einstimmig.', $html);
        $this->assertStringNotContainsString('[^1]', $html);
    }

    public function test_an_external_image_is_rendered(): void
    {
        $html = Markdown::toHtml('Siehe ![Plakat](https://example.com/plakat.png "Sommerfest") hier');

        $this->assertStringContainsString('<img src="https://example.com/plakat.png" alt="Plakat"', $html);
        $this->assertStringContainsString('title="Sommerfest"', $html);
        $this->assertStringContainsString('referrerpolicy="no-referrer"', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    public function test_image_attributes_are_escaped(): void
    {
        $html = Markdown::toHtml('![a" onerror="alert(1)](https://example.com/a.png)');

        $this->assertStringNotContainsString('onerror="', $html);
    }

    public function test_embedded_local_and_unsafe_images_are_dropped(): void
    {
        $html = Markdown::toHtml(
            "Vorher ![a](javascript:alert(1)) ![b](http://example.com/b.png) ![c](/storage/c.png) ![][d] nachher\n\n"
            .'[d]: <data:image/png;base64,iVBORw0KGgo=>'
        );

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('base64', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('Vorher', $html);
        $this->assertStringContainsString('nachher', $html);
    }
}
