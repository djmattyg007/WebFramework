<?php

// This helps accomodate hosting environments that do
// not allow modification of the web root.
$bootstrapper = __DIR__ . "/bootstrapper.php";
if (file_exists($bootstrapper)) {
    $app = require($bootstrapper);
} else {
    $app = require(dirname(__DIR__) . "/bootstrap.php");
}

use \MattyG\Http as Http;
use \Aura\Router as Router;

$app->run(
    new Http\Request($_SERVER, $_GET, $_POST),
    new Http\Response(),
    new Router\Router(new Router\RouteCollection(new Router\RouteFactory()))
);

