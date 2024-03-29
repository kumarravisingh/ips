<?php

namespace Tests\Unit;

use App\Module;
use App\Repositories\ContactDetailRepository;
use App\Tag;
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







    public function testAttachingTagsToInfusionsoft(){
        $this->withExceptionHandling();
        $tag = new Tag();
        $tag->name = 'Start IPA Module 1 Reminders';
        $tag->save();
        $contactRepositoryObject = new ContactDetailRepository();
        $contactRepositoryMock = $this->createMock('App\Infusionsoft\InfusionsoftAddTag', 'App\Contracts\InfusionsoftAddTagContract');

        // sample data to mock the infusionsoft api response data
        $mockContactData = collect(json_decode('{"Email":"5cdf24c86faeb@test.com","_Products":"ipa,iea","Id":8947}'));
        $mockModuleData = Module::first();

        $contactRepositoryMock->method('addTagsUsingInfusionsoftApi')
            ->willReturn(true);

        $resultData = $contactRepositoryObject->attachModule($contactRepositoryMock, $mockModuleData, $mockContactData);

        $this->assertEquals(true, $resultData);
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


    public function testGettingFirstModuleOfFirstProductFromAnArrayOfProducts(){
        $contactRepositoryObject = new ContactDetailRepository();
        $products = 'ipa';
        $module = $contactRepositoryObject->getFirstModule($products);
        $this->assertEquals('IPA Module 1', $module->name);

    }

    public function testGettingNextModuleFromModuleCollection(){
        $contactRepositoryObject = new ContactDetailRepository();
        $allModule = Module::all();
        $completedModule = Module::take(2)->get(); // whose id will be 2
        $result = $contactRepositoryObject->getNextModule($allModule, $completedModule);
        $this->assertEquals($allModule[2]->id,$result->id); // we should get id 3 as array indexing starts from 0
    }

    public function testNotMovingToNextProduct(){
        $contactRepositoryObject = new ContactDetailRepository();
        $allModule = Module::all();
        $completedModule = Module::take(2)->get(); // whose id will be 2
        $result = $contactRepositoryObject->moveToNextProduct($allModule, $completedModule);
        $this->assertEquals(false,$result); // we should get id 3 as array indexing starts from 0
    }

    public function testMovingToNextProduct(){
        $contactRepositoryObject = new ContactDetailRepository();
        $allModule = Module::all();
        $completedModule = $allModule; //
        $result = $contactRepositoryObject->moveToNextProduct($allModule, $completedModule);
        $this->assertEquals(true,$result);
    }


    public function testGettingCompletedModuleOfProductByMatchingItWithRandomlyAttachedCompletedModule(){
        $contactRepositoryObject = new ContactDetailRepository();
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);
        $randomModuleKey = rand(1,3);
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit($randomModuleKey)->get());
        $attachedModule = Module::where('course_key', 'ipa')->limit($randomModuleKey)->pluck('id');

        $product = ['ipa'];
        $email = User::first()->email;
        $allIdOfModuleWithThisProduct = Module::where('course_key', $product)->get();
        $completedModule = $contactRepositoryObject->getCompletedModuleOfProduct($email, $allIdOfModuleWithThisProduct);
        $this->assertEquals($attachedModule, $completedModule->pluck('id'));

    }

    public function testGettingNextModuleIfThereIsUnWatchedModuleInProduct(){
        $contactRepositoryObject = new ContactDetailRepository();
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);
        $randomModuleKey = rand(1,6);
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit($randomModuleKey)->get());
        $comletedModules = User::find($user->id)->completed_modules;
        $product = ['ipa'];
        $email = User::first()->email;
        $lastModule= $contactRepositoryObject->getLastModule($email, $product);
        $this->assertEquals($comletedModules->max('id') + 3, $lastModule->id);
    }

    public function testMovingToNextProductIfAllModulesOfThisProductWatched(){
        $contactRepositoryObject = new ContactDetailRepository();
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);
        $randomModuleKey = 7;
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit($randomModuleKey)->get());
        $comletedModules = User::find($user->id)->completed_modules;
        $products = ['ipa','iea'];
        $email = User::first()->email;
        $lastModule= $contactRepositoryObject->getLastModule($email, $products);
        $this->assertEquals('iea', $lastModule->course_key);
    }

    public function testGettingFalseIfAllModulesOfAllProductsWatched(){
        $contactRepositoryObject = new ContactDetailRepository();
        $uniqid = uniqid();
        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);
        $randomModuleKey = 7;
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit($randomModuleKey)->get());
        $comletedModules = User::find($user->id)->completed_modules;
        $products = ['ipa'];
        $email = User::first()->email;
        $lastModule= $contactRepositoryObject->getLastModule($email, $products);
        $this->assertEquals(false, $lastModule);
    }


}
