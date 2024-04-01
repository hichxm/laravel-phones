<?php

namespace Hichxm\LaravelPhones\Test\Models;

use Hichxm\LaravelPhones\Models\Concerns\HasPhones;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Person
 *
 * @property int $id
 * @property string $name
 *
 */
class Person extends Model
{
    use HasPhones;

    protected $table = 'persons';

    protected $fillable = [
        'name',
    ];
}