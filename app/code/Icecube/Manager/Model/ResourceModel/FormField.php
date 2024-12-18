<?php

namespace Icecube\Manager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FormField extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_form_field', 'field_id');
    }
}
