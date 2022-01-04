<?php

declare(strict_types=1);

namespace OnPay\OnPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

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
    const DELIVERY_DISABLED= 'onpay_delivery_disabled';
    const SECURE= 'onpay_3dsecure';
    const LANGUAGE= 'onpay_language';
    const DESIGN= 'onpay_design';
    const EXPIRATION= 'onpay_expiration';
    const HASH_MAKE= 'onpay_hmac_sha1';

    private $hashCodeBuildParams = [
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

    const AMOUNT_MUL = 100;


    const MOBILE_PAY_CHECKOUT = 'mobilepay_checkout';

    const API_URL = 'https://api.onpay.io/v1/transaction/';

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Payment\Model\Method\Logger $logger
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * isConfigValue function
     *
     * @param string $path
     * @param null $store
     * @return void
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
     * isEnable function
     *
     * @return boolean
     */
    public function isEnable()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/active');
    }

    /**
     * isTestMode function
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/test_mode');
    }

    /**
     * getGatewayId function
     *
     * @return string
     */
    public function getGatewayId()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/gateway_id');
    }

    /**
     * getWindowSecret function
     *
     * @return string
     */
    public function getWindowSecret()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/window_secret');
    }

    /**
     * getApiKey function
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/api_key');
    }

    /**
     * getPaymentWindowLanguage function
     *
     * @return string
     */
    public function getPaymentWindowLanguage()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/payment_window_language');
    }

    /**
     * getPaymentWindowLanguage function
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/instructions');
    }

    /**
     * getPaymentWindowLanguage function
     *
     * @return string
     */
    public function getType()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/type');
    }

    /**
     * getPaymentWindowLanguage function
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/method');
    }

    /**
     * getDeliveryDisabled function
     *
     * @return string
     */
    public function getDeliveryDisabled()
    {
        if($this->getMethod()== self::MOBILE_PAY_CHECKOUT) {
            return $this->isConfigValue('payment/onpaypaymentmethod/delivery_disabled');
        }
        return '';
    }

    /**
     * getSecure function
     *
     * @return string
     */
    public function getSecure()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/secure');
    }

    /**
     * getDesign function
     *
     * @return string
     */
    public function getDesign()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/design');
    }

    /**
     * getExpiration function
     *
     * @return string
     */
    public function getExpiration()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/expiration');
    }

    /**
     * getOrderStatusAfterPayment function
     *
     * @return string
     */
    public function getOrderStatusAfterPayment()
    {
        return $this->isConfigValue('payment/onpaypaymentmethod/order_status');
    }

    /**
     * getOrderStatusAfterPayment function
     *
     * @return boolean
     */
    public function isAutoCapture()
    {
        return (bool) $this->isConfigValue('payment/onpaypaymentmethod/auto_capture');
    }

    /**
     * getAcceptUrl function
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->urlBuilder->getUrl('onpay/accept');
    }

    /**
     * getDeclineUrl function
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->urlBuilder->getUrl('onpay/decline');
    }

    /**
     * getWebsiteUrl function
     *
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->urlBuilder->getUrl('/');
    }

    /**
     * getCallbacUrl function
     *
     * @return string
     */
    public function getCallbacUrl()
    {
        return $this->urlBuilder->getUrl('onpay/callback');
    }

    /**
     * buildHashCode function
     *
     * @param array $params
     * @return string
     */
    public function buildHashCode(array $params)
    {   
        ksort($params);

        $toHashArray = [];

        foreach($params as $key => $value){
            if (0 === strpos($key, 'onpay_') && self::HASH_MAKE !== $key && in_array($key, $this->hashCodeBuildParams)) {
                $toHashArray[$key] = $value;
            }
        }

        $queryString = strtolower(http_build_query($toHashArray));

        return hash_hmac('sha1', $queryString, $this->getWindowSecret());
    }

    public function checkHashCode($additionalInformation)
    {
        $hashCode= $this->buildHashCode($additionalInformation);

        //if($hashCode == $additionalInformation[self::HASH_MAKE]) {
            return true;
        //}
        return false;
    }

    public function connectToOnPayTransaction($type, $tranId, $method, $amount)
    {
        $postData =[];
        if($amount){
            $postData = [
                'data'=>[
                    'amount'=>(int)($amount*self::AMOUNT_MUL)
                ]
            ];
        }

        try{
            $apiUrl = self::API_URL.$tranId.'/'.$type;
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$this->getApiKey()
            ));

            $jsonResponse = curl_exec($ch);
            $response = json_decode($jsonResponse,true);

        }catch(\Exception $e) {
            $this->logger->debug($e->getMessage());
            $response=[
                'errors'=>[
                    [
                        'message'=>$e->getMessage()
                    ]
                ]
            ];
        }finally{
            return $response;
        }
        
    }
}
