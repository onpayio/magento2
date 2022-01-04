<?php
namespace OnPay\OnPay\Controller\Accept;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \OnPay\OnPay\Model\ManageOnPay $manageOnPay
    ){
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->resultFactory = $resultFactory;
        return parent::__construct($context);
	}

	public function execute()
	{
        $post = $this->getRequest()->getParams();

        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/onepage/success');

        $response = $this->manageOnPay->accept($post);

        if(!$response) {
            $this->messageManager->addErrorMessage(
                __("Something went wrong with your request")
            );
        }else{
            $this->messageManager->addSuccessMessage(
                __("Order placed successfully.")
            );
        }

        return $redirect;
	}
}
