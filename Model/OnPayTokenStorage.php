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

namespace OnPay\Magento2\Model;

use OnPay\TokenStorageInterface;
use OnPay\Magento2\Helper\Config;

class OnPayTokenStorage implements TokenStorageInterface
{
    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string|null
     */
    public function getToken() {
        return (string)$this->config->getOauth2Token();
    }

    /**
     * @param  $token
     * @return void
     */
    public function saveToken($token) {
        $this->config->setOauth2Token($token);
    }
}
