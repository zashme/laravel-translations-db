<?php namespace Zash\Translation;

use Illuminate\Translation\FileLoader;

class ServiceProvider extends \Illuminate\Translation\TranslationServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

	protected $commands = [
		'Zash\Translation\Console\Commands\DumpCommand',
		'Zash\Translation\Console\Commands\FetchCommand',
	];

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/translation-db.php', 'translation-db');

        $this->registerDatabase();
        $this->registerLoader();

        $this->commands($this->commands);

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];
            $database = $app['translation.database'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($database, $loader, $locale, $app);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });


    }


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('/migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../config/translation-db.php' => config_path('translation-db.php'),
        ]);


        $this->app['translation.database']->addNamespace(null, null);
    }


    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });
    }


    protected function registerDatabase()
    {
        $this->app->singleton('translation.database', function ($app) {
            return new DatabaseLoader($app);
        });
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('translator', 'translation.loader', 'translation.database');
    }

}
