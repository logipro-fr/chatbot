<?php

namespace Chatbot\Domain\Shared\Exceptions;

use Chatbot\Domain\Shared\FileManagement\BaseDir;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggedException extends \Exception
{
    private const ZERO_CODE = 0;
    public const LOG_FILE = '/log/exceptions.log';


    public function __construct(string $message = "", int $code = self::ZERO_CODE)
    {
        parent::__construct($message, $code);
        $logger = new Logger('logger');
        $logFilePath = BaseDir::getPathTo(self::LOG_FILE);
        $logger->pushHandler(new StreamHandler($logFilePath));

        $logger->error(get_class($this) . "\n" . $message);
    }
}
