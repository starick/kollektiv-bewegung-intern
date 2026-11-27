<?php

namespace App\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders only images hosted elsewhere on the web over https. Embedded images
 * (e.g. base64 data pasted from a document), relative paths and other schemes
 * are dropped, as the app doesn't store images itself.
 */
class ExternalImageExtension implements ExtensionInterface, NodeRendererInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Higher priority than the default image renderer
        $environment->addRenderer(Image::class, $this, 10);
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement|string
    {
        /** @var Image $node */
        $url = $node->getUrl();
        $parts = parse_url($url);
        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return '';
        }

        $alt = '';
        foreach ($node->iterator() as $child) {
            if ($child instanceof Text) {
                $alt .= $child->getLiteral();
            }
        }

        $attributes = [
            'src' => $url,
            'alt' => trim($alt),
            'loading' => 'lazy',
            // Don't tell the image host which page it is shown on
            'referrerpolicy' => 'no-referrer',
        ];
        if ($node->getTitle()) {
            $attributes['title'] = $node->getTitle();
        }

        return new HtmlElement('img', $attributes, '', true);
    }
}
