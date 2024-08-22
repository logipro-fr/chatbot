<?php

namespace Chatbot\Tests\Domain\Shared\FileManagement;

use Chatbot\Domain\Shared\FileManagement\FileManager;
use Chatbot\Domain\Shared\FileManagement\BaseDir;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;

use function Safe\fopen;
use function Safe\mkdir;
use function Safe\rmdir;

class FileManagerTest extends TestCase
{
    private string $filepath;

    public function setUp(): void
    {
        $this->filepath = BaseDir::getPathTo("/log/testingfilemanagement.log");
    }

    public function testCreateFile(): void
    {
        if (file_exists($this->filepath)) {
            unlink($this->filepath);
        }
        $this->assertFalse(file_exists($this->filepath));

        FileManager::createFile($this->filepath);
        $this->assertTrue(file_exists($this->filepath));
    }

    public function testDeleteFile(): void
    {
        if (!file_exists($this->filepath)) {
            if (!is_dir(dirname($this->filepath))) {
                mkdir(dirname($this->filepath));
            }
            $file = fopen($this->filepath, 'c');
            fclose($file);
        }
        $this->assertTrue(file_exists($this->filepath));

        FileManager::deleteFile($this->filepath);
        $this->assertFalse(file_exists($this->filepath));

        FileManager::deleteFile($this->filepath);
        $this->assertFalse(file_exists($this->filepath));
    }

    public function testCreateDir(): void
    {
        $dir = dirname($this->filepath);

        if (is_dir($dir)) {
            $files = [];
            $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::CURRENT_AS_PATHNAME);
            for (; $it->valid(); $it->next()) {
                if (!$it->isDot()) {
                    $files[] = $it->current();
                }
            }
            foreach ($files as $file) {
                FileManager::deleteFile($file);
            }
            rmdir($dir);
        }
        $this->assertFalse(is_dir($dir));

        FileManager::createDir($dir);
        $this->assertTrue(is_dir($dir));
        $this->assertFalse(file_exists($this->filepath));
    }

    public function testDeleteDir(): void
    {
        $filepath2 = BaseDir::getPathTo("/log/testingdirdeletion.log");
        $dir = dirname($this->filepath);

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (!file_exists($this->filepath)) {
            $file = fopen($this->filepath, 'c');
            fclose($file);
        }
        if (!file_exists($filepath2)) {
            $file = fopen($filepath2, 'c');
            fclose($file);
        }
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(file_exists($this->filepath));
        $this->assertTrue(file_exists($filepath2));

        FileManager::deleteDir($dir);

        $this->assertFalse(is_dir($dir));
    }

    public function testCreateFileWithoutDir(): void
    {
        FileManager::deleteDir($dir = dirname($this->filepath));
        $this->assertFalse(is_dir($dir));
        FileManager::createFile($this->filepath);
        $this->assertTrue(is_dir($dir));
        $this->assertTrue(file_exists($this->filepath));
    }
}
