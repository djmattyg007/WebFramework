<?php

/** @var $di \Aura\Di\Container */

$di->setter["MattyG\Framework\Core\Entity\Database"]["setDB"] = $di->get("db");

