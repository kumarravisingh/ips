<?php

namespace App\Contracts;

interface InfusionSoftContactDetailContract {
    function getContactsFromInfusionsoftApi($email);
}
