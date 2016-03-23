Manage and display a rotating scoreboard for Freshman Engineering Design Day
***

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Install

Via Composer. Install dependencies and then run all DB migrations

``` bash
$ composer install

$ php artisan migrate
```

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Migrations

* Use `php artisan migrate:status` to see the current status of the database
* If a rollback fails after changing a migration, run `composer dump-autoload` to refresh the classes in the autoloader
