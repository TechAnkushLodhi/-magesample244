<?php
namespace Icecube\Monitoring\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        // $resultPage->setActiveMenu('Icecube_Monitoring::IcecubeMonitoring');
        $resultPage->getConfig()->getTitle()->prepend(__('Products Sold/Not Sold Metrics'));

        return $resultPage;
    }
}
