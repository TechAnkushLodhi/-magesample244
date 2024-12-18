<?php
namespace Icecube\OrderReminder\Model\AddToCart;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Icecube\OrderReminder\Model\ExportItemsFromOrder\ExportItems;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
class ItemsAddCart 
{
        /**
     * @var Cart
     */
    protected $Cart;

        /**
     * @var ExportItems
     */
    protected $ExportItems;

      /**
     * @var ProductRepositoryInterface
     */
    protected $ProductRepositoryInterface;


         /**
     * @var customerSession
     */
    protected $customerSession;

    protected $urlBuilder;
     
    public function __construct(
        ExportItems $ExportItems,
        Cart $Cart,
        ProductRepositoryInterface $ProductRepositoryInterface,
        Session $customerSession,
        UrlInterface $urlBuilder
    ) {
        $this->ExportItems = $ExportItems;
        $this->Cart = $Cart;
        $this->ProductRepositoryInterface = $ProductRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    public function itemsaddtocart($details){
        $response = [
            'status' => 'error',
            'message' => ''
        ];
        $CustomerId = $details['Customer_ID'];
    
        $errorMessages = []; // Array to store error messages
        $successMessages = []; // Array to store success messages
        echo "<pre>";
        $OrderItems =  $this->ExportItems->exportItems($details['Order_ID']);
        if($OrderItems['status']=='success'){
            $AllProducts  = $OrderItems['message'];
            // print_r($AllProducts);
            // exit;
             foreach ($AllProducts as $ProdcutType => $Product) {
                   if($ProdcutType=='simple' ||  $ProdcutType=='virtual'){
                        foreach ($Product as $key => $Productvalue) {
                                $addToCartResult = $this->AddToCart($Productvalue['id'],$Productvalue['qty'],$CustomerId,null,null);
                                if($addToCartResult['status']=='success'){
                                    $successMessages[] = $addToCartResult['message'];
                                }else{
                                    $errorMessages[] = 'Error adding product ' . $Productvalue['name'] . ': ' . $addToCartResult['message'];
                                }    
                            }
                    }

                   if($ProdcutType=='configurable'){
                        foreach ($Product as $key => $Productvalue) {
                        $addToCartResult = $this->AddToCart($Productvalue['id'],$Productvalue['qty'],$CustomerId,$Productvalue['super_attribute'],null);
                        if($addToCartResult['status']=='success'){
                            $successMessages[] = $addToCartResult['message'];
                        }else{
                            $errorMessages[] = 'Error adding product ' . $Productvalue['name'] . ': ' . $addToCartResult['message'];
                        }    
                    }
                   }
                    if($ProdcutType=='bundle'){
                            foreach ($Product as $key => $Productvalue) {
                            $addToCartResult = $this->AddToCart($Productvalue['id'],$Productvalue['qty'],$CustomerId,$Productvalue['bundle_option'],$Productvalue['bundle_option_qty']);
                            if($addToCartResult['status']=='success'){
                                $successMessages[] = $addToCartResult['message'];
                            }else{
                                $errorMessages[] = 'Error adding product ' . $Productvalue['name'] . ': ' . $addToCartResult['message'];
                            }    
                        }
                    }
                    if($ProdcutType=='downloadable'){
                    foreach ($Product as $key => $Productvalue) {
                        $addToCartResult = $this->AddToCart($Productvalue['id'],$Productvalue['qty'],$CustomerId,$Productvalue['links'],null);
                        if($addToCartResult['status']=='success'){
                            $successMessages[] = $addToCartResult['message'];
                        }else{
                            $errorMessages[] = 'Error adding product ' . $Productvalue['name'] . ': ' . $addToCartResult['message'];
                        }    
                    }
                    }

                if($ProdcutType=='grouped'){
                    
                    foreach ($Product as $key => $Productvalue) {
                           $product = $this->ProductRepositoryInterface->getById($key);
                            $addToCartResult = $this->AddToCart($key,$Productvalue,$CustomerId,null,null);
                            if($addToCartResult['status']=='success'){
                                $successMessages[] = $addToCartResult['message'];
                            }else{
                                $errorMessages[] = 'Error adding product ' .   $product->getName() . ': ' . $addToCartResult['message'];
                            }  
                    }
                }
                
             }
             $response['status'] = 'success';
             $response['message'] = $result=['ErrorMessage'=>$errorMessages,'SuccessMessage'=>$successMessages];
             return $response; 
        }else{
            $response['message'] = $OrderItems['message'];
            return $response; 
        }
       
    }

    public function addToCart($productId, $qty, $customerId,$options,$optionqty)
    {
        $response = [
            'status' => 'error',
            'message' => ''
        ];
        try {
            if($this->customerSession->isLoggedIn()){
                $this->Cart->getQuote()->loadByCustomer($customerId);
            } else {
                $this->Cart->getQuote()->setIsCheckoutCart(true)->setCustomerId(null);
            }
            // Load the product by its ID
            $product = $this->ProductRepositoryInterface->getById($productId);
            if($product->getTypeId()=='configurable'){
                $params = [
                    'product' => $product->getId(),
                    'super_attribute' => $options,
                    'qty' => $qty
                ];
                $this->Cart->addProduct($product, $params);
            }elseif($product->getTypeId()=='bundle'){
                $params = [
                    'qty' => $qty,
                    'product' => $product->getId(),
                    'bundle_option' => $options,
                    'bundle_option_qty' => $optionqty
                ];
                $this->Cart->addProduct($product, $params);
            }elseif($product->getTypeId()=='downloadable'){
                $params = [
                    'qty' => $qty,
                    'product' => $product->getId(),
                    'links' => $options,
                ];
                $this->Cart->addProduct($product, $params);
            }elseif($product->getTypeId()=='grouped'){
                $super_group = [];
                foreach ($qty as $products) {
                    $super_group[$products['id']] = $products['qty'];
                }                                                                                                                           
                $params = [
                    'product' => $product->getId(),
                    'super_group' => $super_group
                 ];
                $this->Cart->addProduct($product, $params);
            }else{
                $this->Cart->addProduct($product, ['qty' => $qty]);
            }
            // Add the product to the quote
            $cartUrl = $this->urlBuilder->getUrl('checkout/cart');
            $this->Cart->save();
            $response['status'] = 'success';
            $response['message'] = __('You added %1 to your <a href="%2">shopping cart</a>', $product->getName(), $cartUrl);
            return $response; 
        } catch (\Exception $e) {
            $response['message'] = __( '%1', $e->getMessage() );
            return $response; 
        }
    }
    



}
?>