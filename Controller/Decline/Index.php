<?php
/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   OnPay_Magento2
 * @copyright OnPay
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author URI: https://onpay.io
 */

namespace OnPay\Magento2\Controller\Decline;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use OnPay\Magento2\Model\ManageOnPay;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var ManageOnPay
     */
    protected $manageOnPay;
    
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ResultFactory $resultFactory,
        ManageOnPay $manageOnPay
    ) {
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/');

        $response = $this->manageOnPay->decline($post);

        if (!$response) {
            $this->messageManager->addErrorMessage(
                __("Something went wrong with your request.")
            );
        } else {
            $this->messageManager->addErrorMessage(
                __("The payment was declined and therefor we have cancelled your order.")
            );
        }

        return $redirect;
    }
}
