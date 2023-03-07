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

namespace OnPay\Magento2\Controller\Accept;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
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

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context       $context
     * @param PageFactory   $pageFactory
     * @param ResultFactory $resultFactory
     * @param ManageOnPay   $manageOnPay
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ResultFactory $resultFactory,
        ManageOnPay $manageOnPay
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        
        $post = $this->getRequest()->getParams();

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/onepage/success');

        $response = $this->manageOnPay->accept($post, false);

        if (!$response) {
            $this->messageManager->addErrorMessage(
                __("Something went wrong with your request.")
            );
        } else {
            $this->messageManager->addSuccessMessage(
                __("Order placed successfully.")
            );
        }

        return $redirect;
    }
}
