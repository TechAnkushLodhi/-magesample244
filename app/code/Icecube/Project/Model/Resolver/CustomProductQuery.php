<?php
namespace Icecube\Project\Model\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
class CustomProductQuery implements ResolverInterface
{
/**
* @var ValueFactory
*/
private $valueFactory;
public function __construct(ValueFactory $valueFactory)
{
$this->valueFactory = $valueFactory;
}
public function resolve(Field $field, $context, $info, array $value = null, array $args = null)
{
// Implement your logic to fetch and return the data here
$productId = $args["id"];
// Perform your data retrieval logic here
$productData = [
"id" => $productId,
"name" => "Sample Product",
"price" => 29.99,
];
$result = function () use ($productData) {
return !empty($productData) ? $productData : [];
};
return $this->valueFactory->create($result);
}
}