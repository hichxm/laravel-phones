<?php

namespace Hichxm\LaravelPhones\Models;

use Carbon\Carbon;
use Hichxm\LaravelSortable\Traits\HasSortableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Class Phone
 *
 * @property int $id
 * @property string|PhoneNumber $phone_e164
 * @property string|null $type
 * @property int|null $order
 * @property string $phoneable_type
 * @property string|int $phoneable_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Phone extends Model
{
    use HasSortableColumn;

    protected $table = 'phones';

    protected $fillable = [
        'phone_e164',
        'type',
    ];

    protected $casts = [
        'phone_e164' => E164PhoneNumberCast::class,
    ];

    /**
     * @return MorphTo
     */
    public function phoneable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function laravelSortableQuery(): Builder
    {
        return $this->newQuery()
            ->when($this->type, function (Builder $query) {
                return $query->where('type', '=', $this->type);
            })
            ->when($this->phoneable_id && $this->phoneable_type, function (Builder $query) {
                return $query
                    ->where('phoneable_id', '=', $this->phoneable_id)
                    ->where('phoneable_type', '=', $this->phoneable_type);
            });
    }
}