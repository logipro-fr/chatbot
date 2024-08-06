<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");

require_once dirname(__DIR__, 2) . '/vendor/autoload_runtime.php';
if (array_key_exists("REQUEST_METHOD", $_SERVER) && $_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    die();
}
return function (array $context) {
    $kernelClass = $_ENV['KERNEL_CLASS'];
    return new $kernelClass($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
