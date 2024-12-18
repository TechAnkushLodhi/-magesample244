<?php
namespace Order\Programmtic\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Order\Programmtic\Model\AllDataController\FileController;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Order\Programmtic\Helper\Data as IcecubeOrderHelper;


class Programmtic extends Action
{
    protected $resultPageFactory;
    protected $FileController;
    protected $messageManager;
    protected $resultRedirectFactory;
    protected $customerSession;
    protected $icecubeOrderHelper;




    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FileController $FileController,
        ManagerInterface $messageManager,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        IcecubeOrderHelper $icecubeOrderHelper


    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->FileController = $FileController;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->icecubeOrderHelper = $icecubeOrderHelper;
    }

    public function execute()
    {      
          
    if ($this->icecubeOrderHelper->isModuleEnabled()){
        $customerEmail = 'mohan@icecubedigital.com';
        $storeId = 1;
        $addressData = [
            'firstname'           => 'Mohan', 
            'lastname'            => 'Lodhi',
            'company'             => 'icecubedigital',  
            'street'              => 'Main Street',
            'city'                => 'Bhind',
            'country_id'          => 'IN',
            'region'              => 'MP', 
            'postcode'            => '380011',
            'telephone'           => "0123456789",  
            'fax'                 => '',   
            'save_in_address_book'=> 1,
            'vat_id'              => null,  
            'customer_notes'      => "This is most importent Order",  
        ];
        // $addressData=0;
        // $shippingMethod = 'freeshipping_freeshipping';
        $shippingMethod = 'flatrate_flatrate';
        $OrderProducts = [
            'items' => [
                ['ProdcutSku' => 'TestGroupProduct', 'associatedProducts' => [['AssociatedProductsSku' => '24-MB01', 'qty' => 1], ['AssociatedProductsSku' => '24-MB04', 'qty' => 1]]],
                ['ProdcutSku' => 'MH01', 'ConfigrableProdcutAttrubtes' => ['size' => 'XL', 'color' => 'Orange'], 'qty' => 1],
                ['ProdcutSku' => 'WT09', 'ConfigrableProdcutAttrubtes' => ['size' => 'M', 'color' => 'Purple'], 'qty' => 1],
                ['ProdcutSku' => '24-MB01', 'qty' => 1],
                ['ProdcutSku' => '24-MB02', 'qty' => 1],
                ['ProdcutSku' => '24-WG080', 'BundleProducts' => [
                ['Option_id'=>1,'OptionProducts'=>[['Selection_id'=>1,'qty'=>1]]],
                ['Option_id'=>2,'OptionProducts'=>[['Selection_id'=>1,'qty'=>1]]],
                ['Option_id'=>3,'OptionProducts'=>[['Selection_id'=>1,'qty'=>1],['Selection_id'=>2,'qty'=>1]]],
                ['Option_id'=>4,'OptionProducts'=>[['Selection_id'=>2,'qty'=>1]]]],                    
                ],
            ]   
        ];
        
        $FileController=$this->FileController->FileController($customerEmail, $storeId,$addressData,$shippingMethod, $OrderProducts);
        $loggedInCustomerEmail = $this->customerSession->getCustomer()->getEmail();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($FileController['status'] === 'success') {
            $this->messageManager->addSuccessMessage(__($FileController['message']));
            if($this->customerSession->isLoggedIn()){
                if ($loggedInCustomerEmail === $customerEmail) {
                    $resultRedirect->setPath('customer/account'); // Account Page
                    return $resultRedirect;
                }else{
                    $resultRedirect->setPath('/'); // Home page
                    return $resultRedirect;
                }
            }else{
                $resultRedirect->setPath('/'); // Home page
                return $resultRedirect;
            }
            
        } else {
            if($this->customerSession->isLoggedIn()){
                $this->messageManager->addSuccessMessage(__($FileController['message']));
                if ($loggedInCustomerEmail === $customerEmail) {
                    $resultRedirect->setPath('customer/account'); // Account Page
                    return $resultRedirect;
                }else{
                    $resultRedirect->setPath('/'); // Home page
                    return $resultRedirect;
                }
            }else{
                $resultRedirect->setPath('/'); // Home page
                return $resultRedirect;
            }
        }

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }else{
        $this->_forward('noroute');
    }

}
     
}
