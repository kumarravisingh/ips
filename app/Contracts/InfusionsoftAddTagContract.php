<?php

namespace App\Contracts;

interface InfusionsoftAddTagContract {
    function addTagsUsingInfusionsoftApi($contactId, $tagId);
}
