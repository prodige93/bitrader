<?php
// Minimal front controller for local testing

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;

$routes = new RouteCollection();

// Define route for home page mapped to HomeController::index
$routes->add('app_home', new Route('/', [
    '_controller' => [new \App\Controller\HomeController(), 'index']
]));

// Define route for login page mapped to SecurityController::login
$routes->add('app_login', new Route('/login', [
    '_controller' => [new \App\Controller\SecurityController(), 'login']
]));

// Create context and matcher
$context = new RequestContext();
$context->fromRequest(Request::createFromGlobals());
$matcher = new UrlMatcher($routes, $context);

// Create controller resolver
$resolver = new ControllerResolver();

// Create event dispatcher
$dispatcher = new EventDispatcher();

// Create kernel
$kernel = new HttpKernel($dispatcher, $resolver);

// Handle request
$request = Request::createFromGlobals();

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));
    $controller = $request->attributes->get('_controller');
    $response = call_user_func($controller, $request);
} catch (Exception $e) {
    $response = new Response('Not Found', 404);
}

$response->send();
?>
