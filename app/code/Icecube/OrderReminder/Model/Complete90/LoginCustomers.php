<?php
namespace Icecube\OrderReminder\Model\Complete90;

use Icecube\OrderReminder\Helper\Data as IcecubeOrderHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class LoginCustomers
{
    /**
     * @var IcecubeOrderHelper
     */
    protected $IcecubeOrderHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    public function __construct(
        IcecubeOrderHelper $IcecubeOrderHelper,
        CustomerFactory $customerFactory,
        OrderCollectionFactory $orderCollectionFactory,
        DateTime $dateTime
    ) {
        $this->IcecubeOrderHelper = $IcecubeOrderHelper;
        $this->customerFactory = $customerFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateTime = $dateTime;
    }

    public function getLoginCustomers()
    {
        $response = [
            'status' => 'error',
            'message' => []
        ];
    
        if (!$this->IcecubeOrderHelper->isModuleEnabled()) {
            $response['message'] = 'Error: Icecube_OrderReminder Module Not enabled.';
            return $response;
        }
        $NumberOfDays  = $this->IcecubeOrderHelper->getNumberOfDays();
        // Get current date
        $currentDate = $this->dateTime->gmtDate();
    
        // Get all customers
        $customerCollection = $this->customerFactory->create()->getCollection();
        $results = [];
        // Iterate through each customer
        foreach ($customerCollection as $customer) {
            $customerId = $customer->getId();
    
            // Get the last order of the customer
            $lastOrder = $this->orderCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addOrder('created_at', 'DESC')
                ->setPageSize(1)
                ->getFirstItem();
    
            // Check if the last order exists and if it is older than 90 days
            if ($lastOrder->getId() && $this->isOrderOlderThan90Days($lastOrder->getCreatedAt(),$NumberOfDays)) {
                $result = [
                    'Order_ID' => $lastOrder->getIncrementId(),
                    'Customer_ID' => $lastOrder->getCustomerId(), 
                    'Customer_Email' => $lastOrder->getCustomerEmail(),
                    'Customer_Firstname' => $lastOrder->getCustomerFirstname(),
                    'Customer_Lastname' => $lastOrder->getCustomerLastname(),
                    'Grand_Total' => $lastOrder->getGrandTotal(),
                    'Store_ID' => $lastOrder->getStoreId(),
                ];
                $results[] = $result;
            }
        }
          
        // Return the results
        $response['status'] = 'success';
        $response['message'] = $results;


        if (empty($response['message'])) {
            // If no customer has an order older than 90 days, add "not found" message
            $response['status'] = 'error';
            $response['message'] = 'last order for any customer is not found to be older than '.$NumberOfDays.' days';
        }
        return $response;
    }
    
    protected function isOrderOlderThan90Days($orderCreatedAt,$NumberOfDays)
    {
        
        $orderDate = strtotime($orderCreatedAt);
        $currentDate = strtotime($this->dateTime->gmtDate());
        // Calculate difference in seconds
        $difference = $currentDate - $orderDate;
        // Convert seconds to days
        $days = $difference / 86400 ;
        // Check if older than 90 days
        return $days > $NumberOfDays;
    }
}
