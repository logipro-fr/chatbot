<?php

namespace Chatbot\Infrastructure\Shared;

use Chatbot\Infrastructure\Exception\NoPWDException;

use function PHPUnit\Framework\throwException;

class CurrentWorkDirPath
{
    public static function getPath(): string
    {
        if (isset($_ENV["PWD"])) {
            return $_ENV["PWD"];
        }

        if (getenv('PWD') !== false) {
            return getenv('PWD');
        }
        throw new NoPWDException();
    }
}
