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
 * Version: 1.0.0
 * Author URI: https://onpay.io
 */

namespace OnPay\Magento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use OnPay\Magento2\Model\ManageOnPay;

class Design implements ArrayInterface
{
    /**
     * @var ManageOnPay
     */
    protected $manageOnPay;

    /**
     * __construct function
     *
     * @param ManageOnPay $manageOnPay
     */
    public function __construct(
        ManageOnPay $manageOnPay
    ) {
        $this->manageOnPay = $manageOnPay;
    }

    /**
     * Config array
     *
     * @inheritDoc
     */
    public function toOptionArray()
    {
        try {
            $options = [];
            foreach ($this->manageOnPay->getPaymentWindowDesigns() as $design) {
                $options[] = [
                    'value' => $design->name,
                    'label' => $design->name
                ];
            }
            return $options;
        } catch (\Exception  $e) {
            return [];
        }
    }
}
