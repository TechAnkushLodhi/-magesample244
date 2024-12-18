<?php
namespace Icecube\OrderReminder\Model\SendMail;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\TransportInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Icecube\OrderReminder\Helper\Data as IcecubeOrderHelper;
use Icecube\OrderReminder\Model\ExportItemsFromOrder\ExportItems;
use Magento\Framework\App\State;
/**
 * Sending class responsible for sending reminder emails to customers.
 */
class Sending
{
     protected $appState;
     /**
     * @var IcecubeOrderHelper
     */
    protected $IcecubeOrderHelper;
    /**
     * @var ExportItems
     */
    protected $ExportItems;
      /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;



    /**
     * Sending constructor.
     *
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param TransportBuilder $transportBuilder
     * @param FactoryInterface $templateFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        TransportBuilder $transportBuilder,
        FactoryInterface $templateFactory,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        IcecubeOrderHelper $IcecubeOrderHelper,
        ExportItems $ExportItems,
        State $appState,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->templateFactory = $templateFactory;
        $this->customerRepository = $customerRepository;
        $this->_urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->IcecubeOrderHelper = $IcecubeOrderHelper;
        $this->ExportItems = $ExportItems;
        $this->appState = $appState;

    }

    /**
     * Send reminder emails to customers based on their recent orders.
     *
     * @param array $OrderDetails - Array containing order details for each customer
     * @return string - Success message or error message
     */
    public function SendingMale($OrderDetails)
    {
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $response = [
            'status' => 'error',
            'message' => ''
        ];
        $successMessage =[];
        foreach ($OrderDetails as $OrderDetail) {
                 
            try {
                // Get customer email and name
                $customerEmail = $OrderDetail['Customer_Email'];
                $customerName = $OrderDetail['Customer_Firstname'] . ' ' . $OrderDetail['Customer_Lastname'];
                $secretKey = 'icecube_reminder_key';
                $encryptedData = $this->encryptData($OrderDetail, $secretKey);
                $Customer_Id = $this->encryptData($OrderDetail['Customer_ID'], $secretKey);
                $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                $controllerUrl =   $baseUrl.'reminder/index/addtocart'; //chenge your website name
                // Generate URL with parameters
                $urlWithParameters = $controllerUrl . '?' . http_build_query(['data' =>  $encryptedData]);

                if ($this->IcecubeOrderHelper->isModuleEnabled()) {
                    $senderName = $this->IcecubeOrderHelper->getSenderName();
                    $senderEmail = $this->IcecubeOrderHelper->getSenderEmail();
                }else{
                    $response['message'] = 'Error: Icecube_OrderReminder Module Not enable.';
                    return $response;   
                }
                // Add your email content and template logic here
                $templateOptions = [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $OrderDetail['Store_ID'],
                ];
            
                $templateVars = [
                    'customer_name' => $customerName,
                    'data' => $OrderDetail,
                    'Add_To_Cart' => $urlWithParameters,
                ];
                $from = ['email' => $senderEmail, 'name' =>  $senderName];
                $to = ['email' => $customerEmail, 'name' => $customerName];  
        
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier("icecube_email_template")
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars(['OrderDetail' => $templateVars])
                    ->setFrom($from)
                    ->addTo($to['email'], $to['name'])
                    ->getTransport();

                $transport->sendMessage();
                 // If the email is sent successfully
                $successMessage[]=$customerEmail;
            } catch (\Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
                return $response; 
            }
        }
        $response['status'] = 'success';
        $response['message'] = $successMessage;
        return $response; 
    }

      // Encryption function
      function encryptData($plainText, $secretKey)
       {
        $plainText = json_encode($plainText);
        $iv = openssl_random_pseudo_bytes(16);
        $encryptedData = openssl_encrypt($plainText, 'aes-256-cbc', $secretKey, 0, $iv);
        $combinedData = $iv . $encryptedData;
        return base64_encode($combinedData);
      }


     

}
