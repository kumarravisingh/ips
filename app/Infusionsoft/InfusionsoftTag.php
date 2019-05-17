<?php

namespace App\Infusionsoft;

use App\Contracts\InfusionsoftTagContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftTag implements InfusionsoftTagContract
{

    function getTagsFromInfusionsoftApi()
    {
        $infusionsoftHelper = new InfusionsoftHelper();
        return collect($infusionsoftHelper->getAllTags());
    }
}
