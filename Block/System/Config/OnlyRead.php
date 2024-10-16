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

namespace OnPay\Magento2\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class OnlyRead extends Field
{
    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setReadonly(true);
        return $element->getElementHtml();
    }
}
