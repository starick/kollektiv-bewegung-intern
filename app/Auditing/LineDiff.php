<?php

namespace App\Auditing;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * Line based diff of two texts, reduced to the changed lines plus a little context around them.
 */
class LineDiff
{
    /**
     * Unchanged lines shown before and after each change.
     */
    private const CONTEXT = 2;

    /**
     * @return array{
     *     added: int,
     *     removed: int,
     *     hunks: array<int, array<int, array{type: 'context'|'added'|'removed', text: string}>>
     * }
     */
    public static function between(?string $old, ?string $new): array
    {
        // Split into lines ourselves, so line endings and a final newline don't count as changes
        $lines = (new Differ(new UnifiedDiffOutputBuilder))->diffToArray(
            self::lines($old),
            self::lines($new),
        );

        $types = [Differ::OLD => 'context', Differ::ADDED => 'added', Differ::REMOVED => 'removed'];
        $lines = array_values(array_map(
            fn (array $line) => ['type' => $types[$line[1]], 'text' => $line[0]],
            array_filter($lines, fn (array $line) => isset($types[$line[1]])),
        ));

        return [
            'added' => count(array_filter($lines, fn (array $line) => $line['type'] === 'added')),
            'removed' => count(array_filter($lines, fn (array $line) => $line['type'] === 'removed')),
            'hunks' => self::hunks($lines),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function lines(?string $text): array
    {
        // One final newline ends the last line, it doesn't start a new empty one
        $text = preg_replace('/\R\z/', '', $text ?? '');

        return $text === '' ? [] : preg_split('/\R/', $text);
    }

    /**
     * Groups changed lines with their context; changes close to each other share one hunk.
     *
     * @param  array<int, array{type: string, text: string}>  $lines
     * @return array<int, array<int, array{type: string, text: string}>>
     */
    private static function hunks(array $lines): array
    {
        $visible = [];

        foreach ($lines as $index => $line) {
            if ($line['type'] !== 'context') {
                $from = max(0, $index - self::CONTEXT);
                $to = min(count($lines) - 1, $index + self::CONTEXT);
                for ($i = $from; $i <= $to; $i++) {
                    $visible[$i] = true;
                }
            }
        }

        $hunks = [];
        $previous = null;

        foreach (array_keys($visible) as $index) {
            if ($previous === null || $index !== $previous + 1) {
                $hunks[] = [];
            }
            $hunks[array_key_last($hunks)][] = $lines[$index];
            $previous = $index;
        }

        return $hunks;
    }
}
