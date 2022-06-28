<?php

declare(strict_types=1);

namespace OnPay\Magento2\Model;

use OnPay\TokenStorageInterface;
use OnPay\Magento2\Helper\Config;

class OnPayTokenStorage implements TokenStorageInterface
{
    /**
     * @var Config
     */
    protected Config $config;

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
    public function getToken()
    {
        return (string)$this->config->getRefreshToken();
    }

    /**
     * @param  $token
     * @return void
     */
    public function saveToken($token)
    {
        $this->config->setRefreshToken($token);
    }
}
