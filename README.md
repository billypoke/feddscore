Manage and display a rotating scoreboard for Freshman Engineering Design Day
***

## Install

Add PHP 5.6
```bash
$ add php56
```

Via Composer. Install dependencies, 
``` bash
$ composer install
```

Then copy `.env.example` in the root to `.env` and fill in the database credentials as well as the `ALLOWED_USERS`,
```bash
$ cp .env.example .env
```

And then run all DB migrations.
```bash
$ php artisan migrate
```

Then set up CSRF (Cross Site Request Forgery) protection by running
```bash
$ php artisan key:generate
```

Currently, a find/sed command is required to deploy the application into
the web environment. This command goes through the vendor folder and prepends
any ini_set command with @ to suppress errors in PHP configurations that
disallow the use of ini_set. It's not included as a post-update or
post-install command for composer because composer is not given enough
time to run the command due to environment restrictions on execution time.

```bash                                                         
find ./vendor -type f -exec sed -i 's/@*ini_set/@ini_set/g' {} \;
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
