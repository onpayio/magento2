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

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Store\Model\ScopeInterface;

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
    const PLUGIN_VERSION = '1.0.0';
    const MOBILE_PAY_CHECKOUT = 'mobilepay_checkout';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var ProductMetadataInterface
     */
    protected ProductMetadataInterface $productMetadata;

    /**
     * @var string|null
     */
    private ?string $refreshToken = null;

    /**
     * Construct Function
     *
     * @param Context $context Constructor modification point for Magento\Framework\App\Helper.
     * @param ResourceConfig $resourceConfig
     * @param UrlInterface $urlBuilder Url Builder
     * @param Logger $logger Class Logger for payment related information (request, response, etc.) which is used for debug
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
     * @param string $path  Path
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
     * @param mixed  $value Value
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
     * Function isEnable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return (bool) $this->getConfigValue('payment/onpaypaymentmethod/active');
    }

    /**
     * Function isTestMode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return (bool) $this->getConfigValue('payment/onpaypaymentmethod/test_mode');
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
            $this->refreshToken = $this->getConfigValue('payment/onpaypaymentmethod/refresh_token');
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
        $this->setConfigValue('payment/onpaypaymentmethod/refresh_token', $token);
    }

    /**
     * Function getGatewayId
     *
     * @return string
     */
    public function getGatewayId()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/gateway_id');
    }

    /**
     * Function setGatewayId
     *
     * @param string $gatewayId
     */
    public function setGatewayId($gatewayId)
    {
        $this->setConfigValue('payment/onpaypaymentmethod/gateway_id', $gatewayId);
    }

    /**
     * Function getWindowSecret
     *
     * @return string
     */
    public function getWindowSecret()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/window_secret');
    }

    /**
     * Function setWindowSecret
     *
     * @param string $windowSecret
     */
    public function setWindowSecret($windowSecret)
    {
        $this->setConfigValue('payment/onpaypaymentmethod/window_secret', $windowSecret);
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getPaymentWindowLanguage()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/payment_window_language');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getInstructions()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/instructions');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getType()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/type');
    }

    /**
     * Function getPaymentWindowLanguage
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/method');
    }

    /**
     * Function getDeliveryDisabled
     *
     * @return string
     */
    public function getDeliveryDisabled()
    {
        if ($this->getMethod() == self::MOBILE_PAY_CHECKOUT) {
            return $this->getConfigValue('payment/onpaypaymentmethod/delivery_disabled');
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
        return $this->getConfigValue('payment/onpaypaymentmethod/secure');
    }

    /**
     * Function getDesign
     *
     * @return string
     */
    public function getDesign()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/design');
    }

    /**
     * Function getExpiration
     *
     * @return string
     */
    public function getExpiration()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/expiration');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return string
     */
    public function getOrderStatusAfterPayment()
    {
        return $this->getConfigValue('payment/onpaypaymentmethod/order_status');
    }

    /**
     * Function getOrderStatusAfterPayment
     *
     * @return boolean
     */
    public function isAutoCapture()
    {
        return (bool) $this->getConfigValue('payment/onpaypaymentmethod/auto_capture');
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
}
