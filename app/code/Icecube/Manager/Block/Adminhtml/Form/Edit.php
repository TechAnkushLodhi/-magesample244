<?php
namespace Icecube\Manager\Block\Adminhtml\Form;

class Edit extends \Magento\Backend\Block\Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Icecube_Manager::department.phtml');
    }

}
