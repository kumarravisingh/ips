<?php

namespace App\Repositories;

use App\Contracts\InfusionsoftContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftRepository implements InfusionsoftContract
{

    function getTagsFromInfusionsoftApi()
    {
        $infusionsoftHelper = new InfusionsoftHelper();
        return collect($infusionsoftHelper->getAllTags());
    }
}
