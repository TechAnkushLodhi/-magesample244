<?php
namespace Icecube\Nintydays\Model;


use Icecube\OrderReminder\Helper\Data as IcecubeNintydaysHelper;

class Completed90
{
    protected $IcecubeNintydaysHelper;
   
    public function __construct(
       
        IcecubeNintydaysHelper $IcecubeNintydaysHelper
    ) {
       
        $this->IcecubeNintydaysHelper = $IcecubeNintydaysHelper;
    }

    /**
     * Get all orders with products created in the last 15 days.
     *
     * @return array
     */
    public function getLoginCustomers()
    {
        if ($this->IcecubeNintydaysHelper->isModuleEnabled()) {
            $numberOfDays = $this->IcecubeNintydaysHelper->getNumberOfDays();
        } else {
            return 'Error: Icecube_Nintydays Module Not enable.';
        }
         return $numberOfDays;

        
    }
}
