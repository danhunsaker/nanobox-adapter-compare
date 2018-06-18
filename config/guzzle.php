<?php

use Apix\Log\Logger\ErrorLog;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LogLevel;

$stack = HandlerStack::create();
$stack->setHandler(new CurlHandler());
$logger = new ErrorLog();

// $logger->setDeferred(true);
$logger->setMinLevel(getenv('LOG_LEVEL') ?: LogLevel::INFO);

$stack->push(Middleware::log($logger, new MessageFormatter(MessageFormatter::CLF), LogLevel::INFO));
// $stack->push(Middleware::log($logger, new MessageFormatter(MessageFormatter::DEBUG), LogLevel::DEBUG));

return [
    'default' => [
        'handler' => $stack,
    ],
];
