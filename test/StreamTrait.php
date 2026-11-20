<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use PHPUnit\Framework\Attributes\After;

use function fopen;

use const STDERR;
use const STDIN;
use const STDOUT;

trait StreamTrait
{
    /**
     * @return resource
     */
    protected function openMemoryStream()
    {
        $stream = fopen('php://memory', 'w+');
        $this->assertIsResource($stream);

        return $stream;
    }

    /**
     * Restore the default IO streams, so a stream closed by one test is never used by the next one.
     */
    #[After]
    public function resetStreams(): void
    {
        Input::setStream(STDIN);
        Output::setErrorStream(STDERR);
        Output::setOutputStream(STDOUT);
    }
}
