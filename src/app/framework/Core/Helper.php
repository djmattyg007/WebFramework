<?php

namespace MattyG\Framework\Core;

interface Helper
{
    /**
     * @param Config $config
     * @param string $helperName
     */
    public function __construct(Config $config, $helperName);
}

