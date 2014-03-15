<?php

// This helps accomodate hosting environments that do
// not allow modification of the web root.
$bootstrapper = __DIR__ . "/bootstrapper.php";
if (file_exists($bootstrapper)) {
    $app = require($bootstrapper);
} else {
    $app = require(dirname(__DIR__) . "/bootstrap.php");
}

$app->run(
    new \MattyG\Http\Request($_SERVER, $_GET, $_POST),
    new \MattyG\Http\Response()
);

