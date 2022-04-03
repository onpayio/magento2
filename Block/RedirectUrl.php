<?php

/**
 * OnPay Magento2 module
 * php version 7.4.27
 *
 * @category  Payment_Method
 * @package   Onpay_Magento
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

namespace OnPay\Magento2\Block;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;
use OnPay\Magento2\Helper\Config;
use OnPay\API\PaymentWindow;
use OnPay\API\PaymentWindow\PaymentInfo;
use OnPay\Magento2\Helper\Currency;
use OnPay\Magento2\Model\ManageOnPay;
use Sokil\IsoCodes\Database\Countries;

/**
 * RedirectUrl OnPay\Magento2\Block\RedirectUrl
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class RedirectUrl extends Template
{
    const COOKIE_ORDER_ID = 'onpay_order';
    const COOKIE_DURATION = 120;

    protected Config $helper;

    protected Currency $currencyHelper;

    protected Countries $isoCodesCountries;

    protected CookieManagerInterface $cookieManager;

    protected OrderFactory $orderFactory;

    protected RegionFactory $regionFactory;

    protected ManageOnPay $manageOnPay;

    protected PaymentWindow $paymentWindow;

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
        $this->createPaymentWindow();
    }

    protected function getOrderCookie()
    {
        $incrementId = $this->cookieManager->getCookie(self::COOKIE_ORDER_ID);
        $this->deleteOrderCookie();
        return $incrementId;
    }

    private function deleteOrderCookie()
    {
        $this->cookieManager->deleteCookie(self::COOKIE_ORDER_ID);
    }

    protected function getOrderDetails($orderId)
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);
        return $order;
    }

    private function getByAlpha2($code)
    {
        return $this->isoCodesCountries->getByAlpha2($code);
    }

    private function getRegion($regionId)
    {
        return $this->regionFactory->create()->load($regionId);
    }

    protected function createPaymentWindow()
    {
        $orderId = $this->getOrderCookie();
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

        // Delivery Disabled
        if ($this->helper->getDeliveryDisabled()) {
            $paymentWindow->setDeliveryDisabled($this->helper->getDeliveryDisabled());
        }

        $order = $this->getOrderDetails($orderId);
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

        $this->paymentWindow = $paymentWindow;
    }

    public function isValid()
    {
        return $this->paymentWindow->isValid();
    }

    public function getActionUrl()
    {
        return $this->paymentWindow->getActionUrl();
    }

    public function getFormFields() {
        return $this->paymentWindow->getFormFields();
    }
}
