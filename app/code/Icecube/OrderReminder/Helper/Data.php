<?php

namespace Icecube\OrderReminder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const XML_PATH_GENERAL_ENABLE = 'icecube_mailsender/general/enable';
    const XML_PATH_MAIL_DATA_DAYS = 'icecube_mailsender/Mail_Data/NumberOfDays';
    const XML_PATH_MAIL_DATA_SENDER_NAME = 'icecube_mailsender/Mail_Data/SenderName';
    const XML_PATH_MAIL_DATA_SENDER_EMAIL = 'icecube_mailsender/Mail_Data/SenderEmail';

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

    public function getNumberOfDays()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAIL_DATA_DAYS);
    }

    public function getSenderName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAIL_DATA_SENDER_NAME);
    }

    public function getSenderEmail()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAIL_DATA_SENDER_EMAIL);
    }

}
