<?php

namespace Tests\Unit;

use App\Module;
use App\Repositories\ContactDetailRepository;
use App\User;
use Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactDetailTest extends TestCase
{
    use RefreshDatabase;
    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        Artisan::call('db:seed');
    }
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

    public function testUserHaveNotWatchedAnyModule(){
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        $contactRepositoryObject = new ContactDetailRepository();
        $watchedModuleStatus = $contactRepositoryObject->userHasWatcheedAnyModule($user->email);
        $this->assertEquals(false, $watchedModuleStatus);
    }

    public function testUserHasWatchedModule(){
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());

        $contactRepositoryObject = new ContactDetailRepository();
        $watchedModuleStatus = $contactRepositoryObject->userHasWatcheedAnyModule($user->email);
        $this->assertEquals(true, $watchedModuleStatus);
    }
}
