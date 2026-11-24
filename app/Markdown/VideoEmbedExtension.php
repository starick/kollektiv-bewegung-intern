<?php

namespace App\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Turns a YouTube or Vimeo link that stands alone in its own paragraph into a
 * placeholder. The player is only loaded from the video site after a click
 * (see resources/js/Helpers/video-embed.ts). Links inside text or lists stay links.
 */
class VideoEmbedExtension implements ExtensionInterface, NodeRendererInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, $this->embedVideos(...));
        $environment->addRenderer(VideoEmbed::class, $this);
    }

    private function embedVideos(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();

        foreach ($document->children() as $paragraph) {
            if (! $paragraph instanceof Paragraph || ! ($link = $this->onlyLink($paragraph))) {
                continue;
            }

            $video = VideoEmbed::fromUrl($link->getUrl(), $this->text($link));
            if ($video) {
                $paragraph->replaceWith($video);
            }
        }
    }

    // The paragraph's single link, when it holds nothing else but whitespace
    private function onlyLink(Paragraph $paragraph): ?Link
    {
        $links = [];
        foreach ($paragraph->children() as $child) {
            if ($child instanceof Link) {
                $links[] = $child;
            } elseif (! $child instanceof Text || trim($child->getLiteral()) !== '') {
                return null;
            }
        }

        return count($links) === 1 ? $links[0] : null;
    }

    private function text(Node $node): string
    {
        $text = '';
        foreach ($node->iterator() as $child) {
            if ($child instanceof Text) {
                $text .= $child->getLiteral();
            }
        }

        return trim($text);
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        /** @var VideoEmbed $node */
        $button = new HtmlElement('button', [
            'type' => 'button',
            'class' => 'flex aspect-video w-full flex-col items-center justify-center gap-2 rounded-xl bg-gray-900 p-4 text-center text-white transition-colors hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
            'data-video-embed' => $node->embedUrl,
            'data-video-title' => $node->label,
        ], [
            new HtmlElement('i', ['class' => 'pi pi-play-circle !text-5xl', 'aria-hidden' => 'true'], ''),
            new HtmlElement('span', ['class' => 'font-semibold'], e(__('Load video'))),
            new HtmlElement('span', ['class' => 'text-sm text-gray-300'], e(__('Nothing loads from :provider until you click.', ['provider' => $node->provider]))),
        ]);

        $caption = new HtmlElement('figcaption', ['class' => 'mt-2 text-sm text-gray-500 dark:text-gray-400'], [
            new HtmlElement('a', [
                'href' => $node->url,
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
            ], e($node->label)),
        ]);

        return new HtmlElement('figure', ['class' => 'not-prose my-6'], [$button, $caption]);
    }
}
