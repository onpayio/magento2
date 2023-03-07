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

declare(strict_types=1);

namespace OnPay\Magento2\Helper;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Store\Model\ScopeInterface;
use OnPay\Magento2\Model\Payment\OnPayMobilePayMethod;

class Config extends AbstractHelper
{
    const PLUGIN_VERSION = '1.0.0';
    const MOBILE_PAY_CHECKOUT = 'mobilepay_checkout';
    const LOGO_DIR = 'images/';
    const MODULE_NAME = 'OnPay_Magento2';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResourceConfig
     */
    protected $_resourceConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var string|null
     */
    private $refreshToken = null;

    /**
     * Construct Function
     *
     * @param Context                  $context         Constructor modification point for Magento\Framework\App\Helper.
     * @param ResourceConfig           $resourceConfig
     * @param UrlInterface             $urlBuilder      Url Builder
     * @param Logger                   $logger          Class Logger for payment related information (request, response, etc.) which is used for debug
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context                  $context,
        ResourceConfig           $resourceConfig,
        UrlInterface             $urlBuilder,
        Logger                   $logger,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_resourceConfig = $resourceConfig;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * Function getConfigValue
     *
     * @param string $path Path
     *
     * @return mixed
     */
    protected function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function getConfigValue
     *
     * @param string $path  Path
     * @param string $value Value
     */
    protected function setConfigValue($path, $value)
    {
        $this->_resourceConfig->saveConfig(
            $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @param  string $code
     * @param  string $field
     * @return mixed
     */
    protected function getPaymentConfigValue($code, $field)
    {
        $path = 'payment/' . $code . '/' . $field;
        return $this->getConfigValue($path);
    }

    /**
     * Function isEnable
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->getConfigValue('onpayio/payment/enabled');
    }

    /**
     * Function isTestMode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return (bool) $this->getConfigValue('onpayio/payment/test_mode');
    }

    /**
     * Function getRefreshToken
     * Store the token internally, for it to be available for subsequent requests without page reload.
     *
     * @return string
     */
    public function getRefreshToken()
    {
        if (null === $this->refreshToken) {
            $this->refreshToken = (string) $this->getConfigValue('onpayio/payment/refresh_token');
        }
        return $this->refreshToken;
    }

    /**
     * Function setRefreshToken
     *
     * @param string $token
     */
    public function setRefreshToken($token)
    {
        $this->refreshToken = $token;
        $this->setConfigValue('onpayio/payment/refresh_token', $token);
    }

    /**
     * Function getGatewayId
     *
     * @return string
     */
    public function getGatewayId()
    {
        return (string) $this->getConfigValue('onpayio/payment/gateway_id');
    }

    /**
     * Function setGatewayId
     *
     * @param string $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->setConfigValue('onpayio/payment/gateway_id', $gatewayId);
    }

    /**
     * Function getWindowSecret
     *
     * @return string
     */
    public function getWindowSecret()
    {
        return (string) $this->getConfigValue('onpayio/payment/window_secret');
    }

    /**
     * Function setWindowSecret
     *
     * @param string $windowSecret
     */
    public function setWindowSecret($windowSecret)
    {
        $this->setConfigValue('onpayio/payment/window_secret', $windowSecret);
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getPaymentWindowLanguage()
    {
        return (string) $this->getConfigValue('onpayio/payment/payment_window_language');
    }

    /**
     * Function getType
     *
     * @return string
     */
    public function getType()
    {
        return (string) $this->getConfigValue('onpayio/payment/type');
    }

    /**
     * Function getSecure
     *
     * @return string
     */
    public function getSecure()
    {
        return (string) $this->getConfigValue('onpayio/payment/secure');
    }

    /**
     * Function getDesign
     *
     * @return string
     */
    public function getDesign()
    {
        return (string) $this->getConfigValue('onpayio/payment/design');
    }

    /**
     * Function getExpiration
     *
     * @return string
     */
    public function getExpiration()
    {
        return (string) $this->getConfigValue('onpayio/payment/expiration');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return string
     */
    public function getOrderStatusAfterPayment()
    {
        return (string) $this->getConfigValue('onpayio/payment/order_status');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return boolean
     */
    public function isAutoCapture()
    {
        return (bool) $this->getConfigValue('onpayio/payment/auto_capture');
    }

    /**
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return $this->urlBuilder->getUrl('onpay/auth/index');
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
        return $this->urlBuilder->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Function getCallbacUrl
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->urlBuilder->getUrl('onpay/callback');
    }

    /**
     * @param  string $code
     * @return string
     */
    public function getInstructions($code)
    {
        return (string) $this->getPaymentConfigValue($code, 'instructions');
    }

    /**
     * @param  string $code
     * @return string|null
     */
    public function getLogo($code)
    {
        $logoUrl = null;

        $file = (string) $this->getPaymentConfigValue($code, 'logo');
        if (!empty($file)) {
            $logoUrl = self::MODULE_NAME . '/' . self::LOGO_DIR . trim($file);
        }

        return $logoUrl;
    }

    /**
     * @param  string $code
     * @return string
     */
    public function getLogoTitle($code)
    {
        return (string) $this->getPaymentConfigValue($code, 'logo_title');
    }
}
