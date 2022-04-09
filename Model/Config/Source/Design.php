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

namespace OnPay\Magento2\Model\Config\Source;

/**
 * Design OnPay\Magento2\Model\Config\Source\Design
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */

class Design implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Protected variable
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig Scope Config
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_curl = $curl;
    }

    /**
     * Config array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $apiKey =  $this->scopeConfig->getValue('payment/onpaypaymentmethod/api_key', $storeScope);

        try {
            $url = "https://api.onpay.io/v1/gateway/window/v3/design/";

            $headers = [
                "Authorization" => "Bearer {$apiKey}"
            ];

            $this->_curl->setHeaders($headers);
            $this->_curl->get($url);

            $response = $this->_curl->getBody();
            $response = json_decode($response, true);

            foreach ($response['data'] as $name) {
                $options[] = [
                    'value' => $name['name'],
                    'label' => $name['name']
                ];
            }
            return $options;
        } catch (\Exception  $e) {
            return [];
        }
    }
}
