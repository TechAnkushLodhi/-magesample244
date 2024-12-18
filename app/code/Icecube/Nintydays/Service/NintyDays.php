<?php

namespace Icecube\Nintydays\Service;

use Icecube\Nintydays\Model\Completed90;

class NintyDays
{
    protected $Completed90;

    public function __construct(      
        Completed90 $Completed90
    ) {
        $this->Completed90 = $Completed90;
    }

    public function execute()
    {
        return $this->Completed90->getLoginCustomers();
    }
}
