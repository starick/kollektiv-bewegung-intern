<?php

namespace App\Wiki;

/**
 * The app's CHANGELOG.md, shown read-only in the wiki.
 */
class Changelog
{
    public function __construct(private readonly string $path) {}

    public static function fromRepository(): self
    {
        return new self(base_path('CHANGELOG.md'));
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    /**
     * The release entries, without the file's own title and the standard-version boilerplate,
     * since the page shows its own title.
     */
    public function markdown(): string
    {
        $markdown = $this->exists() ? file_get_contents($this->path) : '';
        $firstRelease = preg_match('/^#{2,3} \[?\d/m', $markdown, $match, PREG_OFFSET_CAPTURE)
            ? $match[0][1]
            : 0;

        return trim(substr($markdown, $firstRelease));
    }

    /**
     * The link to the changelog in the wiki navigation, null when there is no changelog.
     *
     * @return array{version: ?string}|null
     */
    public function link(): ?array
    {
        return $this->exists() ? ['version' => $this->version()] : null;
    }

    /**
     * The newest released version, like `1.8.2`.
     */
    public function version(): ?string
    {
        return preg_match('/^#{2,3} \[?(\d+\.\d+\.\d+)/m', $this->markdown(), $match) ? $match[1] : null;
    }
}
