# The Filter Package


## Installation via Composer

Add `"joomla/filter": "dev-master"` to the require block in your composer.json, make sure you have `"minimum-stability": "dev"` and then run `composer install`.

```json
{
	"require": {
		"joomla/filter": "dev-master"
	},
	"minimum-stability": "dev"
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer init --stability="dev"
composer require joomla/filter "dev-master"
```

Note that the `Joomla\Language` package is an optional dependency and is only required if the application requires the use of `OutputFilter::stringURLSafe`.