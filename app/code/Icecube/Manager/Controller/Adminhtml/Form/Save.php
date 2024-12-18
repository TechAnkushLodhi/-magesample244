<?php

namespace Icecube\Manager\Controller\Adminhtml\Form;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Icecube\Manager\Model\FormFactory;
use Icecube\Manager\Model\ResourceModel\Form as FormResource;
use Icecube\Manager\Model\ResourceModel\FormField as FormFieldResource;

class Save extends Action
{
    protected $formFactory;
    protected $formResource;
    protected $formFieldResource;

    public function __construct(
        Context $context,
        FormFactory $formFactory,
        FormResource $formResource,
        FormFieldResource $formFieldResource
    ) {
        parent::__construct($context);
        $this->formFactory = $formFactory;
        $this->formResource = $formResource;
        $this->formFieldResource = $formFieldResource;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            $form = $this->formFactory->create();
            $form->setData($data);

            try {
                $this->formResource->save($form);

                if (isset($data['dynamic_form_fields'])) {
                    foreach ($data['dynamic_form_fields'] as $fieldData) {
                        $field = $this->formFactory->create();
                        $field->setData($fieldData);
                        $field->setFormId($form->getId());
                        $this->formFieldResource->save($field);
                    }
                }

                $this->messageManager->addSuccessMessage(__('The form has been saved.'));
                $this->_redirect('*/*/edit', ['form_id' => $form->getId()]);
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the form.'));
            }

            $this->_redirect('*/*/edit', ['form_id' => $this->getRequest()->getParam('form_id')]);
            return;
        }

        $this->_redirect('*/*/');
    }
}
