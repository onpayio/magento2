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
namespace OnPay\OnPay\Observer;

use OnPay\OnPay\Model\Payment\OnPayPaymentMethod;
use OnPay\OnPay\Block\RedirectUrl;

class SalesOrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $_resultFactory;

    protected $cookieManager;
    
    protected $cookieMetadataFactory;
    
    protected $sessionManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->_resultFactory = $context->getResultFactory();
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $methodCode = $order->getPayment()->getMethodInstance()->getCode();

        if ($methodCode == OnPayPaymentMethod::CODE) {

            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration(RedirectUrl::COOKIE_DURATION)
                ->setPath('/')
                ->setDomain($this->sessionManager->getCookieDomain());

            $this->cookieManager->setPublicCookie(
                RedirectUrl::COOKIE_ORDER_ID,
                $order->getIncrementId(),
                $metadata
            );

        }

        return $this;
    }
}
