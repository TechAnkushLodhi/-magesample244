<?php
namespace Icecube\OrderReminder\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Icecube\OrderReminder\Model\AddToCart\ItemsAddCart;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Controller class responsible for adding products to the cart based on the reminder email link.
 */
class Addtocart extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ItemsAddCart
     */
    protected $ItemsAddCart;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ItemsAddCart $ItemsAddCart
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ItemsAddCart $ItemsAddCart,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->ItemsAddCart = $ItemsAddCart;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
     {
                $data = $this->getRequest()->getParam('data');
                $secretKey = 'icecube_reminder_key';
                $details = $this->decryptData($data, $secretKey);
                $addtocart = $this->ItemsAddCart->itemsaddtocart($details);
                // Redirect Create
                $resultRedirect = $this->resultRedirectFactory->create(); 
                
                
                if($addtocart['status']=='success'){
                    if($addtocart['message']['ErrorMessage']){
                       foreach ($addtocart['message']['ErrorMessage'] as  $value) {
                          $this->messageManager->addErrorMessage(__($value));
                       }
                    }
                    // if($addtocart['message']['SuccessMessage']){
                    //     foreach ($addtocart['message']['SuccessMessage'] as  $value) {  
                    //         $formattedMessage = sprintf($value);   
                    //        $this->messageManager->addSuccessMessage(__($formattedMessage));
                    //  }
                    //  }
                }else{
                    $this->messageManager->addErrorMessage(__($addtocart['message'])); 
                }
                $resultRedirect->setPath('checkout');
                return $resultRedirect;    
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
    }

        // Decryption function
        function decryptData($encryptedData, $secretKey) {
            // Base64 decode the encrypted data
            $combinedData = base64_decode($encryptedData);

            // Separate IV and encrypted data
            $iv = substr($combinedData, 0, 16);
            $encryptedData = substr($combinedData, 16);
            $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $secretKey, 0, $iv);
            // Decrypt the data using AES-256-CBC algorithm
            return json_decode($decryptedData, true);
        }

    

}
