<?php
namespace Order\Programmtic\Model\AllDataController;
use Order\Programmtic\Model\ExitCustomer\CustomerExit;
use Order\Programmtic\Model\Product\ProductExit;
use Order\Programmtic\Model\OrderCreate\OrderManagement;
class FileController {
protected $exitCustomer;
protected $exitProduct;
protected $orderManagement;

public function __construct(
    CustomerExit $exitCustomer,
    ProductExit $exitProduct,
    OrderManagement $orderManagement,
    
) {
    $this->exitCustomer = $exitCustomer;
    $this->exitProduct = $exitProduct;
    $this->orderManagement = $orderManagement;
}

 public function FileController($customerEmail, $storeId,$addressData,$shippingMethod, $OrderProducts,$default_address_use){
    $response = [
        'status' => 'error',
        'message' => ''
    ];
    $isCustomerExist = $this->exitCustomer->checkCustomerExistence($customerEmail,$addressData);
    if ($isCustomerExist) {
        $customer_id = $this->exitCustomer->getCustomerDetails($customerEmail);
        $customerId = $customer_id;
        $AllProducts =[];
        foreach ($OrderProducts['items'] as $item) {
            $productSku = null; 
            $extraDetails = null; 
            if (isset($item['ProdcutSku'])) {
                $productSku = $item['ProdcutSku'];
            }
            if (count($item) > 1) {
                unset($item['ProdcutSku']);
                $extraDetails = $item;
            }
            

            $isProductExist = $this->exitProduct->checkProductExistence($productSku);

            if($isProductExist){
                $productType = $this->exitProduct->productchecktype($productSku);
                switch ($productType) {
                    case 'simple':
                       $SimpleProductSku= $this->exitProduct->getSimpleProdcut($productSku,$extraDetails);
                       $AllProducts[]=$SimpleProductSku;
                       
                        break;
                    case 'configurable':
                       
                        $ConfigurableProdcutSku = $this->exitProduct->getConfigurableProdcut($productSku,$extraDetails,);
                       $AllProducts[]=$ConfigurableProdcutSku;
                        break;
            
                    case 'bundle':
                       
                        $bundle = $this->exitProduct->getBundleProduct($productSku,$extraDetails);
                       
                            $AllProducts[] = array(
                                'sku' =>$productSku,
                                'qty' => $bundle,
                            );
                        break;
                    case 'downloadable':
                        $download = $this->exitProduct->getSimpleProdcut($productSku,$extraDetails);
                        $AllProducts[]=$download;
                        break;
                    case 'grouped':
                        $GroupProduct = $this->exitProduct->getGroupProduct($productSku,$extraDetails);
                        foreach ($GroupProduct as $item) {
                            $AllProducts[] = array(
                                'sku' => $item['AssociatedProductsSku'],
                                'qty' => $item['qty']
                            );
                        }
                        break;
                    default:
                    $response['message'] = 'Error:Product Type ' . $productType . '  Not Match.';
                    break;
                }
            }else{
                $response['message'] = 'Error: Product with SKU ' . $productSku . '  Not Exist.';
            }
        }
        try {
            $orderId = $this->orderManagement->placeOrder($AllProducts, $customerId, $storeId, $addressData, $shippingMethod);
            return $orderId;
            if($orderId['status']==='success'){
                $response['status'] = 'success';
                $response['message'] = 'Order placed successfully. Order ID: ' . $orderId['message'];
            }else{
                $response['message'] = $orderId['message']; 
            }
           
        } catch (\Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }

    }else {
        // Customer does not exist
        $response['message'] = 'Error: Customer with Email ' . $customerEmail . '  Not Exist.';
    }

    return $response; 
 }

}
?>