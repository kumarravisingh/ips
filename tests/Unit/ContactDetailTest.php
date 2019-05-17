<?php

namespace Tests\Unit;

use App\Repositories\ContactDetailRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGettingContactDetailFromApiUsingEmail()
    {
        $contactRepositoryObject = new ContactDetailRepository();
        $contactRepositoryMock = $this->createMock('App\Infusionsoft\InfusionsoftContact', 'App\Contracts\InfusionsoftContactDetailContract');

        // sample data to mock the infusionsoft api response data
        $mockContactData = collect(json_decode('{"Email":"5cdf24c86faeb@test.com","_Products":"ipa,iea","Id":8947}'));

        $contactRepositoryMock->method('getContactsFromInfusionsoftApi')->with('5cdf24c86faeb@test.com')
            ->willReturn($mockContactData);

        $resultData = $contactRepositoryObject->getContactDetail($contactRepositoryMock, '5cdf24c86faeb@test.com');

        $this->assertEquals($resultData,$mockContactData);


    }
}
