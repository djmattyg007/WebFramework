<?php

/** @var $di \Aura\Di\Container */

$di->setter["MattyG\\Framework\\Core\\Entity\\Database"]["setDB"] = $di->get("db");
$di->setter["MattyG\\Framework\\Core\\Entity\\DatabaseCollection"]["setDB"] = $di->get("db");

// Helpers
// I have to use closures here because the DI container will throw an exception
// if you try to have optional dependencies.
$di->params["MattyG\\Framework\\Core\\Helper"]["config"] = $di->get("config");
$di->set("url", function() use ($di) {
    if ($di->has("router")) {
        $di->setter["MattyG\\Framework\\Helper\\Core\\Url"]["setRouter"] = $di->get("router");
    }
    return $di->newInstance("MattyG\\Framework\\Helper\\Core\\Url", array("helperName" => "url"));
});
$di->set("translate", $di->lazyNew("MattyG\\Framework\\Helper\\Core\\Translate", array("helperName" => "translate")));

$di->set("meta", $di->lazyNew("MattyG\\Framework\\Helper\\View\\Meta", array("helperName" => "meta")));

$di->set("cssHeader", $di->lazyNew("MattyG\\Framework\\Helper\\View\\AssetManager", array("helperName" => "cssHeader")));
$di->set("jsHeader", $di->lazyNew("MattyG\\Framework\\Helper\\View\\AssetManager", array("helperName" => "jsHeader")));
$di->set("jsFooter", $di->lazyNew("MattyG\\Framework\\Helper\\View\\AssetManager", array("helperName" => "jsFooter")));
$di->params["MattyG\\Framework\\Helper\\View\\AssetManager"]["url"] = $di->lazyGet("url");

$di->set("navbar_left", function() use ($di) {
    if ($di->has("router")) {
        $di->setter["MattyG\\Framework\\Helper\\View\\Menu"]["setRouter"] = $di->get("router");
    }
    return $di->newInstance("MattyG\\Framework\\Helper\\View\\Menu", array("helperName" => "navbar_left"));
});
$di->set("navbar_right", function() use ($di) {
    if ($di->has("router")) {
        $di->setter["MattyG\\Framework\\Helper\\View\\Menu"]["setRouter"] = $di->get("router");
    }
    return $di->newInstance("MattyG\\Framework\\Helper\\View\\Menu", array("helperName" => "navbar_right"));
});
$di->params["MattyG\\Framework\\Helper\\View\\Menu"]["url"] = $di->lazyGet("url");

