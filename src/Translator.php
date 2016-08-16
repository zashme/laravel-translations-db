<?php namespace Zash\Translation;;

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

    /**
     * @var DatabaseLoader|null
     */
    protected $database = null;


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

        // Get the locale that should be used
        foreach ($this->parseLocale($locale) as $l) {
            $this->load($namespace, $group, $l);
            $line = $this->getLine($namespace, $group, $l, $item, $replace);
            if (! is_null($line)) {
                break;
            }
        }

        return isset($line) ? $line : $key;
    }


    /**
     * @param string $namespace
     * @param string $group
     * @param string $locale
     */
    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        // Load cached or from database
        if (\Config::get('translation-db.use_cache')) {
            $this->loaded[$namespace][$group][$locale] = $this->loadCached($locale, $group, $namespace);
            return;
        }

        $this->loaded[$namespace][$group][$locale] = $this->database->load($locale, $group, $namespace);
    }


    /**
     * @param string $locale
     * @param string $group
     * @param string $namespace
     *
     * @return string
     */
    protected function loadCached($locale, $group, $namespace)
    {
        $cacheIdentifier = \Config::get('translation-db.cache_prefix') . '.' . $locale . '.' . $group;

        return \Cache::tags(\Config::get('translation-db.cache_tag'))
            ->rememberForever($cacheIdentifier, function () use ($locale, $group, $namespace) {
                return $this->database->load($locale, $group, $namespace);
            });
    }

}
