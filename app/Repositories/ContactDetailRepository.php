<?php

namespace App\Repositories;

use App\Contracts\InfusionsoftAddTagContract;
use App\Contracts\InfusionSoftContactDetailContract;
use App\Module;
use App\Tag;
use App\User;

class ContactDetailRepository{

    /**
     * get contact details
     * @param InfusionSoftContactDetailContract $infusionSoftContactDetailContract
     * @param $email
     * @return mixed
     */
    public function getContactDetail(InfusionSoftContactDetailContract $infusionSoftContactDetailContract, $email){
        return $infusionSoftContactDetailContract->getContactsFromInfusionsoftApi($email);
    }

    /**
     * get last module for tag assignment
     * @param $email
     * @param $products
     * @return bool|mixed
     */
    public function getLastModule($email, $products){
         if($this->userHasWatcheedAnyModule($email)){

            $module = $this->calculateModuleToAttach($email, $products);
         }else{
             $module = $this->getFirstModule($products);
         }
         return $module;
    }

    /**
     * iterate over products and their modules to find next module to attach
     * @param $email
     * @param $products
     * @return bool|mixed
     */
    public function calculateModuleToAttach($email, $products){

        foreach($products as $product){

            $allIdOfModuleWithThisProduct = Module::where('course_key', $product)->get();

            $completedModuleOfThisProduct = $this->getCompletedModuleOfProduct($email, $allIdOfModuleWithThisProduct);

            if($completedModuleOfThisProduct->count() == 0 ){
                return  $this->getFirstModule($product);
            }

           if(! $this->moveToNextProduct($allIdOfModuleWithThisProduct, $completedModuleOfThisProduct)){
               return $this->getNextModule($allIdOfModuleWithThisProduct, $completedModuleOfThisProduct);
           }
        }
        return false;
    }

    /**
     * get all completed modules of given product
     * @param $email
     * @param $allIdOfModuleWithThisProduct
     * @return mixed
     */
    public function getCompletedModuleOfProduct($email, $allIdOfModuleWithThisProduct){
       return User::whereEmail($email)->first()
           ->completed_modules()->whereIn('module_id',$allIdOfModuleWithThisProduct->pluck('id'))->get();
    }

    /**
     * Find out moving to next module or assigning tag to module of this product
     * @param $allModulesOfProduct
     * @param $completedModulesOfProduct
     * @return bool
     */
    public function moveToNextProduct($allModulesOfProduct, $completedModulesOfProduct){
        if($allModulesOfProduct->count() == $completedModulesOfProduct->count()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get next module from the list of all module collection of current product for tag assignment
     * @param $allModulesOfProduct
     * @param $completedModulesOfProduct
     * @return mixed
     */
    public function getNextModule($allModulesOfProduct, $completedModulesOfProduct){
        $maxIdOfCompletedModule = $completedModulesOfProduct->max('id');
       $nextModuleId = $allModulesOfProduct->where('id','>',$maxIdOfCompletedModule)->first();
       return $nextModuleId;
    }


    /**
     * Check if user has watched any module
     * @param $userEmail
     * @return bool
     */
    public function userHasWatcheedAnyModule($userEmail){
        $watchedModule = User::whereEmail($userEmail)->first()->completed_modules;
        if($watchedModule->count() > 0){
            return true;
        }else{
            return false;
        }

    }

    /**
     * get first module of the given product
     * @param $products
     * @return mixed
     */
    public function getFirstModule($product){
            return Module::where('course_key', $product)->first();
    }

    /**
     * attach module tag to infusionsoft
     * @param InfusionsoftAddTagContract $infusionsoftAddTagContract
     * @param $module
     * @param $contact
     * @return mixed
     */
    public function attachModule(InfusionsoftAddTagContract $infusionsoftAddTagContract, $module, $contact){
        if($module == false){
            $tag = Tag::where('name','like', '%'.'Module reminders completed'.'%')->first();
        }else{
            $tag = Tag::where('name','like', '%'.$module->name.'%')->first();
        }
        return $infusionsoftAddTagContract->addTagsUsingInfusionsoftApi($contact['Id'], $tag->id);

    }
}
