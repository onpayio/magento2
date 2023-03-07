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

namespace OnPay\Magento2\Block;

use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use OnPay\API\Exception\InvalidFormatException;
use OnPay\Magento2\Helper\Config;
use OnPay\API\PaymentWindow;
use OnPay\API\PaymentWindow\PaymentInfo;
use OnPay\Magento2\Helper\Currency;
use OnPay\Magento2\Model\ManageOnPay;
use OnPay\Magento2\Model\Payment\OnPaySelectMethod;
use OnPay\Magento2\Model\Payment\OnPayMobilePayCheckoutMethod;
use Sokil\IsoCodes\Database\Countries;

class RedirectUrl extends Template
{
    const COOKIE_ORDER_ID = 'onpay_order';
    const COOKIE_DURATION = 120;

    /**
     * @var string|null
     */
    protected $incrementId;

    /**
     * @var Config
     */
    protected $helper;

    /**
     * @var Currency
     */
    protected $currencyHelper;

    /**
     * @var Countries
     */
    protected $isoCodesCountries;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var ManageOnPay
     */
    protected $manageOnPay;

    /**
     * @var PaymentWindow|null
     */
    protected $paymentWindow;

    /**
     * @param Context                $context
     * @param Config                 $helper
     * @param CookieManagerInterface $cookieManager
     * @param OrderFactory           $orderFactory
     * @param Countries              $isoCodesCountries
     * @param RegionFactory          $regionFactory
     * @param ManageOnPay            $manageOnPay
     */
    public function __construct(
        Context                     $context,
        Config                      $helper,
        CookieManagerInterface      $cookieManager,
        OrderFactory                $orderFactory,
        Countries                   $isoCodesCountries,
        RegionFactory               $regionFactory,
        ManageOnPay                 $manageOnPay
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
        $this->orderFactory = $orderFactory;
        $this->isoCodesCountries = $isoCodesCountries;
        $this->regionFactory = $regionFactory;
        $this->manageOnPay = $manageOnPay;
        $this->currencyHelper = new Currency();
        $this->paymentWindow = null;
    }

    /**
     * @return string|null
     * @throws InputException
     * @throws FailureToSendException
     */
    public function getOrderId()
    {
        if (null === $this->incrementId) {
            $this->incrementId = $this->cookieManager->getCookie(self::COOKIE_ORDER_ID);
            $this->deleteOrderCookie();
        }
        return $this->incrementId;
    }

    /**
     * @return void
     * @throws InputException
     * @throws FailureToSendException
     */
    private function deleteOrderCookie()
    {
        $this->cookieManager->deleteCookie(self::COOKIE_ORDER_ID);
    }

    /**
     * @param  $orderId
     * @return Order
     */
    protected function getOrderDetails($orderId)
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);
        return $order;
    }

    /**
     * @param  $code
     * @return Countries\Country|null
     */
    private function getByAlpha2($code)
    {
        return $this->isoCodesCountries->getByAlpha2($code);
    }

    /**
     * @param  $regionId
     * @return Region
     */
    private function getRegion($regionId)
    {
        return $this->regionFactory->create()->load($regionId);
    }

    /**
     * @return PaymentWindow|null
     * @throws InvalidFormatException
     */
    protected function getPaymentWindow()
    {
        if (null === $this->paymentWindow) {
            $this->paymentWindow = $this->createPaymentWindow();
        }
        return $this->paymentWindow;
    }

    /**
     * @return PaymentWindow|null
     * @throws InvalidFormatException
     */
    protected function createPaymentWindow()
    {
        $orderId = $this->getOrderId();
        if (null === $orderId) {
            //Cannot redirect: Could not get Order ID
            return null;
        }

        $order = $this->getOrderDetails($orderId);

        $payment = $order->getPayment();
        if (null !== $payment->getLastTransId()) {
            //Cannot redirect: This order is already processed
            return null;
        }

        $payment->getTransaction();

        $reorder = 'N';
        $paymentWindow = new PaymentWindow();
        $paymentWindow->setGatewayId($this->helper->getGatewayId());
        $paymentWindow->setSecret($this->helper->getWindowSecret());
        $paymentWindow->setTestMode($this->helper->isTestMode());
        $paymentWindow->setPlatform('Magento 2', Config::PLUGIN_VERSION, $this->helper->getMagentoVersion());

        $paymentWindow->setAcceptUrl($this->helper->getAcceptUrl());
        $paymentWindow->setDeclineUrl($this->helper->getDeclineUrl());
        $paymentWindow->setCallbackUrl($this->helper->getCallbackUrl());
        $paymentWindow->setWebsite($this->helper->getWebsiteUrl());
        $paymentWindow->setType($this->helper->getType());
        $paymentWindow->set3DSecure($this->helper->getSecure());
        $paymentWindow->setLanguage($this->helper->getPaymentWindowLanguage());
        $paymentWindow->setDesign($this->helper->getDesign());
        $paymentWindow->setExpiration($this->helper->getExpiration());

        $method = $payment->getMethod();
        if ($method !== OnPaySelectMethod::METHOD_CODE) {
            // Remove onpay prefix from method code
            $paymentWindow->setMethod(str_replace('onpay_', '', $method));
        }

        $minorAmount = $this->currencyHelper->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');

        $paymentWindow->setCurrency($order->getOrderCurrencyCode());
        $paymentWindow->setAmount($minorAmount);
        $paymentWindow->setReference($order->getIncrementId());

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $info = new PaymentInfo();
        $info->setName($order->getCustomerName());
        $info->setEmail($order->getCustomerEmail());
        $info->setReorder($reorder);

        // Billing address
        if ($billingAddress->getStreet()) {
            $street = $shippingAddress->getStreet();
            $info->setBillingAddressLine1($street[0]);
            if (isset($street[1])) {
                $info->setBillingAddressLine2($street[1]);
            }
            if (isset($street[2])) {
                $info->setBillingAddressLine3($street[2]);
            }
        }
        $info->setBillingAddressCity($billingAddress->getCity());
        $info->setBillingAddressPostalCode($billingAddress->getPostcode());
        $info->setBillingAddressCountry($this->getByAlpha2($billingAddress->getCountryId())->getNumericCode());
        if ($billingAddress->getRegionId()) {
            $code = $this->getRegion($billingAddress->getRegionId())->getCode();
            if (false !== strpos($code, '-')) {
                $code = substr($code, strpos($code, '-') + 1);
            }
            $info->setBillingAddressState($code);
        }

        // Shipping address
        if ($shippingAddress->getStreet()) {
            $street = $shippingAddress->getStreet();
            $info->setShippingAddressLine1($street[0]);
            if (isset($street[1])) {
                $info->setShippingAddressLine2($street[1]);
            }
            if (isset($street[2])) {
                $info->setShippingAddressLine3($street[2]);
            }
        }
        $info->setShippingAddressCity($shippingAddress->getCity());
        $info->setShippingAddressPostalCode($shippingAddress->getPostcode());
        $info->setShippingAddressCountry($this->getByAlpha2($shippingAddress->getCountryId())->getNumericCode());
        if ($shippingAddress->getRegionId()) {
            $code = $this->getRegion($shippingAddress->getRegionId())->getCode();
            if (false !== strpos($code, '-')) {
                $code = substr($code, strpos($code, '-') + 1);
            }
            $info->setShippingAddressState($code);
        }

        // Gift
        if ($order->getGiftcertAmount()) {
            $info->setGiftCardAmount((int)$order->getGiftcertAmount());
        }

        if ($order->getGiftCards()) {
            $info->setGiftCardCount($order->getGiftcards());
        }

        $paymentWindow->setInfo($info);

        // Update Payment Method
        $this->manageOnPay->updatePaymentAdditionalInformation($paymentWindow->getFormFields(), $order->getPayment());

        return $paymentWindow;
    }

    /**
     * @return bool
     */
    public function isAlreadyPaid()
    {
        $orderId = $this->getOrderId();
        if (null === $orderId) {
            //Cannot redirect: Could not get Order ID
            return false;
        }

        $order = $this->getOrderDetails($orderId);
        $payment = $order->getPayment();
        if (null !== $payment->getLastTransId()) {
            //Cannot redirect: This order is already processed
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $paymentWindow = $this->getPaymentWindow();
        if (null === $paymentWindow) {
            return false;
        }
        return $paymentWindow->isValid();
    }

    /**
     * @return string|null
     */
    public function getActionUrl()
    {
        $paymentWindow = $this->getPaymentWindow();
        if (null === $paymentWindow) {
            return null;
        }
        return $paymentWindow->getActionUrl();
    }

    /**
     * @return array|null
     */
    public function getFormFields()
    {
        $paymentWindow = $this->getPaymentWindow();
        if (null === $paymentWindow) {
            return null;
        }
        return $paymentWindow->getFormFields();
    }
}
