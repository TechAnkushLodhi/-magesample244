<?php
namespace Order\Programmtic\Model\OrderCreate;
 
class NewOrderCreate 
{
    public function __construct(

    ) {
       
    }
    public function placeOrder($AllProducts, $customerId, $storeId, $addressData, $shippingMethod)
    {  
       return $addressData;
    }
 
    
}
 
?>