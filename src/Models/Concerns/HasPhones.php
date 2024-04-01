<?php

namespace Hichxm\LaravelPhones\Models\Concerns;

use Hichxm\LaravelPhones\Models\Phone;
use Hichxm\LaravelSortable\Traits\HasSortableColumn;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Trait HasPhones
 *
 * @mixin Model
 */
trait HasPhones
{

    public static function bootHasPhones(): void
    {
        if (method_exists(self::class, 'forceDeleting')) {
            self::forceDeleting(function (Model $model) {
                $model->phones()->delete();
            });
        } elseif (method_exists(self::class, 'forceDeleted')) {
            self::forceDeleted(function (Model $model) {
                $model->phones()->delete();
            });
        } else {
            self::deleting(function (Model $model) {
                $model->phones()->delete();
            });
        }
    }

    /**
     * @return MorphMany<Phone>
     */
    public function phones(): MorphMany
    {
        return $this
            ->unorderedPhones()
            ->ordered();
    }

    /**
     * @return MorphMany<Phone>
     */
    public function unorderedPhones(): MorphMany
    {
        return $this->morphMany($this->getPhoneModelClass(), 'phoneable');
    }

    /**
     * @return Phone|null
     */
    public function getFirstPhone(): Phone|null
    {
        /** @var Phone $phone */
        $phone = $this->phones()->first();

        return $phone;
    }

    /**
     * @return Collection<Phone>
     */
    public function getPhones(string $type = null, bool $ordered = true, string $orderDirection = 'asc'): Collection
    {
        return $this
            ->unorderedPhones()
            ->when($ordered, fn($query) => $query->ordered($orderDirection))
            ->when($type, fn($query) => $query->where('type', $type))
            ->get();
    }

    /**
     * Add a phone to the model.
     *
     * @param string $number
     * @param array $country
     * @param string|null $type
     * @return Phone
     */
    public function addPhone(string $number, array $country = [], string $type = null): Phone
    {
        // If the PhoneNumber class has a method ofCountry, use it.
        if(method_exists(PhoneNumber::class, 'ofCountry')) {
            $phoneNumber = (new PhoneNumber($number))->ofCountry($country);
        } else {
            $phoneNumber = new PhoneNumber($number, $country);
        }

        /** @var Phone $phone */
        $phone = $this->phones()->create([
            'phone_e164' => (string) $phoneNumber,
            'type' => $type,
        ]);

        return $phone;
    }

    /**
     * @see HasSortableColumn::setNewOrder()
     *
     * @param $ids
     * @param int $startIndex
     * @param callable|null $customizeQuery
     * @return void
     */
    public function reorderPhones($ids, int $startIndex = 1, callable $customizeQuery = null): void
    {
        $this->getPhoneModel()::setNewOrder($ids, $startIndex, $customizeQuery);
    }

    /**
     * @return Phone
     * @throws BindingResolutionException
     */
    protected function getPhoneModel(): Phone
    {
        return app()->make($this->getPhoneModelClass());
    }

    /**
     * @return string
     */
    protected function getPhoneModelClass(): string
    {
        return config('laravel-phones.model');
    }
}