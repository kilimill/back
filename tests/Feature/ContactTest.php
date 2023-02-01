<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use DatabaseMigrations;

    private Hotel $hotel;
    private Contact $contact;

    public function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->hotel = Hotel::factory()->for($owner)->create();
        $this->contact = Contact::factory()->for($this->hotel)->create();
        $this->userLogin($owner);
    }

    public function testRemoveContact(): void
    {
        $this->assertDatabaseHas((new Contact())->getTable(), [
            'id' => $this->contact->getKey(),
        ]);

        $this->deleteJson(route('api.hotels.contacts.remove', [
            'hotel' => $this->hotel,
            'contact' => $this->contact,
        ]))->assertOk();

        $this->assertDatabaseMissing((new Contact())->getTable(), [
            'id' => $this->contact->getKey(),
        ]);

        $this->assertDatabaseHas((new Hotel())->getTable(), [
            'id' => $this->hotel->getKey(),
            'status_id' => Hotel::STATUS_ID_UNDER_REVIEW,
        ]);
    }

    public function testRemoveNotExistsHotel()
    {
        $this->deleteJson(route('api.hotels.contacts.remove', [
            'hotel' => 100500,
            'contact' => $this->contact,
        ]))->assertNotFound();
    }

    public function testRemoveNotAuthUser()
    {
        $this->userLogOut();
        $this->deleteJson(route('api.hotels.contacts.remove', [
            'hotel' => $this->hotel,
            'contact' => $this->contact,
        ]))->assertUnauthorized();
    }

    public function testRemoveNotExistsContact()
    {
        $this->deleteJson(route('api.hotels.contacts.remove', [
            'hotel' => $this->hotel,
            'contact' => 100500,
        ]))->assertNotFound();
    }

    public function testRemoveNotOwnerUser()
    {
        $notOwner = User::factory()->create();
        $this->userLogin($notOwner);

        $this->deleteJson(route('api.hotels.contacts.remove', [
            'hotel' => $this->hotel,
            'contact' => $this->contact,
        ]))->assertNotFound();
    }
}
