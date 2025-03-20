<?php

use Amp\Http\HttpStatus;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket\InternetAddress;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

use function Amp\ByteStream\getStdout;
use function Amp\trapSignal;

require __DIR__.'/vendor/autoload.php';

const APP_PORT = 1337;

// PSR-3 logger

$logHandler = new StreamHandler(getStdout());
$logHandler->pushProcessor(new PsrLogMessageProcessor);
$logHandler->setFormatter(new ConsoleFormatter);

$logger = new Logger('server');
$logger->pushHandler($logHandler);

// Error handler
$errorHandler = new DefaultErrorHandler;

// Server instance
$server = SocketHttpServer::createForDirectAccess($logger);

$server->expose(new InternetAddress('0.0.0.0', APP_PORT));
$server->expose(new InternetAddress('[::]', APP_PORT));

// Instance router
$router = new Router($server, $logger, $errorHandler);

// Static content
$router->setFallback(
    new DocumentRoot(
        $server,
        $errorHandler,
        __DIR__.'/public'
    )
);

// Add routes
$router->addRoute('GET', '/', new ClosureRequestHandler(
    function () {
        return new Response(
            status: HttpStatus::OK,
            headers: ['Content-type' => 'text/plain'],
            body: 'Hope wrld'
        );
    }
));

$router->addRoute('GET', '/greeting/{name}', new ClosureRequestHandler(
    function (Request $request) {
        $args = $request->getAttribute(Router::class);

        return new Response(
            status: HttpStatus::OK,
            headers: ['Content-type' => 'text/plain'],
            body: "Hope wrld, hi {$args['name']}"
        );
    }
));

// Route of static content
$router->addRoute('GET', '/p', new ClosureRequestHandler(
    function (Request $req): Response {

        $html = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head>
            <title>Example</title>
            <link rel="stylesheet" href="./assets/css/style.css"/>
        </head>
        
        <body>
            <div>
                Hello, World!
            </div>
        </body>
        </html>
        HTML;

        return new Response(
            HttpStatus::OK,
            ['content-type' => 'text/html; charset=utf-8'],
            $html
        );
    }
));
// Run the server
$server->start($router, $errorHandler);

// Listening the server SIG
$signal = trapSignal([SIGINT, SIGTERM]);

$logger->info("Caught signal $signal, stopping server");

$server->stop();
