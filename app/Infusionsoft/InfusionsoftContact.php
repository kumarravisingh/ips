<?php

namespace App\Infusionsoft;

use App\Contracts\InfusionSoftContactDetailContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftContact implements InfusionSoftContactDetailContract
{

    /**
     * get contact detail from infusionsoft helper method
     * @param $email
     * @return bool
     */
    function getContactsFromInfusionsoftApi($email)
    {
        $infusionsoftHelper = new InfusionsoftHelper();
        return $infusionsoftHelper->getContact($email);
    }
}
