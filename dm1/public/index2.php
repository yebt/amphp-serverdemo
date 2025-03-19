<?php

use Amp\Http\HttpStatus;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket\InternetAddress;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

use function Amp\ByteStream\getStdout;
use function Amp\trapSignal;

require __DIR__.'/../vendor/autoload.php';

// PSR-3 logger instance

$logHandler = new StreamHandler(getStdout());
$logHandler->pushProcessor(new PsrLogMessageProcessor);
$logHandler->setFormatter(new ConsoleFormatter);

$logger = new Logger('server');
$logger->pushHandler($logHandler);

// server socket
$server = SocketHttpServer::createForDirectAccess($logger);

$server->expose(new InternetAddress("0.0.0.0", 1337));
$server->expose(new InternetAddress("[::]", 1337));

// Error handler
$errorHandler = new DefaultErrorHandler();

// router
$router = new Router($server, $logger, $errorHandler);

// Add routes
$router->addRoute('GET', '/', new ClosureRequestHandler(function () {
    return new Response(
        status: HttpStatus::OK,
        headers: ['Content-type' => 'text/plain'],
        body: 'Hope wrld'
    );
}));

$router->addRoute('GET', '/{name}', new ClosureRequestHandler(function (Request $request) {
    $args = $request->getAttribute(Router::class);

    return new Response(
        status: HttpStatus::OK,
        headers: ['Content-type' => 'text/plain'],
        body: "Hope wrd, Hope {$args['name']}"
    );
}));

// Run the server
// $server->expose('0.0.0.0:1337');
$server->start($router, $errorHandler);

// Serve requests until SIGINT or SIGTERM is received by the process.
$signal = trapSignal([SIGINT, SIGTERM]);

$logger->info("Caught signal $signal, stopping server");

$server->stop();
