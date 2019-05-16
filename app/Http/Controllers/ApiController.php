<?php

namespace App\Http\Controllers;

use App\Contracts\TagContract;
use App\Http\Helpers\InfusionsoftHelper;
use App\Repositories\InfusionsoftRepository;
use App\Repositories\TagRepository;
use App\Tag;
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

         $this->getTagsForAssignment(new TagRepository());
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

        $tags  = $tagRepository->getTags(new InfusionsoftRepository());

        return $tags->all();
    }

}
