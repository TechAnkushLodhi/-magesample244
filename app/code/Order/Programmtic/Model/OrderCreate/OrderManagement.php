<?php
namespace Order\Programmtic\Model\OrderCreate;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
class OrderManagement
{
    protected $productRepository;
    protected $productFactory;
    protected $cart;
    protected $addressRepository;
    public function __construct(
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        Product $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository 
    ) {
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->addressRepository = $addressRepository;

    }

   
   
    public function placeOrder($AllProducts, $customerId, $storeId, $addressData, $shippingMethod)
    {  
     $quote = $this->createQuote($AllProducts, $customerId, $storeId, $addressData, $shippingMethod);
        return $quote;
    }

    public function createQuote($AllProducts, $customerId, $storeId, $addressData, $shippingMethod)
    { 
        $response = [
            'status' => 'error',
            'message' => ''
        ];
        try {

            if (!$customerId) {
                $response['message'] = 'Error: Customer ' . $customerId . '  Not Exist.';
            }

            $store=$this->_storeManager->getStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->customerFactory->create()->load($customerId);
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($customer->getEmail());
           
            $quote = $this->cart->getQuote()->loadByCustomer($customerId);
            // $quote=$this->quote->create();
            $quote->setStore($store);
            $customer= $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer

            $quote->removeAllItems();
            foreach ($AllProducts as $product) {

                $ProductSku=$product['sku'];
                $ProductQty=$product['qty'];
                $product = $this->productFactory->loadByAttribute('sku', $ProductSku);
                if (!$product) {
                    throw new NoSuchEntityException(__('Product not found with SKU %1', $ProductSku));
                }
                  // Set product quantity and price
                 $price = $product->getPrice();
                 $BundleProduct = $this->productRepository->get($ProductSku);
                 $BundleProductType = $BundleProduct->getTypeId();
                 if ($BundleProductType == 'bundle') {
                    // Load the bundle product
                    $bundleProduct = $this->productRepository->getById($BundleProduct->getId());
                    $qty=1;
                    $params = [
                        'product' => $BundleProduct->getId(),
                        'bundle_option' => $ProductQty['BundleProductOptions'],
                        'qty' => $qty
                    ];
                    $this->cart->addProduct($bundleProduct, $params);
                 }else{
                    $this->cart->addProduct($product, $ProductQty);
                 }
            }
           
             $shippingAddressId = $customer->getDefaultShipping();
             $billingAddressId = $customer->getDefaultBilling();

             if (!$shippingAddressId && !$billingAddressId) {
                $billingAddress=  $quote->getBillingAddress()->addData($addressData);
                $shippingAddress= $quote->getShippingAddress()->addData($addressData);

                $quote->setBillingAddress($billingAddress);
                $quote->setShippingAddress($shippingAddress);
            }
           
               
            if($addressData!=='null'){
                $billingAddress=  $quote->getBillingAddress()->addData($addressData);
                $shippingAddress= $quote->getShippingAddress()->addData($addressData);
            }
            $shippingAddress=$quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod($shippingMethod); 
            $quote->setPaymentMethod('checkmo'); 
            $quote->setInventoryProcessed(false); 
            $quote->save(); 


            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => 'checkmo']);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();
            $grandTotal = $quote->getGrandTotal();
            $total = $quote->getSubtotal();
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            $orderId = $order->getId();
           
            if( $orderId){
                $response['status'] = 'success';
                $response['message'] = $orderId;
            }else{
                $response['message'] = 'Error: Order Id not found ';
            }
        } catch (\Exception $e) {
            // echo $e.getMessage();
            // exit;
            $response['message'] = 'Error: ' . $e->getMessage();
            
        }
        return $response;
    }

    
}