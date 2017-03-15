# Laravel 5 Service for Fraser Institute

This repository using [dweidner/laravel-goutte](https://github.com/dweidner/laravel-goutte) to provide a service in [Laravel 5](http://laravel.com) for getting school ranking data from [Fraser Institute](https://www.fraserinstitute.org/school-performance). 

## Installing using [Composer](https://getcomposer.org/)

```sh
$ composer require goviafang/laravel-fraser
```

Add the service provider to your config/app.php file:

```php
// config/app.php

return [
    // ...
    
    'providers' => [
    
        // ...
        Weidner\Goutte\GoutteServiceProvider::class,
        Govia\Fraser\FraserServiceProvider::class,
    ],
    
    
    'aliase' => [
    
        // ...
        'Goutte' => Weidner\Goutte\GoutteFacade::class,
    ],
],
```

Publish fraser.php to your config:

```sh
php artisan vendor:publish --tag=fraser
```

## Usage

```php
Route::get('fraser', function (\Govia\Fraser\Fraser $fraser) {

    $records = $fraser->setProvince('on')
        ->setGrade('elementary')
        ->getList();

    $detail = $fraser->getDetail($records->first()->link);

    dd(
        $records->first(),
        $detail
    );
});
```