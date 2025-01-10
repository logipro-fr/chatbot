<?php

namespace Chatbot\Tests\Infrastructure\Shared;

use Chatbot\Infrastructure\Exception\NoPWDException;
use Chatbot\Infrastructure\Shared\CurrentWorkDirPath;
use PHPUnit\Framework\TestCase;

class CurrentWorkDirPathTest extends TestCase
{
    private const MY_CURRENT_WORKING_DIR = '/home/myfolder';

    private ?string $d_env;
    private ?string $env;

    public function setUp(): void
    {
        /** @var ?string $d_env */
        $d_env = isset($_ENV['PWD']) ? $_ENV['PWD'] : null;
        $this->d_env = $d_env;
        /** @var ?string $d_env */
        $env = getenv('PWD') == false ? null : (string)(getenv('PWD'));
        $this->env = $env;
    }

    public function tearDown(): void
    {
        if ($this->d_env == null) {
            unset($_ENV['PWD']);
        } else {
            $_ENV['PWD'] = $this->d_env;
        }

        if ($this->env == null) {
            putenv('PWD');
        } else {
            putenv('PWD=' . $this->env);
        }
    }

    public function testGetFullPathWithEnv(): void
    {
        $_ENV['PWD'] = self::MY_CURRENT_WORKING_DIR;
        $this->assertEquals(self::MY_CURRENT_WORKING_DIR, $_ENV['PWD']);
        putenv('PWD=' . self::MY_CURRENT_WORKING_DIR);
        $this->assertEquals(self::MY_CURRENT_WORKING_DIR, getenv('PWD'));

        $path = CurrentWorkDirPath::getPath();
        $this->assertEquals(self::MY_CURRENT_WORKING_DIR, $path);
    }

    public function testGetFullPathOnlygetenv(): void
    {
        unset($_ENV['PWD']);
        $this->assertFalse(isset($_ENV['PWD']));
        putenv('PWD=' . self::MY_CURRENT_WORKING_DIR);
        $this->assertEquals(self::MY_CURRENT_WORKING_DIR, getenv('PWD'));

        $path = CurrentWorkDirPath::getPath();
        $this->assertEquals(self::MY_CURRENT_WORKING_DIR, $path);
    }

    public function testNoPWDException(): void
    {
        $this->expectException(NoPWDException::class);
        $this->expectExceptionMessage("Env var PWD no found");
        unset($_ENV['PWD']);
        putenv('PWD');
        CurrentWorkDirPath::getPath();
    }
}
