[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

# Laravel PLogs

Laravel package to save logs in database making them permanent, always available for as long as you want.

## Screenshot

![Main Window](https://github.com/sarfraznawaz2005/plogs/blob/master/screen.jpg?raw=true)

## Requirements

 - PHP >= 5.6
 - Laravel 5

## Installation

Via Composer

``` bash
$ composer require sarfraznawaz2005/plogs
```

For Laravel < 5.5:

Add Service Provider to `config/app.php` in `providers` section
```php
Sarfraznawaz2005\PLogs\ServiceProvider::class,
```

---

Publish package's config and migration files by running below command:

```bash
$ php artisan vendor:publish --provider="Sarfraznawaz2005\PLogs\ServiceProvider"
```

Run `php artisan migrate` to create `plogs` table in your database.

Now application logs can be seen at `http://yourapp.com/plogs`. See `config/plogs.php` config file to customize route and more settings.

## Credits

- [Sarfraz Ahmed][link-author]
- [All Contributors][link-contributors]

## License

Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sarfraznawaz2005/plogs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sarfraznawaz2005/plogs.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/sarfraznawaz2005/plogs
[link-downloads]: https://packagist.org/packages/sarfraznawaz2005/plogs
[link-author]: https://github.com/sarfraznawaz2005
[link-contributors]: https://github.com/sarfraznawaz2005/plogs/graphs/contributors