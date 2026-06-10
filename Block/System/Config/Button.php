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
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use OnPay\Magento2\Helper\Config;

class Button extends Field
{
    protected $_template = 'OnPay_Magento2::system/config/button.phtml';

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->isGatewayLinked()) {
            return '';
        }
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Returns true when a Gateway ID and an OAuth2 API token are both stored,
     * meaning the merchant has already completed the 1-Click OnPay setup.
     *
     * @return bool
     */
    private function isGatewayLinked()
    {
        return $this->config->getGatewayId() !== ''
            && (string) $this->config->getOauth2Token() !== '';
    }

    /**
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->getUrl('/onpay/auth/index');
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(['id' => 'btn_id', 'label' => __('1-Click OnPay Setup')]);
        return $button->toHtml();
    }
}
