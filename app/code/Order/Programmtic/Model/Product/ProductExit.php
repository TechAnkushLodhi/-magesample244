<?php
namespace Order\Programmtic\Model\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;


class ProductExit
{
    protected $productFactory;
    protected $productRepository;
    protected $Error;
   


    public function __construct(
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository
   

    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->Error = [
            'type' => "error",
            'message' => ''
        ];
    }

    /**
     * Check if the product exists for a given SKU
     *
     * @param string $productSku
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkProductExistence($productSku)
    {
        try {
            $product = $this->productFactory->create()->loadByAttribute('sku', $productSku);
            return $product && $product->getId() ? true : false;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error while checking product existence.'));
        }
    }


    public function productchecktype($productSku)
    {
        try {
            // Get product by SKU
            $product = $this->productRepository->get($productSku);
            // Check product type
            $productType = $product->getTypeId();
            return $productType;
        } catch (NoSuchEntityException $e) {
            return 'Product not found';
        }
    }

     public function getSimpleProdcut($productSku,$extraDetails){ 
      $product = $this->productFactory->create()->loadByAttribute('sku', $productSku);
        return ["sku"=>$product->getSku(),'qty'=>$extraDetails['qty']];
      }


    /**
     * Get product details for a given SKU
     *
     * @param string $productSku
     * @return array
     */

    public function getConfigurableProdcut($productSku,$extraDetails)
    {
       
        $configurableProduct   = $this->productFactory->create()->loadByAttribute('sku', $productSku);
        if ($configurableProduct  &&  $configurableProduct ->getTypeId() == 'configurable') {
            // Get the child product by selected attributes
            $childProduct = $this->getChildProductByAttributes($configurableProduct, $extraDetails);
            if ($childProduct) {
                return  $childProduct;
            }else{
                echo "Child produt  not match";
            }
        }else{
            echo "Prodcut is not configrable Product";
        } 
    }

      // Function to get child product by selected attributes
        protected function getChildProductByAttributes($configurableProduct, $extraDetails)
        {
            $productTypeInstance = $configurableProduct->getTypeInstance();
            $usedProducts = $productTypeInstance->getUsedProducts($configurableProduct);
            foreach ($usedProducts as $childProduct) {
                $matches = true;
                $childAttributes = $childProduct->getAttributes(); // Get all attributes of child product
                  
                foreach ($extraDetails['ConfigrableProdcutAttrubtes'] as $attributeCode => $selectedValue) {
                    $productValue = $childAttributes[$attributeCode]->getFrontend()->getValue($childProduct);
                    if ($productValue !== $selectedValue) {
                        $matches = false;
                        break;
                    }
                }
                if ($matches) {
                    // Return child product SKU and quantity
                    return array(
                        'sku' => $childProduct->getSku(),
                        'qty' => $extraDetails['qty']
                    );
                }
            }
            
            return null;
        }

       /**
     * Get associated products of a group product by SKU
     *
     * @param string $productSku
     * @param array $attributes
     * @return array
     * @throws NoSuchEntityException
     */
    public function getGroupProduct($productSku, $extraDetails)
    {
       $OrderAssociatedPrdoucts;
       foreach ($extraDetails as  $value) {
        $OrderAssociatedPrdoucts=$value;
       }
       
    
     
        try {
            /** @var ProductInterface $groupProduct */
            $groupProduct = $this->productRepository->get($productSku);
            // Check if the product is a group product
            if ($groupProduct->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                // Retrieve associated products
                $associatedProducts = $groupProduct->getTypeInstance()->getAssociatedProducts($groupProduct);
    
                $result = [];
                foreach ($associatedProducts as $associatedProduct) {
                    $sku = $associatedProduct->getSku();
                      // Search for matching SKU in the original array
                        foreach ($OrderAssociatedPrdoucts as $item) {
                            if ($item['AssociatedProductsSku'] == $sku) {
                                // If SKU matches, store the values in a new array
                                $result[] = array(
                                    'AssociatedProductsSku' => $item['AssociatedProductsSku'],
                                    'qty' => $item['qty']
                                );
                            }
                        }
                    }
                  
                return $result;
            } else {
                throw new NoSuchEntityException(__('The specified product is not a group product.'));
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error while retrieving group product information.'));
        }
    } 

    public function getBundleProduct($productSku, $extraDetails)
    {
      
            $OptionsArray = [];
            $QtyArray=[];
            foreach ($extraDetails['BundleProducts'] as $bundleProduct) {
                $optionId = $bundleProduct['option_id'];
                foreach ($bundleProduct['OptionProducts'] as $optionProduct) {
                    $OptionsArray[$optionId][] = $optionProduct['section_id']-1;
                    $QtyArray[$optionId][] = $optionProduct['qty'];
                }
            }
            $BundleProduct = $this->productFactory->create()->loadByAttribute('sku', $productSku);
            // get bundle option and section by product factory
            $product = $this->productFactory->create()->load($BundleProduct->getId());
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds($product),$product);
            $bundleOptions = [];
            foreach ($selectionCollection as $selection) {
                $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
            }
            // Process the Arrays
            $BundleProductsOptios = array();
            foreach ($OptionsArray as $OptionsArrayKey => $valueArray) {
                foreach ($valueArray as $FirstArrayValue) {
                    foreach ($bundleOptions as $bundleKey => $bundleValue) {
                        foreach ($bundleValue as $key => $value) {
                            if ($OptionsArrayKey == $bundleKey && $FirstArrayValue == $key) {
                                $BundleProductsOptios[$OptionsArrayKey][] = $value;
                            }
                        }
                    }
                }
            }
            // qty array process
            $qtyMainArray=[];
            foreach ($BundleProductsOptios as $keys => $valueArray) {
                 foreach ($valueArray as $valueArrayKeys => $valueArrayValue) {
                   foreach ($QtyArray as $QtyArrayKeys => $QtyArrayValues) {
                        foreach ($QtyArrayValues as $QtyArrayValueskeys => $QtyArrayValuesValue) {
                          if($keys==$QtyArrayKeys && $valueArrayKeys==$QtyArrayValueskeys){
                            $qtyMainArray[$keys][$valueArrayValue]=$QtyArrayValuesValue;
                          }
                        }
                   }
                 }
            }
         
            return ['BundleProductOptions'=>$BundleProductsOptios,'BundleProductQty'=>$qtyMainArray];
    
    }
    
    
   

    
}

