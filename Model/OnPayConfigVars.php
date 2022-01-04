<?php

namespace OnPay\OnPay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * OnPayConfigVars class
 */
class OnPayConfigVars implements ConfigProviderInterface
{
    /**
     * helper variable
     *
     * @var \OnPay\OnPay\Helper\Config
     */
    public $helper;

    /**
     * storeManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * __construct function
     *
     * @param \OnPay\OnPay\Helper\Config $helper
     */
    public function __construct(
        \OnPay\OnPay\Helper\Config $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }


    /**
     * function function
     *
     * @return void
     */
    public function getConfig()
    {
        return [
            'payment'=>[
                'onpay'=> [
                    'instructions'=>$this->helper->getInstructions(),
                    'redirect_url'=>$this->getRedirectUrl()
                ]
            ]
        ];
    }

    private function getRedirectUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        return $baseUrl.'onpay/redirect/window';
    }
}
