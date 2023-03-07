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

namespace OnPay\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use OnPay\Magento2\Helper\Config;

class OnPayConfigVars implements ConfigProviderInterface
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Helper variable
     *
     * @var Config
     */
    protected $helper;

    /**
     * StoreManager variable
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * __construct function
     *
     * @param Escaper               $escaper
     * @param Config                $helper       Config from helper
     * @param StoreManagerInterface $storeManager store Manager
     */
    public function __construct(
        Escaper $escaper,
        Config $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * Function Config
     *
     * @return void
     */
    public function getConfig()
    {
        $methods = [
            'select',
            'card',
            'paypal',
            'mobilepay',
            'viabill',
            'anyday',
            'swish',
            'vipps',
        ];

        $config = [];
        foreach ($methods as $method) {
            $methodCode = 'onpay_' . $method;
            $config['payment'][$methodCode]['instructions'] = $this->helper->getInstructions($methodCode);
            $config['payment'][$methodCode]['redirect_url'] = $this->_getRedirectUrl();
            $config['payment'][$methodCode]['logo'] = $this->getLogo($methodCode);
            $config['payment'][$methodCode]['logo_title'] = $this->helper->getLogoTitle($methodCode);
        }

        return $config;
    }

    /**
     * @param  $methodCode
     * @return false|string
     */
    private function getLogo($methodCode)
    {
        $logo = $this->helper->getLogo($methodCode);
        if (!empty($logo)) {
            return nl2br($this->escaper->escapeHtml($logo));
        }
        return false;
    }

    /**
     * Function
     *
     * @return string
     */
    private function _getRedirectUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        return $baseUrl . 'onpay/redirect/window';
    }
}
