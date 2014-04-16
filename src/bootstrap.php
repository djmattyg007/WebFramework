<?php

$pools = glob(__DIR__ . "/config/*");
array_walk($pools, function(&$value) {
    $value = str_replace(__DIR__ . "/config/", "", $value);
});

foreach ($pools as $pool) {
    $functionsFile = __DIR__ . "/app/$pool/Core/functions.php";
    if (file_exists($functionsFile)) {
        require($functionsFile);
    }
}
// Don't pollute the global namespace.
unset($pool);
unset($functionsFile);

if (!defined("MATTYG_FRAMEWORK_AUTOLOADER_NOREGISTER")) {
    require(__DIR__ . "/autoload/Autoloader.php");

    $autoloader = new \MattyG\Framework\Autoload\Autoloader(__DIR__, $pools, true);
}

if (!defined("MATTYG_FRAMEWORK_APP_INIT")) {
    define("MATTYG_FRAMEWORK_APP_INIT", true);
}

$app = new \MattyG\Framework\Core\App(__DIR__, $pools, MATTYG_FRAMEWORK_APP_INIT);

return $app;

