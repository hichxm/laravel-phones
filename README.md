# Laravel Phones

Laravel Phones is a package that provides a simple way to manage phones numbers related to models 
in your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require hichxm/laravel-phones
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Hichxm\LaravelPhones\LaravelPhonesServiceProvider" --tag="config"
```

You need to publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Hichxm\LaravelPhones\LaravelPhonesServiceProvider" --tag="migrations"
```

After the migration has been published you can create the phones table by running the migrations:

```bash
php artisan migrate
```

## Usage

First, add the `HasPhones` trait to the model you want to associate phones.

```php
use Hichxm\LaravelPhones\Traits\HasPhones;

class User extends Model
{
    use HasPhones;
}
```

Then, you can use the `phones` relationship to manage the phones numbers.

```php
$user = User::find(1);

$user->phones()->create([
    'number' => '1234567890',
    'type' => 'mobile',
]);

$user->addPhone('+33987654321');
$user->addPhone('0987654321', ['FR']); // with country code, will be stored as +33987654321
$user->addPhone('+33612345678', [], 'mobile'); // with type
```