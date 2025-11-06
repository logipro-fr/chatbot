<?php

namespace Chatbot\Infrastructure\Shared;

use Chatbot\Infrastructure\Exception\NoPWDException;

class CurrentWorkDirPath
{
    public static function getPath(): string
    {
        if (isset($_ENV["PWD"])) {
            /** @var string $env */
            $env = $_ENV["PWD"];
            return $env;
        }

        if (getenv('PWD') !== false) {
            return strval(getenv('PWD'));
        }
        throw new NoPWDException("Env var PWD no found");
    }
}
