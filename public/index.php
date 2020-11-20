<?php

declare(strict_types=1);

use App\Controllers\HelloWorldController;
use App\Controllers\IndexController;
use DI\ContainerBuilder;
use FastRoute\RouteCollector;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\MiddlewarePipe;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;

use function DI\create;
use function DI\get;
use function FastRoute\simpleDispatcher;


require_once(dirname(__DIR__) . '/vendor/autoload.php');

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions([
    IndexController::class => create(IndexController::class),
    HelloWorldController::class => create(HelloWorldController::class)
        ->constructor(get('Foo')),
    'Foo' => 'bar'
]);
$container = $containerBuilder->build();

$router = simpleDispatcher(function (RouteCollector $r) {
    $r->get('/hello/{name}', HelloWorldController::class);
});

$requestHandler = new MiddlewarePipe();
$requestHandler->pipe(new FastRoute($router));
$requestHandler->pipe(new RequestHandler($container));

$server = new RequestHandlerRunner(
    $requestHandler,
    new SapiEmitter(),
    static function () {
        return ServerRequestFactory::fromGlobals();
    },
    static function (\Throwable $e) {
        $response = (new ResponseFactory())->createResponse(500);
        $response->getBody()->write(sprintf(
            'An error occurred: %s',
            $e->getMessage()
        ));

        return $response;
    }
);

$server->run();
