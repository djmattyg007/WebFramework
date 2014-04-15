<?php

/** @var $di \Aura\Di\Container */

$di->setter["MattyG\Framework\Core\Entity\Database"]["setDB"] = $di->get("db");
$di->setter["MattyG\Framework\Core\Entity\DatabaseCollection"]["setDB"] = $di->get("db");

