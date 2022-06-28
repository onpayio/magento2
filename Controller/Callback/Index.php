<?php

/**
 * OnPay Magento2 module
 * php version 7.4.27
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author: Julian F. Christmas
 * Version: 1.0.0
 * Author URI: https://intelligodenmark.dk
 */

namespace OnPay\Magento2\Controller\Callback;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use OnPay\Magento2\Model\ManageOnPay;

/**
 * Index  OnPay\Magento2\Controller\Callback\Index
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
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
     * Construct function
     *
     * @param Context       $context       Constructor modification point for Magento\Framework\App\Helper.
     * @param PageFactory   $pageFactory   Page Factory
     * @param ResultFactory $resultFactory Result Factory
     * @param ManageOnPay   $manageOnPay   OnPay Class
     */
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
     * Execute Function
     *
     * @return void
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/callback-called.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('callback-called');

        if (isset($post['onpay_uuid'])) {
            $response = $this->manageOnPay->accept($post);

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/callback-accept.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('callback-accept');
        } else {
            $response = $this->manageOnPay->decline($post);

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/callback-decline.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('callback-decline');
        }
    }
}
