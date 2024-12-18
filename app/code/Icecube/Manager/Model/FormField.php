<?php
namespace Icecube\Manager\Model;

use Magento\Framework\Model\AbstractModel;

class FormField extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Icecube\Manager\Model\ResourceModel\FormField');
    }
}
