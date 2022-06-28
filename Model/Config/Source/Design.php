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

use Magento\Framework\Option\ArrayInterface;
use OnPay\Magento2\Model\ManageOnPay;

/**
 * Design OnPay\Magento2\Model\Config\Source\Design
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */

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
