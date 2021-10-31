<?php

namespace App\Models;

class Continent
{
    private $continent;

    public function __construct($continent)
    {
        $this->continent = $continent;
    }

    /**Get all phone codes of the continent
     *
     */
    public function getPhoneCodes()
    {

    }
}