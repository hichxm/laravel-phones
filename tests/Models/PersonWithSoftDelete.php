<?php

namespace Hichxm\LaravelPhones\Test\Models;

use Hichxm\LaravelPhones\Models\Concerns\HasPhones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Person
 *
 * @property int $id
 * @property string $name
 *
 */
class PersonWithSoftDelete extends Model
{
    use HasPhones;
    use SoftDeletes;

    protected $table = 'persons';

    protected $fillable = [
        'name',
    ];
}