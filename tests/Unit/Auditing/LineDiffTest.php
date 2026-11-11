<?php

namespace Tests\Unit\Auditing;

use App\Auditing\LineDiff;
use PHPUnit\Framework\TestCase;

class LineDiffTest extends TestCase
{
    public function test_changed_line_is_shown_with_two_lines_of_context(): void
    {
        $old = "a\nb\nc\nd\ne\nf\ng";
        $new = "a\nb\nc\nD\ne\nf\ng";

        $this->assertSame([
            'added' => 1,
            'removed' => 1,
            'hunks' => [[
                ['type' => 'context', 'text' => 'b'],
                ['type' => 'context', 'text' => 'c'],
                ['type' => 'removed', 'text' => 'd'],
                ['type' => 'added', 'text' => 'D'],
                ['type' => 'context', 'text' => 'e'],
                ['type' => 'context', 'text' => 'f'],
            ]],
        ], LineDiff::between($old, $new));
    }

    public function test_distant_changes_get_separate_hunks(): void
    {
        $lines = array_map('strval', range(1, 20));
        $old = implode("\n", $lines);
        $new = implode("\n", array_replace($lines, [1 => 'two', 18 => 'nineteen']));

        $diff = LineDiff::between($old, $new);

        $this->assertSame(2, $diff['added']);
        $this->assertSame(2, $diff['removed']);
        $this->assertCount(2, $diff['hunks']);
    }

    public function test_close_changes_share_one_hunk(): void
    {
        $diff = LineDiff::between("a\nb\nc\nd\ne", "A\nb\nc\nd\nE");

        $this->assertCount(1, $diff['hunks']);
    }

    public function test_added_text_on_empty_protocol(): void
    {
        $this->assertSame([
            'added' => 2,
            'removed' => 0,
            'hunks' => [[
                ['type' => 'added', 'text' => '# Plenum'],
                ['type' => 'added', 'text' => '- Punkt'],
            ]],
        ], LineDiff::between('', "# Plenum\n- Punkt"));
    }

    public function test_trailing_newline_and_line_endings_are_not_changes(): void
    {
        $this->assertSame(
            ['added' => 0, 'removed' => 0, 'hunks' => []],
            LineDiff::between("a\r\nb", "a\nb\n"),
        );
    }

    public function test_added_empty_line_is_a_change(): void
    {
        $diff = LineDiff::between("a\nb", "a\n\nb");

        $this->assertSame(1, $diff['added']);
        $this->assertSame(0, $diff['removed']);
    }
}
