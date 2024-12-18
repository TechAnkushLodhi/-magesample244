<?php

namespace Order\Programmtic\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const XML_PATH_GENERAL_ENABLE = 'multiorder_section/general/enable';

    protected $scopeConfig;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_ENABLE);
    }
}
