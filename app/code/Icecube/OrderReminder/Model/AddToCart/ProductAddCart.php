<?php
namespace Icecube\OrderReminder\Model\AddToCart;


use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use  Magento\Framework\Data\Form\FormKey; 
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;

class ProductAddCart 
{
    protected $cart;
    protected $productRepository;
    protected $optionRepository;
    protected $selectionRepository;
    protected $formKey;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    protected $customerSession;


    public function __construct(
        Cart $cart,
        ProductRepositoryInterface $productRepository,
        ProductOptionRepositoryInterface $optionRepository,
        FormKey $formKey,
        ProductFactory $productFactory,
        Session $customerSession
       
    ) {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->formKey = $formKey;
        $this->productFactory = $productFactory;
        $this->customerSession = $customerSession;


    }

    public function ProductAddCart($details)
{
 
    $ProductInCart=false;       
      foreach ($details as  $value) {
        $productId=  $value['product_id'];
        $Product = $this->productRepository->getById($productId);
            // echo $productId;
            // echo  $Product->isAvailable();
            if($Product->getTypeId() == 'bundle'){
                $bundleOptions = [];
                $BundleProductPara=$this->getBundleOptions($value,$productId);
                foreach ($BundleProductPara as $OptionsSections) {
                    $optionId = $OptionsSections['Option_Id'];
                    $valueId = $OptionsSections['Section_Id'];
                     // If the option is not yet in the $bundleOptions array, create an array for it
                        if (!isset($bundleOptions[$optionId])) {
                            $bundleOptions[$optionId] = [];
                        }
                    $bundleOptions[$optionId][] = $valueId;
                 }
              $BundleProduct =  $this->addBundleProductToCart($CustomerID, $productId, $bundleOptions);
              if($BundleProduct){
                $ProductInCart=true;
              }else{
                   $ProductInCart=false;
              }
             
            }elseif($Product->getTypeId() == 'configurable'){
                $childProductId =  $value['ChildProductIds'][0];
                $Product = $this->productRepository->getById($childProductId);
                $ConfigrablePoduct=$this->AddToCart($CustomerID,$childProductId,$Product); 
                if($ConfigrablePoduct){
                    $ProductInCart=true;
                  }else{
                       $ProductInCart=false;
                  } 
            }else{       
            $SimpleProducts=$this->AddToCart($CustomerID,$productId,$Product);   
            if($SimpleProducts){
                $ProductInCart=true;
              }else{
                   $ProductInCart=false;
              } 
            }  
      }
    //   exit;
      return $ProductInCart;
}
public function AddToCart($CustomerID,$productId,$Product){
    if ($CustomerID==='guest') {
        $this->cart->getQuote()->setIsCheckoutCart(true)->setCustomerId(null);
    }else{
       if($this->customerSession->isLoggedIn()){
         $this->cart->getQuote()->loadByCustomer($CustomerID);
        }else{
           $this->cart->getQuote()->setIsCheckoutCart(true)->setCustomerId(null);
        }
     }  
        $productDetails = [];
        $quote = $this->cart->getQuote();
        $productInCart = false;
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getId() == $productId) {
                $productInCart = true;
                // If the product is already in the cart, increase its quantity
                $item->setQty($item->getQty() + 1);
                $quote->collectTotals();
                $quote->save();
                $productDetails = [
                    'id' => $item->getProduct()->getId(),
                    'name' => $item->getProduct()->getName(),
                    'sku' => $item->getProduct()->getSku(),
                    'quantity' => $item->getQty(), // Updated quantity
                ];
                break;
            }
        }
        if (!$productInCart) {
            // Add the product to the cart with an initial quantity of 1
            $quote->addProduct($Product, 1);
            $quote->collectTotals();
            $quote->save();
            $productDetails = [
                'id' => $Product->getId(),
                'name' => $Product->getName(),
                'sku' => $Product->getSku(),
                'quantity' => 1, // Initial quantity
            ];
        }
       return   $productDetails;
    
}
public function addBundleProductToCart($customerId, $productId, $bundleOptions)
{        
    try {
        if (!$customerId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Customer not exist.'));
        }
         // Load the bundle product
         $bundleProduct = $this->productRepository->getById($productId);
         if($customerId!=="guest"){
             if($this->customerSession->isLoggedIn()){
                $this->cart->getQuote()->loadByCustomer($customerId);
             }
         }
         // Prepare the parameters for adding to cart
            //  'bundle_option_qty' => [  // usr for add qty
            //     '1' => '1',
            //     '2' => '1',
            //     '3' => ['5' => '1', '6' => '3','7'=>'4'],
            //     '4' => '9',
            // ], 
         $qty=1;
         $params = [
             'product' => $productId,
             'bundle_option' => $bundleOptions,
             'qty' => $qty
         ];

         $this->cart->addProduct($bundleProduct, $params);
         $this->cart->save();
         return 'Product added to cart successfully.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}
public function getBundleOptions($childProductIds,$productId)
{
          $bundleProductOptions=[];
          $Product = $this->productRepository->getById($productId);

             foreach ($childProductIds['ChildProductIds'] as  $ChildProductId) {
                  // Get bundle options
                $options = $this->optionRepository->getList($Product->getSku());
                foreach ($options as $option) {
                    $items = $this->optionRepository->get($Product->getSku(), $option->getOptionId());
                    // echo "<pre>";
                    // print_r($items->debug());
                  
                     foreach ($items['product_links'] as  $product_links) {

                        if($product_links['entity_id']==$ChildProductId){
                            $bundleProductOptions[]=['Option_Id'=>$product_links['option_id'],'Section_Id'=>$product_links['id']];
                           
                        }
                        
                     }
                   
                }
            }           
            return  $bundleProductOptions;
            //  get bundle option and section by product factory
            // $product = $this->productFactory->create()->load($productId);
            // $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product),$product);
            // $bundleOptions = [];
            // foreach ($selectionCollection as $selection) {
            //     $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
            // }
            // echo "<pre>";
            // print_r($bundleOptions);
            // exit;
            // return $bundleOptions;
}

   
}



?>


