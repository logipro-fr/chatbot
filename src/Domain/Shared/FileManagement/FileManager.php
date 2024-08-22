<?php

namespace Chatbot\Domain\Shared\FileManagement;

use FilesystemIterator;
use RecursiveDirectoryIterator;

use function Safe\fclose;
use function Safe\fopen;
use function Safe\mkdir;
use function Safe\rmdir;
use function Safe\unlink;

class FileManager
{
    public static function createFile(string $filepath): void
    {
        self::createDir(dirname($filepath));
        $file = fopen($filepath, 'c');
        fclose($file);
    }

    public static function deleteFile(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public static function createDir(string $dirpath): void
    {
        if (!is_dir($dirpath)) {
            mkdir($dirpath);
        }
    }

    public static function deleteDir(string $dirpath): void
    {
        if (is_dir($dirpath)) {
            $files = [];
            $it = new RecursiveDirectoryIterator($dirpath, FilesystemIterator::CURRENT_AS_PATHNAME);
            for (; $it->valid(); $it->next()) {
                if (!$it->isDot()) {
                    $files[] = $it->current();
                }
            }
            foreach ($files as $file) {
                FileManager::deleteFile($file);
            }
            rmdir($dirpath);
        }
    }
}
