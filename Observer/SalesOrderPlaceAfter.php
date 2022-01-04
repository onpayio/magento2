<?php

namespace OnPay\OnPay\Observer;

use OnPay\OnPay\Model\Payment\OnPayPaymentMethod;
use OnPay\OnPay\Block\RedirectUrl;

class SalesOrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    private $_resultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ){
        $this->_resultFactory = $context->getResultFactory();
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $order = $observer->getEvent()->getOrder();

        $methodCode = $order->getPayment()->getMethodInstance()->getCode();

        if($methodCode == OnPayPaymentMethod::CODE) {

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