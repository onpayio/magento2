<?php
/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   Onpay_Magento
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   GPL-2.0+
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author: Julian F. Christmas
 * Version: 1.0.0
 * Author URI: https://intelligodenmark.dk
 */
namespace OnPay\OnPay\Controller\Accept;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    
    protected $manageOnPay;
    
    private $context;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \OnPay\OnPay\Model\ManageOnPay $manageOnPay
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        
        $post = $this->getRequest()->getParams();

        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl('/checkout/onepage/success');

        $response = $this->manageOnPay->accept($post);

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
