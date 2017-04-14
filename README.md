# Feddscore
Manage and display a rotating scoreboard for Freshman Engineering Design Day
***

## Route Explanations
* `/` is the base route and redirects to the current year's advertisement

* `/dashboard` is the default route and determines the page to show based on the internal state of the application. The
    function `DashboardController@getCurrent` does the following when determining which page to show

    * If the current year has competitions in it, then the page shown is based on the current date
        * If Design Day is currently happening, the repeater is shown
        * If Design Day has yet to happen, the advertisement is shown
        * If Design Day has already happened, the final scores are shown
    * If the current year does NOT have competitions, the hall of fame page is shown

* `/dashboard/{year}/blah` will override this behavior to show a specific view, where `blah` is one of:
    * `advert`: show the advertisement for the current year
    * `repeater`: show the repeater for the provided year. NOTE: Will only show competitions that are `active`
    * `final`: show the final scores for the provided year
    * `hall-of-fame`: hide the non-placing scores with a css/jquery expander

    NOTE: `advert` and `repeater` are less useful outside the current year, since `advert` ignores the provided year and
    `repeater` only shows `active` competitions, and the `admin` page only edits the current year's competitions. This
    means that database edits would be required to show a previous year's repeater after the fact.

* `/admin` is the administrative interface for adding, removing, and editing teams and competitions for the current year
    * NOTE: This route is protected by Shibboleth, but there is currently no access list for it

* `/competition/{id}` is the page that show when editing a competition, it should not be accessed directly, but through
    the `/admin` route above
    * NOTE: This route is also protected by Shibboleth, with the same caveat

## Install

1st, fork the repository to your own repository, then clone the fork.
```bash
$ git clone <github_URL> feddscore
```

SSH into the engr-ras-web server. Doing this prevents future errors from commands below.
```bash
$ ssh engr-ras-web.eos.ncsu.edu
```

Once inside the engr-ras-web server, navigate to your project directory inside Terminal.
```bash
$ cd <your_project_directory_path>
```

Then add PHP 5.6
```bash
$ add php56
```

Via Composer. Install dependencies, 
``` bash
$ composer install
```

Copy `.env.example` in the root to `.env`.
```bash
$ cp .env.example .env
```

Then, open the `.env` file
```bash
$ gedit .env
```
* Starting on line 6, fill out the `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
by replacing `homestead` and `secret` with the appropriate data.
* Note, you do not need to place any single/double quotes around your information.
* On line 28, edit `ALLOWED_USERS` by replacing `user1 user2` with your unity id.


* Optionally, below `DB_PASSWORD`, add `DB_PORT=` and fill this out.
* Optionally, change `CACHE_DRIVER=file` to `CACHE_DRIVER=array` if you want to disable caching.


## Migrations

After editing and saving the `.env` file, run all DB migrations.
```bash
$ php artisan migrate
```

Then set up CSRF (Cross Site Request Forgery) protection by running
```bash
$ php artisan key:generate
```

* Use `php artisan migrate:status` to see the current status of the database
* If a rollback fails after changing a migration, run `composer dump-autoload`
to refresh the classes in the autoloader

## Fix ini_set() issues
Currently, a find/sed command is required to deploy the application into
the web environment. This command goes through the vendor folder and prepends
any ini_set command with @ to suppress errors in PHP configurations that
disallow the use of ini_set. It's not included as a post-update or
post-install command for composer because composer is not given enough
time to run the command due to environment restrictions on execution time.
```bash                                                         
$ find ./vendor -type f -exec sed -i 's/@*ini_set/@ini_set/g' {} \;
$ find ./bootstrap -type f -exec sed -i 's/@*ini_set/@ini_set/g' {} \;
```

## URL Routing

After completing the previous steps, please note that in development,
you must specify the url path by using:
```bash
$ php artisan route:list
```

* For example, using `...feddscore/` or `..feddscore/index.php/` will *not* work
and will result in some sort of an error.
* However, using `...feddscore/index.php/admin` or `...feddscore/index.php/dashboard` will work.

## Testing

*WARNING!* Using the following command will undo the migrations from above.
``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
