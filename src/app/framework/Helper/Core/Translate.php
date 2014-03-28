<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Config as Config;
use \MattyG\Framework\Core\Helper\HelperInterface as Helper;

class Translate implements Helper
{
    const DIR_LOCALE = "locale";

    /**
     * @var MattyG\Framework\Core\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $translationDir;

    /**
     * @var array
     */
    protected $translations;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @param Config $config
     * @param string $helperName
     * @param bool $strict
     */
    public function __construct(Config $config, $helperName, $strict = false)
    {
        $this->config = $config;
        $this->strict = $strict;
        $this->locale = $this->config->getConfig("site/locale");
        $this->translationDir = $this->config->getBaseDirectory() . self::DIR_LOCALE . "/";
        $this->loadTranslations();
    }

    /**
     * @param string $locale
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function loadTranslations($locale = null)
    {
        if (!is_string($locale)) {
            if ($locale === null) {
                $locale = $this->locale;
            } elseif (is_object($locale) && method_exists($locale, "__toString")) {
                $locale = (string) $locale;
            } else {
                throw new \InvalidArgumentException("Invalid locale supplied.");
            }
        }
        $localeDir = $this->translationDir . $locale . "/";
        if (!file_exists($localeDir)) {
            throw new \RuntimeException("Locale $locale does not exist.");
        }
        if (!is_readable($localeDir)) {
            throw new \RuntimeException("Unable to read translations for $locale locale.");
        }
        $this->translations = array();

        $files = glob($localeDir . "*.json");
        foreach ($files as $file) {
            if (!is_readable($file)) {
                if ($this->strict) {
                    throw new \RuntimeException("Translation file $file is not readable.");
                } else {
                    continue;
                }
            }
            $translations = file_get_contents($file);
            $translations = json_decode($translations, true);
            if (!is_array($translations)) {
                if ($this->strict) {
                    throw new \RuntimeException("Translation file $file contains invalid JSON.");
                } else {
                    continue;
                }
            }
            $this->translations = array_merge($this->translations, $translations);
        }
    }

    /**
     * @param string $key
     * @param array $tokens
     * @return string
     */
    public function getTranslation($key, array $tokens = array())
    {
        if (isset($this->translations[$key])) {
            return vsprintf($this->translations[$key], $tokens);
        } else {
            return vsprintf($key, $tokens);
        }
    }

    /**
     * Alias for getTranslation()
     *
     * @param string $key
     * @param array $tokens
     * @return string
     */
    public function __($key, array $tokens = array())
    {
        return $this->getTranslation($key, $tokens);
    }
}

