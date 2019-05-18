<?php

namespace App\Http\Controllers;

use App\Contracts\TagContract;
use App\Http\Helpers\InfusionsoftHelper;
use App\Infusionsoft\InfusionsoftAddTag;
use App\Infusionsoft\InfusionsoftContact;
use App\Infusionsoft\InfusionsoftTag;
use App\Module;
use App\Repositories\ContactDetailRepository;
use App\Repositories\TagRepository;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Response;

class ApiController extends Controller
{

    /**
     * This function will assign module reminder to Infusionsoft for email notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignModuleReminder(Request $request){
//        $infusionsoftHelper = new InfusionsoftHelper();
//        return $infusionsoftHelper->getContact('5cdf24c86faeb@test.com');
        //return $this->exampleCustomer();
        $this->getTagsForAssignment(new TagRepository());
         $contact = $this->getContactDetailsFromApi(new ContactDetailRepository(), $request->email);
         $products = $this->getProductsInArrayFormat($contact['_Products']);
         $module = $this->getModuleForTagAssignment(new ContactDetailRepository(), $request->email, $products);
         $response =  $this->assignModuleUsingInfusionsoft(new ContactDetailRepository(),$module, $contact);
         return collect($response);

        return response()->json(['success'=>true]);
    }

    private function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    /**
     * function to get tags since tags won't change we save them in db
     * or if already exists in database just return them for use
     * @param TagRepository $tagRepository
     * @return Tag[]|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */

    public function getTagsForAssignment(TagRepository $tagRepository){

        return $tagRepository->getTags(new InfusionsoftTag());

        return $this;
    }

    // Todo: Get contact detail from infusinsoft by email

    public function getContactDetailsFromApi(ContactDetailRepository $contactDetailRepository, $email){

        $data = $contactDetailRepository->getContactDetail(new InfusionsoftContact(), $email);
        return $data;
        //return $this;

    }


    // Todo: Get Product from contact detail
    // Todo: Convert products to array
    public function getProductsInArrayFormat($products){
        return explode(',', $products);
    }

    public function getModuleForTagAssignment(ContactDetailRepository $contactDetailRepository, $email, $products){
        return $contactDetailRepository->getLastModule($email, $products);
    }

    public function assignModuleUsingInfusionsoft(ContactDetailRepository $contactDetailRepository, $module, $contact){
        return $contactDetailRepository->attachModule(new InfusionsoftAddTag() ,$module, $contact);
    }


}
