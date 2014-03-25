<?php

namespace MattyG\Framework\Core\Helper;

interface HelperInterfaceExtra extends HelperInterface
{
    /**
     * This is actually a temporary hack. PHP 5.6 will support
     * the '...' operator, meaning it will unpack an array or a
     * Traversable object in a function call.
     * This means any necessary dependent helpers can be given
     * to a helper dynamically when it is constructed, rather
     * than through a setter after the fact.
     */
    public function giveHelpers(array $helpers);
}

