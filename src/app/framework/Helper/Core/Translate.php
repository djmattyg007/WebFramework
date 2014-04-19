<?php

namespace MattyG\Framework\Helper\Core;

use \MattyG\Framework\Core\Config as Config;
use \MattyG\Framework\Core\Helper\HelperInterface as Helper;

class Translate implements Helper
{
    const LOCALE_CACHE_ENTRY_NAME = "core_locale";

    const DIR_LOCALE = "locale";

    /**
     * @var \MattyG\Framework\Core\Config
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
     * @param \MattyG\Framework\Core\Config $config
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
     * Save all available translations into the cache.
     *
     * @return void
     */
    protected function saveLocaleCache()
    {
        if (!($cache = $this->config->getCacheObject())) {
            return;
        }
        $cache->saveData(self::LOCALE_CACHE_ENTRY_NAME, json_encode($this->translations), (time() + 3600));
    }

    /**
     * Load translations from the cache so we don't need to read them all from
     * disk individually.
     *
     * @return array|null
     */
    protected function loadLocaleCache()
    {
        if (!($cache = $this->config->getCacheObject())) {
            return null;
        }
        return json_decode($cache->loadData(self::LOCALE_CACHE_ENTRY_NAME, null), true);
    }

    /**
     * Read in all translations for a given locale. Defaults to the locale
     * specified in the configuration tree.
     * Optionally checks to see if translations are available in the cache
     * first, and if so, uses that rather than re-read all translations from
     * the disk again.
     *
     * @param string $locale
     * @param bool $useCache
     * @return void
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function loadTranslations($locale = null, $useCache = true)
    {
        if ($useCache === true && ($translations = $this->loadLocaleCache())) {
            $this->translations = $translations;
            return;
        }

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
        $this->saveLocaleCache();
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

