<?php namespace Aktiweb\Translation;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Translation\LoaderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Translator
 * @package Aktiweb\Translation
 */
class Translator extends \Illuminate\Translation\Translator implements TranslatorInterface {

    /**
     * @var Application|null
     */
    protected $app = null;


    public function __construct(LoaderInterface $database, LoaderInterface $loader, $locale, Application $app)
    {
        $this->database = $database;
        $this->app = $app;
        parent::__construct($loader, $locale);
    }


    /**
     * @param $namespace
     * @return bool
     */
    protected static function isNamespaced($namespace)
    {
        return !(is_null($namespace) || $namespace == '*');
    }


    /**
     * Get the translation for the given key.
     *
     * @param  string $key
     * @param  array $replace
     * @param  string $locale
     * @param  bool $fallback
     * @return string
     */
    public function get($key, array $replace = array(), $locale = null, $fallback = true)
    {
        if (is_null($key)) {
            return '';
        }

        list($namespace, $group, $item) = $this->parseKey($key);

        // Here we will get the locale that should be used for the language line. If one
        // was not passed, we will use the default locales which was given to us when
        // the translator was instantiated. Then, we can load the lines and return.
        foreach ($this->parseLocale($locale) as $locale) {
            if (!self::isNamespaced($namespace)) {
                // Database stuff
                $this->database->addTranslation($locale, $group, $key);
            }

            $this->load($namespace, $group, $locale);

            $line = $this->getLine($namespace, $group, $locale, $item, $replace);

            if (! is_null($line)) {
                break;
            }
        }

        if (! isset($line)) {
            return $key;
        }

        return $line;
    }


    /**
     * @param string $namespace
     * @param string $group
     * @param string $locale
     */
    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) return;

        if (!self::isNamespaced($namespace)) {
            if (\Config::get('translation-db.use_cache')) {
                $cacheIdentifier = \Config::get('translation-db.cache_prefix') . '.' . $locale . '.' . $group;
                $lines = \Cache::tags(\Config::get('translation-db.cache_tag'))
                    ->rememberForever($cacheIdentifier, function () use ($locale, $group, $namespace) {
                        return $this->database->load($locale, $group, $namespace);
                    });
            }
            else {
                $lines = $this->database->load($locale, $group, $namespace);
            }
        }
        else {
            $lines = $this->loader->load($locale, $group, $namespace);
        }
        $this->loaded[$namespace][$group][$locale] = $lines;
    }

}
