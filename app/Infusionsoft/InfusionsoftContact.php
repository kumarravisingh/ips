<?php

namespace App\Infusionsoft;

use App\Contracts\InfusionSoftContactDetailContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftContact implements InfusionSoftContactDetailContract
{


    function getContactsFromInfusionsoftApi($email)
    {
        $infusionsoftHelper = new InfusionsoftHelper();
        return $infusionsoftHelper->getContact($email);
    }
}
