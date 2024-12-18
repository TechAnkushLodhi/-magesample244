<?php
namespace Icecube\OrderReminder\Model\ExportItemsFromOrder;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;


class ExportItems
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var OrderItemCollectionFactory
     */
    protected $orderItemCollectionFactory;

     /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

     /**
     * @var optionRepository
     */
    protected $optionRepository;

        /**
     * @var ProductFactory
     */
    private $productFactory;

    public function __construct(
        Order $order,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        ProductRepositoryInterface $productRepository,
        ProductOptionRepositoryInterface $optionRepository,
        ProductFactory $productFactory
    ) {
        $this->order = $order;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->productFactory = $productFactory;
    }
   
    public function exportItems($orderId)
    { 
        $response = [
            'status' => 'error',
            'message' => ''
        ];
        // Load order by increment id
         $order = $this->order->loadByIncrementId($orderId);
           if($order->getId()){ // Check if the order ID exists
            if (count($order->getAllItems()) == 0) {
                $response['message'] = ' No items found in the'.$orderId;
                return $response; 
            }
            $orderItemsCollection = $this->orderItemCollectionFactory->create()->setOrderFilter($order);
            $orderItemsDetails = [];
                    foreach ($orderItemsCollection as $item) {
                        // Simple Orders
                        if($item->getProductType()=='simple'){
                            if(!$item->getParentItemId()){
                            
                                        $orderItemsDetails['simple'][]=[
                                            'id'=>$item->getProductId(),
                                            'sku'=>$item->getSku(),
                                            'name'=>$item->getName(),
                                            'qty'=>$item->getQtyOrdered()
                                        ];
                            }  
                        }
                          // virtual Orders
                        if($item->getProductType()=='virtual'){
                            if(!$item->getParentItemId()){
                            
                                        $orderItemsDetails['virtual'][]=[
                                            'id'=>$item->getProductId(),
                                            'sku'=>$item->getSku(),
                                            'name'=>$item->getName(),
                                            'qty'=>$item->getQtyOrdered()
                                        ];
                            }  
                        }
                        // Bundle Orders
                        if($item->getProductType()=='bundle'){
                            if(!$item->getParentItemId()){
                                        $productOptions = $item->getProductOptions()['info_buyRequest'];
                                        $orderItemsDetails['bundle'][]=[
                                            'id'=>$item->getProductId(),
                                            'sku'=>$item->getSku(),
                                            'name'=>$item->getName(),
                                            'qty'=>$item->getQtyOrdered(),
                                            'bundle_option_qty'=>$productOptions['bundle_option_qty'],
                                            'bundle_option'=> $productOptions['bundle_option']
                                        ];
                                    }
                                
                        }  
                        // Configurable Orders
                        if($item->getProductType()=='configurable'){
                            if(!$item->getParentItemId()){
                            $OrderOptions = $item->getProductOptions()['info_buyRequest']['super_attribute'];
                                    $orderItemsDetails['configurable'][]=[
                                        'id'=>$item->getProductId(),
                                        'sku'=>$item->getSku(),
                                        'name'=>$item->getName(),
                                        'qty'=>$item->getQtyOrdered(),
                                        'super_attribute'=> $OrderOptions
                                    ];
                                }  
                        }  
                        // Downloadable Orders
                        if($item->getProductType()=='downloadable'){
                            if(!$item->getParentItemId()){
                                    $orderItemsDetails['downloadable'][]=[
                                        'id'=>$item->getProductId(),
                                        'sku'=>$item->getSku(),
                                        'name'=>$item->getName(),
                                        'qty'=>$item->getQtyOrdered(),
                                        'links'=> $item->getProductOptions()['links']
                                    ];
                            }  
                        } 
                        // Group Orders
                        if($item->getProductType()=='grouped'){
                            if(!$item->getParentItemId()){
                            $GroupProductParentId = $item->getProductOptions()['info_buyRequest']['super_product_config']['product_id'];
                                $orderItemsDetails['grouped'][$GroupProductParentId][]=[
                                    'id'=>$item->getProductId(),
                                    'sku'=>$item->getSku(),
                                    'name'=>$item->getName(),
                                    'qty'=>$item->getQtyOrdered()
                                    ];
                                }
                        }
                }
                $response['status'] = 'success';
                $response['message'] = $orderItemsDetails;
                return $response; 
       }else {
        $response['message'] = $orderId.' OrderId Not Fount In Order Collection' ;
        return $response; 
        } 
    }
}

