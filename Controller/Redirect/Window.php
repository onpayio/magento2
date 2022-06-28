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
namespace OnPay\Magento2\Controller\Redirect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Window extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        return $this->_pageFactory->create();
    }
}
