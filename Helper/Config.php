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

declare(strict_types=1);

namespace OnPay\Magento2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Config  OnPay\Magento2\Helper\Config
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class Config extends AbstractHelper
{
    // Onpay Input
    const GATEWAY_ID = 'onpay_gatewayid';
    const CURRENCY = 'onpay_currency';
    const AMOUNT = 'onpay_amount';
    const REFERENCE = 'onpay_reference';
    const ACCEPT_URL = 'onpay_accepturl';
    const BILLING_CITY = 'onpay_info_billing_address_city';
    const BILLING_COUNTRY = 'onpay_info_billing_address_country';
    const BILLING_LINE1 = 'onpay_info_billing_address_line1';
    const BILLING_LINE2 = 'onpay_info_billing_address_line2';
    const BILLING_LINE3 = 'onpay_info_billing_address_line3';
    const BILLING_POSTCODE = 'onpay_info_billing_address_postal_code';
    const BILLING_STATE = 'onpay_info_billing_address_state';
    const SHIPPING_CITY = 'onpay_info_shipping_address_city';
    const SHIPPING_COUNTRY = 'onpay_info_shipping_address_country';
    const SHIPPING_LINE1 = 'onpay_info_shipping_address_line1';
    const SHIPPING_LINE2 = 'onpay_info_shipping_address_line2';
    const SHIPPING_LINE3 = 'onpay_info_shipping_address_line3';
    const SHIPPING_POSTCODE = 'onpay_info_shipping_address_postal_code';
    const SHIPPING_STATE = 'onpay_info_shipping_address_state';
    const INFO_NAME = 'onpay_info_name';
    const INFO_EMAIL = 'onpay_info_email';
    const INFO_REORDER = 'onpay_info_reorder';
    const INFO_GIFT_AMOUNT = 'onpay_info_gift_card_amount';
    const INFO_GIFT_COUNT = 'onpay_info_gift_card_count';
    const INFO_DECLINE_URL = 'onpay_declineurl';
    const INFO_CALLBACK_URL = 'onpay_callbackurl';
    const TEST_MODE = 'onpay_testmode';
    const WEBSITE = 'onpay_website';
    const TYPE = 'onpay_type';
    const METHOD = 'onpay_method';
    const DELIVERY_DISABLED = 'onpay_delivery_disabled';
    const SECURE = 'onpay_3dsecure';
    const LANGUAGE = 'onpay_language';
    const DESIGN = 'onpay_design';
    const EXPIRATION = 'onpay_expiration';
    const HASH_MAKE = 'onpay_hmac_sha1';

    private $_hashCodeBuildParams = [
        self::GATEWAY_ID,
        self::CURRENCY,
        self::AMOUNT,
        self::REFERENCE,
        self::ACCEPT_URL,
        self::BILLING_CITY,
        self::BILLING_COUNTRY,
        self::BILLING_LINE1,
        self::BILLING_LINE2,
        self::BILLING_LINE3,
        self::BILLING_POSTCODE,
        self::BILLING_STATE,
        self::SHIPPING_CITY,
        self::SHIPPING_COUNTRY,
        self::SHIPPING_LINE1,
        self::SHIPPING_LINE2,
        self::SHIPPING_LINE3,
        self::SHIPPING_POSTCODE,
        self::SHIPPING_STATE,
        self::INFO_NAME,
        self::INFO_EMAIL,
        self::INFO_REORDER,
        self::INFO_GIFT_AMOUNT,
        self::INFO_GIFT_COUNT,
        self::INFO_DECLINE_URL,
        self::INFO_CALLBACK_URL,
        self::TEST_MODE,
        self::WEBSITE,
        self::TYPE,
        self::METHOD,
        self::DELIVERY_DISABLED,
        self::SECURE,
        self::LANGUAGE,
        self::DESIGN,
        self::EXPIRATION,
    ];

    const MOBILE_PAY_CHECKOUT = 'mobilepay_checkout';

    const API_URL = 'https://api.onpay.io/v1/transaction/';

    protected $_scopeConfig;

    protected $urlBuilder;

    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * Construct Function
     *
     * @param \Magento\Framework\App\Helper\Context $context    Constructor modification point for Magento\Framework\App\Helper.
     * @param \Magento\Framework\UrlInterface       $urlBuilder Url Builder
     * @param \Magento\Payment\Model\Method\Logger  $logger     Class Logger for payment related information (request, response, etc.) which is used for debug
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->_curl = $curl;
        parent::__construct($context);
    }

    /**
     * Function isConfigValue
     *
     * @param [type] $path  Path
     * @param [type] $store Store
     *
     * @return boolean
     */
    public function isConfigValue($path, $store = null)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Function isEnable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/active');
    }

    /**
     * Function isTestMode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/test_mode');
    }

    /**
     * Function getGatewayId
     *
     * @return string
     */
    public function getGatewayId()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/gateway_id');
    }

    /**
     * Function getWindowSecret
     *
     * @return string
     */
    public function getWindowSecret()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/window_secret');
    }

    /**
     * Function getApiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/api_key');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getPaymentWindowLanguage()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/payment_window_language');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/instructions');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getType()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/type');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/method');
    }

    /**
     * Function getDeliveryDisabled
     *
     * @return string
     */
    public function getDeliveryDisabled()
    {
        if ($this->getMethod() == self::MOBILE_PAY_CHECKOUT) {
            return $this->isConfigValue('payment/onpaypaymentmethod/delivery_disabled');
        }
        return '';
    }

    /**
     * Function getSecure
     *
     * @return string
     */
    public function getSecure()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/secure');
    }

    /**
     * Function getDesign
     *
     * @return string
     */
    public function getDesign()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/design');
    }

    /**
     * Function getExpiration
     *
     * @return string
     */
    public function getExpiration()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/expiration');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return string
     */
    public function getOrderStatusAfterPayment()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/order_status');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return boolean
     */
    public function isAutoCapture()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/auto_capture');
    }

    /**
     * Function getAcceptUrl
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->urlBuilder->getUrl('onpay/accept');
    }

    /**
     * Function getDeclineUrl
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->urlBuilder->getUrl('onpay/decline');
    }

    /**
     * Function getWebsiteUrl
     *
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->urlBuilder->getUrl('/');
    }

    /**
     * Function getCallbacUrl
     *
     * @return string
     */
    public function getCallbacUrl()
    {
        return $this->urlBuilder->getUrl('onpay/callback');
    }

    /**
     * Function buildHashCode
     *
     * @param array $params Params
     *
     * @return string
     */
    public function buildHashCode(array $params)
    {
        ksort($params);

        $toHashArray = [];

        foreach ($params as $key => $value) {
            if (0 === strpos($key, 'onpay_') && self::HASH_MAKE !== $key && in_array($key, $this->_hashCodeBuildParams)) {
                $toHashArray[$key] = $value;
            }
        }

        $queryString = strtolower(http_build_query($toHashArray));

        return hash_hmac('sha1', $queryString, $this->getWindowSecret());
    }

    /**
     * Function checkHashCode
     *
     * @param [type] $additionalInformation Additional Information
     *
     * @return void
     */
    public function checkHashCode($additionalInformation)
    {
        $hashCode = $this->buildHashCode($additionalInformation);

        //if($hashCode == $additionalInformation[self::HASH_MAKE]) {
        return true;
        //}
        //return false;
    }

    /**
     * Function connectToOnPayTransaction
     *
     * @param [type] $type   Type
     * @param [type] $tranId Transaction Id
     * @param [type] $method Method
     * @param [type] $amount Amount
     *
     * @return void
     */
    public function connectToOnPayTransaction($type, $tranId, $method, $amount)
    {

        $minor_units = $this->isConfigValue('payment/onpaypaymentmethod/minor_units');

        $postData = [];
        if ($amount) {
            $postData = [
                'data' => [
                    'amount' => (int)($amount * $minor_units)
                ]
            ];
        }

        try {
            $apiUrl = self::API_URL . $tranId . '/' . $type;

            $headers = [
                "Authorization" => "Bearer {$this->getApiKey()}",
                "Content-Type" => "application/json"
            ];

            $this->_curl->setHeaders($headers);
            $this->_curl->post($apiUrl, $postData);

            $response = $this->_curl->getBody();
            $response = json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $response = [
                'errors' => [
                    [
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        } finally {
            return $response;
        }
    }
}
