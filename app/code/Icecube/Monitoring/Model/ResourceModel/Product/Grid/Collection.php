<?php
namespace Icecube\Monitoring\Model\ResourceModel\Product\Grid;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class Collection extends ProductCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
    }

    public function getSoldAndNotSoldProducts($days)
    {
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['entity_id', 'sku', 'name'])
            ->columns(
                new \Zend_Db_Expr("SUM(IF(sales_order_item.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY), 1, 0)) AS sold_count"),
                'main_table'
            )
            ->joinLeft(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                'e.entity_id = sales_order_item.product_id',
                []
            )
            ->group('e.entity_id');
            
        return $this;
    }
}
