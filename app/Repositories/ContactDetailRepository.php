<?php

namespace App\Repositories;

use App\Contracts\InfusionSoftContactDetailContract;
use App\User;

class ContactDetailRepository{

    public function getContactDetail(InfusionSoftContactDetailContract $infusionSoftContactDetailContract, $email){
        return $infusionSoftContactDetailContract->getContactsFromInfusionsoftApi($email);
    }

    public function getLastModule($email){
        // Todo: Check user watched any module if not assign first one
        return $this->userHasWatcheedAnyModule($email);
    }


    // Todo: Traverse product wise get last watched module of this product
    // Todo: If all  videos of this product watched move to next product
    // Todo: Attach tag to last module




    public function userHasWatcheedAnyModule($userEmail){
        $watchedModule = User::whereEmail($userEmail)->first()->completed_modules;
        if($watchedModule->count() > 0){
            return true;
        }else{
            return false;
        }

    }
}
