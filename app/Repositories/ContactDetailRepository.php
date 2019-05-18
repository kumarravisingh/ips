<?php

namespace App\Repositories;

use App\Contracts\InfusionsoftAddTagContract;
use App\Contracts\InfusionSoftContactDetailContract;
use App\Module;
use App\Tag;
use App\User;

class ContactDetailRepository{

    public function getContactDetail(InfusionSoftContactDetailContract $infusionSoftContactDetailContract, $email){
        return $infusionSoftContactDetailContract->getContactsFromInfusionsoftApi($email);
    }

    public function getLastModule($email, $products){
        // Todo: Check user watched any module if not assign first one
         if($this->userHasWatcheedAnyModule($email)){

            $module = $this->getModuleToAttach($email, $products);
         }else{
             $module = $this->getFirstModule($products);
         }
         return $module;
    }

    public function getModuleToAttach($email, $products){

        foreach ($products as $product){

            $allIdOfModuleWithThisProduct = Module::where('course_key', $product)->get();

            $completedModuleOfThisProduct = User::whereEmail($email)->first()
               ->completed_modules(function ($query) use($allIdOfModuleWithThisProduct){
                return $query->whereIn('module_id', $allIdOfModuleWithThisProduct->pluck('id'))->get();
            })->get();

           if(! $this->moveToNextProduct($allIdOfModuleWithThisProduct, $completedModuleOfThisProduct)){
               return $this->getNextModule($allIdOfModuleWithThisProduct, $completedModuleOfThisProduct);
           }
        }
        return false;
    }

    public function moveToNextProduct($allModulesOfProduct, $completedModulesOfProduct){
        if($allModulesOfProduct->max('id') == $completedModulesOfProduct->max('id')){
            return true;
        }else{
            return false;
        }
    }

    public function getNextModule($allModulesOfProduct, $completedModulesOfProduct){
        $maxIdOfCompletedModule = $completedModulesOfProduct->max('id');
        return $allModulesOfProduct->where('id','>',$maxIdOfCompletedModule)->first();
    }

    public function userHasWatcheedAnyModule($userEmail){
        $watchedModule = User::whereEmail($userEmail)->first()->completed_modules;
        if($watchedModule->count() > 0){
            return true;
        }else{
            return false;
        }

    }

    public function getFirstModule($products){
        foreach ($products as $product){
            return Module::where('course_key', $product)->first();
        }
    }

    public function attachModule(InfusionsoftAddTagContract $infusionsoftAddTagContract, $module, $contact){
        if($module == false){
            $tag = Tag::where('name','like', '%'.'Module reminders completed'.'%')->first();
        }else{
            $tag = Tag::where('name','like', '%'.$module->name.'%')->first();
        }
        return $infusionsoftAddTagContract->addTagsUsingInfusionsoftApi($contact['Id'], $tag->id);


    }
}
