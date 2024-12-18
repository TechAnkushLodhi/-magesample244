<?php
namespace Order\Programmtic\Model\ExitCustomer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\CustomerFactory;

class CustomerExit
{
    protected $customerFactory;

    public function __construct(
        CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    ) {
        $this->customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Check if the customer exists for a given email
     *
     * @param string $customerEmail
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkCustomerExistence($customerEmail,$addressData)
    { 
        try {
            $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($customerEmail);
            if($customer->getId()){
                return true;
            }else{
                try {
                    $store=$this->_storeManager->getStore();
                    $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                    $customer=$this->customerFactory->create();
                    $customer->setWebsiteId($websiteId);
                    $customer->loadByEmail($customerEmail);
    
                    $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($addressData['firstname'])
                    ->setLastname($addressData['lastname'])
                    ->setEmail($customerEmail) 
                    ->setPassword($customerEmail);
                     $customer->save();
                } catch (\Exception $e) {
                   echo $e->getMessage();
                   exit;
                }
                return true;
            }
            
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error while checking customer existence.'));
        }
    }

    /**
     * Get customer details for a given email
     *
     * @param string $customerEmail
     * @return array
     */
    public function getCustomerDetails($customerEmail)
    {
        $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($customerEmail);
           return $customer->getId();
        // return [
        //     'id' => $customer->getId(),
        //     'firstname' => $customer->getFirstname(),
        //     'lastname' => $customer->getLastname(),
        //     'email' => $customer->getEmail(),
            
        //     // Add more customer details as needed
        // ];
    }
}
