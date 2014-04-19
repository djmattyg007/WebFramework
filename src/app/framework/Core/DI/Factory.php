<?php

namespace MattyG\Framework\Core\DI;

class Factory extends \Aura\Di\Factory
{
    /**
     * @param string $class
     * @param array $parent
     * @return array|void
     */
    public function getUnifiedParams($class, array $parent)
    {
        $unified = parent::getUnifiedParams($class, $parent);
        if (empty($unified)) {
            return $unified;
        }

        $rclass = $this->getReflection($class);
        $rinterfaces = $rclass->getInterfaces();
        foreach ($rinterfaces as $iname => $rinterface) {
            if (isset($this->params[$iname])) {
                // For some reason ReflectionClass::getConstructor() doesn't work for interfaces.
                if ($rinterface->hasMethod("__construct")) {
                    $rparams = $rinterface->getMethod("__construct")->getParameters();
                    foreach ($rparams as $rparam) {
                        $name = $rparam->name;
                        if (!isset($unified[$name]) && isset($this->params[$iname][$name])) {
                            $unified[$name] = $this->params[$iname][$name];
                        }
                    }
                }
            }
        }

        return $unified;
    }
}
