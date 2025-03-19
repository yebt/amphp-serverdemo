<?php

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket\InternetAddress;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

use function Amp\ByteStream\getStdout;

require __DIR__ . "/vendor/autoload.php";

const APP_PORT=1337;

# PSR-3 logger

$logHandler = new StreamHandler(getStdout());
$logHandler->pushProcessor(new PsrLogMessageProcessor());
$logHandler->setFormatter(new ConsoleFormatter());

$logger = new Logger('server');
$logger->pushHandler($logHandler);

# Error handler
$errorHandler = new DefaultErrorHandler();

# Server instance
$server = SocketHttpServer::createForDirectAccess($logger);

$server->expose(new InternetAddress('0.0.0.0', APP_PORT));
$server->expose(new InternetAddress('[::]', APP_PORT));

# Instance router
$router = new Router($server,$logger,$errorHandler);

# Add routes
$router->addRoute()
