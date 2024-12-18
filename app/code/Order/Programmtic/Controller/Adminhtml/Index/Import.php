<?php
namespace Order\Programmtic\Controller\Adminhtml\Index;
use Magento\Framework\Controller\Result\JsonFactory;
use Order\Programmtic\Helper\Data as IcecubeOrderHelper;
use Order\Programmtic\Model\AllDataController\FileController;


class Import extends \Magento\Backend\App\Action
{	
	protected $resultJsonFactory;
    protected $icecubeOrderHelper;
    protected $FileController;

	

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		JsonFactory $resultJsonFactory,
        IcecubeOrderHelper $icecubeOrderHelper,
        FileController $FileController,

	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
        $this->icecubeOrderHelper = $icecubeOrderHelper;
        $this->FileController = $FileController;

	}

			public function execute()
		{
			
			
			$result = $this->resultJsonFactory->create();
			try {
				$postData = $this->getRequest()->getPost();
				if (isset($postData['output'])) {
					$totalOrder=[];
					 
					foreach ($postData['output'] as  $customerdetail) {
					         $customer=[];

							 $customer['customer_email']=$customerdetail["customer_email"];
							 $customer['customer_new_address']=$customerdetail["customer_new_address"];
							 $customer['default_address_use']=$customerdetail["default_address_use"];
							 $customer['store_Id']=$customerdetail["store_Id"];
							 $customer['shipping_method']=$customerdetail["shipping_method"];
							//  items extract
							 $items = $customerdetail["items"]; 
							$productData = [];
							foreach ($items as $value) {
								$values = explode("=>", $value, 2);
								if (count($values) == 2) {
									$key = trim($values[0]); 
									$productDetails = trim($values[1]); 
									if (isset($productData[$key])) {
										$existingDetails = [$productData[$key]];
										$newDetails = [$productDetails];
										$mergedDetails = array_merge($existingDetails, $newDetails);
										$productData[$key] = $mergedDetails;
									}else {
										$productData[$key] = $productDetails;
									}
								}
							 }
							 $itmes=[];
                            foreach ($productData as $key => $values) {
								
								    if($key=="SimpleProduct"){
										if(is_array($values)){
											foreach ($values as $key => $simpleProduct) {
												$itmes[]=$this->simpleProduct($simpleProduct);
											}
										}else{
											$itmes[]=$this->simpleProduct($values);
										}
									}


									if($key=="ConfigurableProduct"){
										if(is_array($values)){
											foreach ($values as $key => $configurable) {
												$itmes[]=$this->configurable($configurable);
											}
										}else{
											$itmes[]=$this->configurable($values);
										}
									}

									if($key=="GroupProduct"){
										if(is_array($values)){
											foreach ($values as $key => $GroupProduct) {
												$itmes[]=$this->GroupProduct($GroupProduct);
											}
										}else{
											$itmes[]=$this->GroupProduct($values);
										}
									}
								
									if($key=="BundleProduct"){
										if(is_array($values)){
											foreach ($values as $key => $BundleProduct) {
												$itmes[]=$this->BundleProduct($BundleProduct);
											}
										}else{
											$itmes[]=$this->BundleProduct($values);
										}
									}



							}   
							$customer['items']=$itmes;
							$totalOrder[]=$customer;
					} 
					if(!$this->icecubeOrderHelper->isModuleEnabled()){
						return $result->setData([
							'status' => 'success', 
							'message' => "Extension is disable."	
	
						]);
					}
					 
					$OrderdIds=[];
                     foreach ($totalOrder as $key => $value) {
						$items=[];
						$items['items']=$value['items'];

						$FileController=$this->FileController->FileController
						(
						$value['customer_email'],
						$value['store_Id'],
						$value['customer_new_address'],
						$value['shipping_method'],
						$items,
						$value['default_address_use']
					  );
					  $OrderdIds[]=$FileController;
					break;
					 }
					
					 return $result->setData([
						'status' => 'success', 
						'message' => $OrderdIds	
						]);
					
				
				
				} else {
					return $result->setData(['status' => 'error', 'message' => 'fieldvalue key is not set']);
				}
			} catch (\Exception $e) {
				return $result->setData(['status' => 'error', 'message' => $e->getMessage()]);
			}
		}

		public function simpleProduct($simpleProduct){
			$sku = substr($simpleProduct, strpos($simpleProduct, '=>') + 2, strpos($simpleProduct, '::') - strpos($simpleProduct, '=>') - 2);  
			$qty = substr($simpleProduct, strpos($simpleProduct, '::qty=') + 6);
			$simpleProduct= [
				'ProdcutSku' => $sku,
				'qty' => intval($qty) 
			];
			return $simpleProduct;
		}

		public function configurable($configurable){
			$sku = substr($configurable, strpos($configurable, '=>') + 2, strpos($configurable, '::') - strpos($configurable, '=>') - 2);
			$attributes = [];
			$attributeString = substr($configurable, strpos($configurable, '::attribute=>') + 13, strpos($configurable, '{qty=') - strpos($configurable, '::attribute=>') - 13);
			$attributeString=substr($attributeString, 1,-1);
			$attributePairs = explode(':', $attributeString);
			foreach ($attributePairs as $pair) {
				list($key, $value) = explode('=', $pair);
				$attributes[$key] = $value;
			}
			$qty = intval(substr($configurable, strpos($configurable, '{qty=') + 5, -1));
			$configrableproduct = [
				'ProdcutSku' => $sku,
				'ConfigrableProdcutAttrubtes' => $attributes,
				'qty' => $qty
			];

		    return $configrableproduct;
		}

		public function GroupProduct($GroupProduct){
			$sku = substr(
				$GroupProduct, 
				strpos($GroupProduct, '=>') + 2,  // '=>' ke baad ka index
				strpos($GroupProduct, '::') - strpos($GroupProduct, '=>') - 2  // '::' se pehle ka index se subtract karte hue length
			);
			
			// Associated Products extract karne ke liye
			$associatedProducts = [];
			$associatedProductsString = substr(
				$GroupProduct, 
				strpos($GroupProduct, 'AssociatedProduct=>{') + strlen('AssociatedProduct=>{'),  // 'AssociatedProduct=>{' ke baad ka index
				-2  // '}' ka position
			);
			$associatedProductDetails = explode('][', $associatedProductsString);
			foreach ($associatedProductDetails as $productDetail) {
				$productDetail = str_replace(['[', ']'], '', $productDetail);
				$productDetailArray = explode(':', $productDetail);
				$associatedProducts[] = [
					'AssociatedProductsSku' => substr($productDetailArray[0], strpos($productDetailArray[0], '=') + 1),
					'qty' => substr($productDetailArray[1], strpos($productDetailArray[1], '=') + 1),
				];
			}
			
			// Final array banaye
			$GroupProduct = [
				'ProdcutSku' => $sku,
				'associatedProducts' => $associatedProducts,
			];

			return $GroupProduct;
		}
         
		public function BundleProduct($BundleProduct){
			$optionArray=[];
			// SKU extract karne ke liye
			$sku = substr($BundleProduct, strpos($BundleProduct, '=>') + 2, strpos($BundleProduct, '::') - strpos($BundleProduct, '=>') - 2);
			$optionArray['ProdcutSku']=$sku;
			$output_string = preg_replace('/\s*{([^}]*)}\s*/', '{$1}', $BundleProduct);
			$output_string = preg_replace('/[^:]+::/', '', $output_string);
			preg_match_all('/{[^}]+}/', $output_string, $matches);
			foreach ($matches as $key => $optiondata) {
			  foreach ($optiondata as $key => $value) {
				$value = trim(preg_replace('/^\{|\}$/', '', $value));
				//  exterct option_id
				 $startPos = strpos($value, "option_id=");
				 $endPos = strpos($value, ":", $startPos);
				 $option_id = substr($value, $startPos + strlen("option_id="), $endPos - $startPos - strlen("option_id="));
				//  extract sections
				$sectionsPosition=strpos($value, "sections=");
				$section = substr($value, $sectionsPosition + strlen("sections="));
				$section_products = [];
				$pattern = '/section_id=(\d+):qty=(\d+)/';
				preg_match_all($pattern, $section, $section_products, PREG_SET_ORDER);
				$resultArray = [];
				foreach ($section_products as $match) {
					$resultArray[] = [
						'section_id' => $match[1],
						'qty' => $match[2]
					];
				}
			   $optionArray['BundleProducts'][] = ['option_id' => $option_id,"OptionProducts"=>$resultArray];
			  }
			}

		  return  $optionArray;	
		}


}
?>
