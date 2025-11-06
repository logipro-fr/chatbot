<?php

namespace Chatbot\Domain\Shared\FileManagement;

use function Safe\realpath;

class BaseDir
{
    private const DIR = __DIR__ . '/../../../..';

    public static function getPathTo(string $path): string
    {
        return realpath(self::DIR) . $path;
    }
}
