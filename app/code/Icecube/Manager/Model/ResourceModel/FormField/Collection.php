<?php

namespace Icecube\Manager\Model\ResourceModel\FormField;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Icecube\Manager\Model\FormField as FormFieldModel;
use Icecube\Manager\Model\ResourceModel\FormField as FormFieldResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(FormFieldModel::class, FormFieldResource::class);
    }
}
