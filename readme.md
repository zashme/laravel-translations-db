## Localization from a database


This package was created, as an **in-place replacement** for the default TranslationServiceProvider and mend for everyone who got tired of collecting translations in language files and maintaining dozens
of arrays, filled with lots and lots of keys. Keys will be added to the database **automatically**, so no more hussling with
adding your keys to the translations file. You'll never forget to translate a key anymore! In production your keys will be **cached** to ensure the localization stays fast!


## Installation

Require this package with composer:

```
composer require zash/laravel-translation-db
```
> Like to live on the edge?
> Use: ```composer require 'zash/laravel-translations-db:*@dev'```

After updating composer, we'll have to replace the TranslationServiceProvider the our ServiceProvider in config/app.php.

Find:
```
'Illuminate\Translation\TranslationServiceProvider',
```
and Replace this with:
```
'Zash\Translation\ServiceProvider',
```

The ServiceProvider will now be loaded. To use this package properly you'll also need the create a table in your database,
this can be achieved by publishing the config and migration of this package.

Run the following command:
```
php artisan vendor:publish --provider='Zash\Translation\ServiceProvider'
```
and afterwards run your migrations:
```
php artisan migrate
```

And that's all there is to it!

## Usage
You can just start using translations as you would normaly do with the Laravels default package. The functions ```trans()``` and ```Lang::get()``` can be used as you normaly would.
> For more information about Localization and the usage of these functions, please refer to the [Documentation](http://laravel.com/docs/5.1/localization) on the mather.

### Files are still possible
The usage of translation files is however still possible. Every translation prefixed by a namespace is parsed through the old
TranslationServiceProvider. This is needed for external packages that come with there own translation files. But in general
you shouldn't be bothered by this.

## Importing and exporting
To ease the proces of migrating to translations in the database, two commands are available since version 0.3.
### Import
To import all translations out of your current language files, you can use the command:
```
php artisan translation:fetch
```
This will import all available translations in language files into the database.
> To import just some specifics you can also make use of the options ```--locale``` and ```--group```.

### Export
To dump the translations from your database back to your filesystem, use:
```
php artisan translation:dump
```
> The options ```--locale``` and ```--group``` are also available for this command.
> **Caution**: all current files will be **overwritten**, so use with care!
