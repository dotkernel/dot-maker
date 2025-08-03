<?php

declare(strict_types=1);

namespace Dot\Maker;

use function sprintf;

enum ColorEnum: int
{
    case Default                 = 0;
    case ForegroundBlack         = 30;
    case ForegroundRed           = 31;
    case ForegroundGreen         = 32;
    case ForegroundYellow        = 33;
    case ForegroundBlue          = 34;
    case ForegroundMagenta       = 35;
    case ForegroundCyan          = 36;
    case ForegroundWhite         = 37;
    case BackgroundBlack         = 40;
    case BackgroundRed           = 41;
    case BackgroundGreen         = 42;
    case BackgroundYellow        = 43;
    case BackgroundBlue          = 44;
    case BackgroundMagenta       = 45;
    case BackgroundCyan          = 46;
    case BackgroundWhite         = 47;
    case ForegroundBrightBlack   = 90;
    case ForegroundBrightRed     = 91;
    case ForegroundBrightGreen   = 92;
    case ForegroundBrightYellow  = 93;
    case ForegroundBrightBlue    = 94;
    case ForegroundBrightMagenta = 95;
    case ForegroundBrightCyan    = 96;
    case ForegroundBrightWhite   = 97;
    case BackgroundBrightBlack   = 100;
    case BackgroundBrightRed     = 101;
    case BackgroundBrightGreen   = 102;
    case BackgroundBrightYellow  = 103;
    case BackgroundBrightBlue    = 104;
    case BackgroundBrightMagenta = 105;
    case BackgroundBrightCyan    = 106;
    case BackgroundBrightWhite   = 107;

    public static function colorize(
        string $text,
        self $foregroundColor = self::Default,
        self $backgroundColor = self::Default,
    ): string {
        if ($backgroundColor->value !== self::Default->value) {
            return sprintf(
                "\033[%d;%dm%s\033[%dm",
                $foregroundColor->value,
                $backgroundColor->value,
                $text,
                self::Default->value
            );
        }

        return sprintf(
            "\033[%dm%s\033[%dm",
            $foregroundColor->value,
            $text,
            self::Default->value
        );
    }
}
