<?php
namespace Icecube\Monitoring\Block\Adminhtml\Product;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;

class Index extends Template
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getMetrics()
    {
       return "This is testing metrics";
    }
}
