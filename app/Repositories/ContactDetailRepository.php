<?php

namespace App\Repositories;

use App\Contracts\InfusionSoftContactDetailContract;

class ContactDetailRepository{

    public function getContactDetail(InfusionSoftContactDetailContract $infusionSoftContactDetailContract, $email){
        return $infusionSoftContactDetailContract->getContactsFromInfusionsoftApi($email);
    }
}
