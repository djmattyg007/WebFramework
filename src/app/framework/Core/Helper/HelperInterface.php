<?php

namespace MattyG\Framework\Core\Helper;

use \MattyG\Framework\Core\Config as Config;

interface HelperInterface
{
    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName);
}

