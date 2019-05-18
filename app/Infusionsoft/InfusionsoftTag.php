<?php

namespace App\Infusionsoft;

use App\Contracts\InfusionsoftTagContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftTag implements InfusionsoftTagContract
{
    /**
     * get all tags using infusionsoft helper method
     * @return \Illuminate\Support\Collection
     */
    function getTagsFromInfusionsoftApi()
    {
        $infusionsoftHelper = new InfusionsoftHelper();
        return collect($infusionsoftHelper->getAllTags());
    }
}
