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

namespace OnPay\OnPay\Block;

use OnPay\OnPay\Helper\Config;

/**
 * RedirectUrl OnPay\OnPay\Block\RedirectUrl
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class RedirectUrl extends \Magento\Framework\View\Element\Template
{
    const COOKIE_ORDER_ID = 'onpay_order';
    const COOKIE_DURATION = 120;
    const INFO_GIFT_AMOUNT = 'onpay_info_gift_card_amount';
    const INFO_GIFT_COUNT = 'onpay_info_gift_card_count';

    protected $helper;

    protected $isoCodesCountries;

    protected $cookieManager;

    protected $orderFactory;

    protected $customerRepositoryInterface;

    protected $regionFactory;

    protected $manageOnPay;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Config $helper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Sokil\IsoCodes\Database\Countries $isoCodesCountries,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \OnPay\OnPay\Model\ManageOnPay $manageOnPay
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
        $this->orderFactory = $orderFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->isoCodesCountries = $isoCodesCountries;
        $this->regionFactory = $regionFactory;
        $this->manageOnPay = $manageOnPay;
    }

    public function getGatewayId()
    {
        return $this->helper->getGatewayId();
    }

    public function getHashCode($params)
    {
        return $this->helper->buildHashCode($params);
    }

    public function getAcceptUrl()
    {
        return $this->helper->getAcceptUrl();
    }

    public function getOrderCookie()
    {
        $incrementId = $this->cookieManager->getCookie(self::COOKIE_ORDER_ID);
        $this->deleteOrderCookie();
        return $incrementId;
    }

    private function deleteOrderCookie()
    {
        $this->cookieManager->deleteCookie(self::COOKIE_ORDER_ID);
    }

    public function getOrderDetails($orderId)
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderId);
        return $order;
    }

    public function getCustomerDetails($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    public function getByAlpha2($code)
    {
        return $this->isoCodesCountries->getByAlpha2($code);
    }

    public function getRegion($regionId)
    {
        return $this->regionFactory->create()->load($regionId);
    }

    public function getPostParams($orderId, $reorder)
    {
        $order = $this->getOrderDetails($orderId);

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $params = [
            Config::GATEWAY_ID => $this->getGatewayId(),
            Config::CURRENCY => $order->getOrderCurrencyCode(),
            Config::AMOUNT => (int)($order->getGrandTotal() * 100),
            Config::REFERENCE => $order->getIncrementId(),
            Config::ACCEPT_URL => $this->getAcceptUrl(),
            Config::BILLING_CITY => $billingAddress->getCity(),
            Config::BILLING_COUNTRY => $this->getByAlpha2($billingAddress->getCountryId())->getNumericCode(),
            Config::BILLING_POSTCODE => $billingAddress->getPostcode(),
            Config::SHIPPING_CITY => $shippingAddress->getCity(),
            Config::SHIPPING_COUNTRY => $this->getByAlpha2($shippingAddress->getCountryId())->getNumericCode(),
            Config::SHIPPING_POSTCODE => $shippingAddress->getPostcode(),
            Config::INFO_NAME => $order->getCustomerName(),
            Config::INFO_EMAIL => $order->getCustomerEmail(),
            Config::INFO_REORDER => $reorder,
            Config::INFO_DECLINE_URL => $this->helper->getDeclineUrl(),
            Config::INFO_CALLBACK_URL => $this->helper->getCallbacUrl(),
            Config::TEST_MODE => $this->helper->isTestMode(),
            Config::WEBSITE => $this->helper->getWebsiteUrl(),
            Config::TYPE => $this->helper->getType(),
            Config::SECURE => $this->helper->getSecure(),
            Config::LANGUAGE => $this->helper->getPaymentWindowLanguage(),
            Config::DESIGN => $this->helper->getDesign(),
            Config::EXPIRATION => $this->helper->getExpiration()
        ];

        

        // Delivery Disabled
        if ($this->helper->getDeliveryDisabled()) {
            $params[Config::DELIVERY_DISABLED] = $this->helper->getDeliveryDisabled();
        }

        // Region
        /* if($billingAddress->getRegionId()){
            $params[Config::BILLING_STATE] = $this->getRegion($billingAddress->getRegionId())->getCode();
        } */

        if ($billingAddress->getRegionId()) {
            $code = $this->getRegion($billingAddress->getRegionId())->getCode();
            if (false !== strpos($code, '-')) {
                $code = substr($code, strpos($code, '-') + 1);
            }
            $params[Config::BILLING_STATE] = $code;
        }
        if ($shippingAddress->getRegionId()) {
            $code = $this->getRegion($shippingAddress->getRegionId())->getCode();
            if (false !== strpos($code, '-')) {
                $code = substr($code, strpos($code, '-') + 1);
            }
            $params[Config::SHIPPING_STATE] = $code;
        }

        /* if($shippingAddress->getRegionId()){
            $params[Config::SHIPPING_STATE] = $this->getRegion($shippingAddress->getRegionId())->getCode();
        } */

        // Street For Billing Address
        if ($billingAddress->getStreet()) {
            $street = $billingAddress->getStreet();
            $params[Config::BILLING_LINE1] = $street[0];
            if (isset($street[1])) {
                $params[Config::BILLING_LINE2] = $street[1];
            }
            if (isset($street[2])) {
                $params[Config::BILLING_LINE3] = $street[2];
            }
        }

        // Street For Shipping Address
        if ($shippingAddress->getStreet()) {
            $street = $shippingAddress->getStreet();
            $params[Config::SHIPPING_LINE1] = $street[0];
            if (isset($street[1])) {
                $params[Config::SHIPPING_LINE2] = $street[1];
            }
            if (isset($street[2])) {
                $params[Config::SHIPPING_LINE3] = $street[2];
            }
        }

        // Gift
        $params = $this->getGiftDetails($params, $order);

        // Create Hash Code
        $params[Config::HASH_MAKE] = $this->getHashCode($params);

        // Update Payment Method
        $this->manageOnPay->updatePaymentAdditionalInformation($params, $order->getPayment());

        return $params;
    }

    private function getGiftDetails($params, $order)
    {
        // Gift
        if ($order->getGiftcertAmount()) {
            $params[self::INFO_GIFT_AMOUNT] = (int)$order->getGiftcertAmount();
        }

        if ($order->getGiftCards()) {
            $params[self::INFO_GIFT_COUNT] = $order->getGiftCards();
        }

        return $params;
    }
}
