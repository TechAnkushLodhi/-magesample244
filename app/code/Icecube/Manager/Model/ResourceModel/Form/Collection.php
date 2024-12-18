<?php

namespace Icecube\Manager\Model\ResourceModel\Form;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Icecube\Manager\Model\Form as FormModel;
use Icecube\Manager\Model\ResourceModel\Form as FormResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(FormModel::class, FormResource::class);
    }
}
