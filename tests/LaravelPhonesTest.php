<?php

namespace Hichxm\LaravelPhones\Test;

use Hichxm\LaravelPhones\Models\Phone;
use Hichxm\LaravelPhones\Test\Models\PersonWithSoftDelete;
use Illuminate\Database\Schema\Blueprint;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

class LaravelPhonesTest extends TestCase
{
    public function test_it_can_create_a_phone()
    {
        $person = $this->createDummyPerson();

        $this->assertDatabaseCount('phones', 0);

        $person->addPhone('+33612345678');

        $this->assertDatabaseCount('phones', 1);
    }

    public function test_it_can_create_a_phone_with_country()
    {
        $person = $this->createDummyPerson();

        $this->assertDatabaseCount('phones', 0);

        $person->addPhone('0612345678', ['FR']);

        $this->assertDatabaseHas('phones', [
            'phone_e164' => '+33612345678',
        ]);
    }

    public function test_it_can_create_a_phone_with_type()
    {
        $person = $this->createDummyPerson();

        $this->assertDatabaseCount('phones', 0);

        $person->addPhone('+33612345678', [], 'mobile');

        $this->assertDatabaseHas('phones', [
            'type' => 'mobile',
        ]);
    }

    public function test_it_can_create_a_phone_with_country_and_type()
    {
        $person = $this->createDummyPerson();

        $this->assertDatabaseCount('phones', 0);

        $person->addPhone('0612345678', ['FR'], 'mobile');

        $this->assertDatabaseHas('phones', [
            'phone_e164' => '+33612345678',
            'type' => 'mobile',
        ]);
    }

    public function test_it_can_reorder_phones()
    {
        $person = $this->createDummyPerson();

        $phone1 = $person->addPhone('+33612345678');
        $phone2 = $person->addPhone('+33612345679');
        $phone3 = $person->addPhone('+33612345680');

        $person->reorderPhones([
            $phone3->id,
            $phone1->id,
            $phone2->id,
        ]);

        $this->assertEquals(1, $phone3->fresh()->order);
        $this->assertEquals(2, $phone1->fresh()->order);
        $this->assertEquals(3, $phone2->fresh()->order);
    }

    public function test_it_can_reorder_phones_with_start_index()
    {
        $person = $this->createDummyPerson();

        $phone1 = $person->addPhone('+33612345678');
        $phone2 = $person->addPhone('+33612345679');
        $phone3 = $person->addPhone('+33612345680');

        $person->reorderPhones([
            $phone3->id,
            $phone1->id,
            $phone2->id,
        ], 10);

        $this->assertEquals(10, $phone3->fresh()->order);
        $this->assertEquals(11, $phone1->fresh()->order);
        $this->assertEquals(12, $phone2->fresh()->order);
    }

    public function test_it_can_reorder_phones_with_customize_query()
    {
        $person = $this->createDummyPerson();

        $phone1 = $person->addPhone('+33612345678', [], 'mobile');
        $phone2 = $person->addPhone('+33612345679', [], 'mobile');
        $phone3 = $person->addPhone('+33612345680', [], 'mobile');
        $phone4 = $person->addPhone('+33612345681', [], 'other');

        $person->reorderPhones([
            $phone3->id,
            $phone1->id,
            $phone2->id,
        ], 1, function ($query) {
            $query->where('type', 'mobile');
        });

        $this->assertEquals(1, $phone3->fresh()->order);
        $this->assertEquals(2, $phone1->fresh()->order);
        $this->assertEquals(3, $phone2->fresh()->order);
        $this->assertEquals(4, $phone4->fresh()->order);
    }

    public function test_cast_it_return_phone_number_class()
    {
        $person = $this->createDummyPerson();

        $phone = $person->addPhone('+33612345678');

        $this->assertInstanceOf(PhoneNumber::class, $phone->phone_e164);
    }

    public function test_get_first_phone()
    {
        $person = $this->createDummyPerson();

        $phone1 = $person->addPhone('+33612345678');
        $person->addPhone('+33612345679');

        $phone = $person->getFirstPhone();

        $this->assertEquals($phone1->phone_e164, $phone->phone_e164);
    }

    public function test_get_phones()
    {
        $person = $this->createDummyPerson();

        $person->addPhone('+33612345678', [], 'mobile');
        $person->addPhone('+33612345679', [], 'mobile');
        $person->addPhone('+33612345680', [], 'other');

        $phones = $person->getPhones();

        $this->assertCount(3, $phones);
    }

    public function test_get_phones_with_type()
    {
        $person = $this->createDummyPerson();

        $phone1 = $person->addPhone('+33612345678', [], 'mobile');
        $phone2 = $person->addPhone('+33612345679', [], 'mobile');
        $person->addPhone('+33612345680', [], 'other');

        $phones = $person->getPhones('mobile');

        $this->assertCount(2, $phones);
        $this->assertEquals($phone1->phone_e164, $phones->first()->phone_e164);
        $this->assertEquals($phone2->phone_e164, $phones->last()->phone_e164);
    }

    public function test_it_can_delete_a_phone()
    {
        $person = $this->createDummyPerson();

        $phone = $person->addPhone('+33612345678');

        $this->assertDatabaseCount('phones', 1);

        $phone->delete();

        $this->assertDatabaseCount('phones', 0);
    }

    public function test_it_can_delete_all_phones()
    {
        $person = $this->createDummyPerson();

        $person->addPhone('+33612345678');
        $person->addPhone('+33612345679');

        $this->assertDatabaseCount('phones', 2);

        $person->phones()->delete();

        $this->assertDatabaseCount('phones', 0);
    }

    public function test_it_can_delete_related_phones()
    {
        $person = $this->createDummyPerson();

        $person->addPhone('+33612345678');
        $person->addPhone('+33612345679');

        $this->assertDatabaseCount('phones', 2);

        $person->delete();

        $this->assertDatabaseCount('phones', 0);
    }

    public function test_it_can_delete_related_phones_with_soft_delete_and_force_deleted()
    {
        $this->app['db']->connection()->getSchemaBuilder()->table('persons', function (Blueprint $table) {
            $table->softDeletes();
        });

        $person = $this->createDummyPerson([], PersonWithSoftDelete::class);

        $person->addPhone('+33612345678');
        $person->addPhone('+33612345679');

        $this->assertDatabaseCount('phones', 2);

        $person->forceDelete();

        $this->assertDatabaseCount('phones', 0);
    }

    public function test_it_cant_delete_related_phones_with_soft_delete_and_without_force_delete()
    {
        $this->app['db']->connection()->getSchemaBuilder()->table('persons', function (Blueprint $table) {
            $table->softDeletes();
        });

        $person = $this->createDummyPerson([], PersonWithSoftDelete::class);

        $person->addPhone('+33612345678');
        $person->addPhone('+33612345679');

        $this->assertDatabaseCount('phones', 2);

        $person->delete();

        $this->assertDatabaseCount('phones', 2);
    }

    public function test_it_ordered_by_entity()
    {
        $person = $this->createDummyPerson();

        $person2 = $this->createDummyPerson();

        $person1Phone1 = $person->addPhone('+33612345678');
        $person2Phone1 = $person2->addPhone('+33612345678');
        $person1Phone2 = $person->addPhone('+33612345679');
        $person2Phone2 = $person2->addPhone('+33612345679');
        $person1Phone3 = $person->addPhone('+33612345680');
        $person2Phone3 = $person2->addPhone('+33612345680');

        $this->assertEquals(1, $person1Phone1->order);
        $this->assertEquals(2, $person1Phone2->order);
        $this->assertEquals(3, $person1Phone3->order);
        $this->assertEquals(1, $person2Phone1->order);
        $this->assertEquals(2, $person2Phone2->order);
        $this->assertEquals(3, $person2Phone3->order);
    }
}