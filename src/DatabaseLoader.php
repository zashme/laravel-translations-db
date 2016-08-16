<?php namespace Zash\Translation;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Translation\LoaderInterface;

class DatabaseLoader implements LoaderInterface {

    protected $_app = null;

    public function __construct(Application $app)
    {
        $this->_app = $app;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        return \DB::connection(\Config::get('translation-db.database'))->table('translations')
            ->where('locale', $locale)
            ->where('group', $group)
            ->lists('value', 'name');
    }

    /**
     * Add a new namespace to the loader.
     * This function will not be used but is required
     * due to the LoaderInterface.
     * We'll just leave it here as is.
     *
     * @param  string  $namespace
     * @param  string  $hint
     * @return void
     */
    public function addNamespace($namespace, $hint) {}

    /**
     * Adds a new translation to the database
     *
     * @param string $locale
     * @param string $group
     * @param string $key
     * @return void
     */
    public function addTranslation($locale, $group, $key)
    {
        // Extract the real key from the translation.
        if (preg_match("/^{$group}\.(.*?)$/sm", $key, $match)) {
            $name = $match[1];
        } else {
            throw new TranslationException('Could not extract key from translation.');
        }

        $item = \DB::connection(\Config::get('translation-db.database'))->table('translations')
            ->where('locale', $locale)
            ->where('group', $group)
            ->where('name', $name)->first();

        if($item === null) {
            $data = compact('locale', 'group', 'name');
            $data = array_merge($data, ['updated_at' => date_create(), 'created_at' => date_create()]);
            \DB::connection(\Config::get('translation-db.database'))->table('translations')->insert($data);
        }
    }
}
