<?php

namespace App\Infusionsoft;

use App\Contracts\InfusionsoftAddTagContract;
use App\Http\Helpers\InfusionsoftHelper;

class InfusionsoftAddTag implements InfusionsoftAddTagContract
{
    /**
     * Call infusionsoft helper to add tag
     * @param $contactId
     * @param $tagId
     * @return bool
     */
    function addTagsUsingInfusionsoftApi($contactId, $tagId){
        $infusionsoftHelper = new InfusionsoftHelper();
        return $infusionsoftHelper->addTag($contactId, $tagId);
    }
}
