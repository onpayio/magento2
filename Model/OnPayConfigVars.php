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

namespace OnPay\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use OnPay\Magento2\Helper\Config;

/**
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class OnPayConfigVars implements ConfigProviderInterface
{
    /**
     * Helper variable
     *
     * @var Config
     */
    public $helper;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * __construct function
     *
     * @param Config                 $helper       Config from helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager store Manager
     */
    public function __construct(
        Config $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
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
        return [
            'payment' => [
                'onpay' => [
                    'instructions' => $this->helper->getInstructions(),
                    'redirect_url' => $this->_getRedirectUrl()
                ]
            ]
        ];
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
