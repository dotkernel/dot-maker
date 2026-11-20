<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use function array_key_last;
use function array_map;
use function array_pop;
use function explode;
use function implode;
use function max;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function strlen;
use function substr_count;

/**
 * Aligns the double arrows of multi-line array items in generated code, so the output passes
 * the Laminas Coding Standard (WebimpressCodingStandard.Arrays.DoubleArrow).
 *
 * Every key that starts its own line inside the same multi-line array is padded to the longest key
 * of that array, whether its value is on the same line or opens a nested array.
 */
class ArrayAligner
{
    private const KEY_PATTERN = '/^(\s*)(\'[^\']*\'|"[^"]*"|[\w\\\\]+::class)\s*=>\s*(.*)$/';

    public static function align(string $code): string
    {
        $lines = explode("\n", $code);

        /** @var list<list<array{int, string, string, string}>> $groups */
        $groups = [];
        foreach ($lines as $index => $line) {
            if ($groups !== [] && preg_match(self::KEY_PATTERN, $line, $matches) === 1) {
                $groups[array_key_last($groups)][] = [$index, $matches[1], $matches[2], $matches[3]];
            }

            $depth = self::bracketBalance($line);
            for (; $depth > 0; $depth--) {
                $groups[] = [];
            }
            for (; $depth < 0 && $groups !== []; $depth++) {
                self::alignGroup($lines, (array) array_pop($groups));
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, string> $lines
     * @param list<array{int, string, string, string}> $group
     */
    private static function alignGroup(array &$lines, array $group): void
    {
        if ($group === []) {
            return;
        }

        $width = max(array_map(static fn (array $item): int => strlen($item[2]), $group));
        foreach ($group as [$index, $indent, $key, $value]) {
            $lines[$index] = sprintf('%s%s%s=> %s', $indent, $key, str_repeat(' ', $width - strlen($key) + 1), $value);
        }
    }

    /**
     * Opening minus closing square brackets on a line, ignoring brackets inside quoted strings.
     */
    private static function bracketBalance(string $line): int
    {
        $code = (string) preg_replace('/\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"/', '', $line);

        return substr_count($code, '[') - substr_count($code, ']');
    }
}
