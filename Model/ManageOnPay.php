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

namespace OnPay\OnPay\Model;

use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * ManageOnPay Magento\Sales\Model\Order\Payment\Transaction\ManageOnPay
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
class ManageOnPay
{
    /**
     * Helper variable
     *
     * @var \OnPay\OnPay\Helper\Config
     */
    public $helper;

    /**
     * OrderFactory variable
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $orderFactory;

    public $orderManagement;

    public $transactionBuilder;

    /**
     * __construct function
     *
     * @param \OnPay\OnPay\Helper\Config                                      $helper             Helper
     * @param \Magento\Sales\Model\OrderFactory                               $orderFactory       order Factory
     * @param \Magento\Sales\Api\OrderManagementInterface                     $orderManagement    order Management
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder transaction Builder
     */
    public function __construct(
        \OnPay\OnPay\Helper\Config $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
    ) {
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * Payment accept
     *
     * @param $post all post values
     *
     * @return array
     */
    public function accept($post)
    {
        $response = false;

        if (isset($post['onpay_reference'])) {

            $orderId = $post['onpay_reference'];

            $order = $this
                ->orderFactory
                ->create();
            $order->loadByIncrementId($orderId);

            $response = $this
                ->helper
                ->checkHashCode(
                    $order->getPayment()
                        ->getAdditionalInformation()
                );

            if ($response) {

                $this->updateTransactionInformation($post, $order);

                if (isset($post['onpay_uuid'])) {

                    $order
                        ->addStatusHistoryComment(
                            __(
                                "OnPay - Transaction Authorized. 
					Authorized Id: %1 -authorized",
                                $post['onpay_uuid']
                            )
                        );
                    $order->save();
                }
            }
        }

        return $response;
    }

    /**
     * Payment decline
     *
     * @param $post all post values
     *
     * @return array
     */
    public function decline($post)
    {
        $response = false;

        if (isset($post['onpay_reference'])) {

            $orderId = $post['onpay_reference'];

            $order = $this
                ->orderFactory
                ->create();
            $order->loadByIncrementId($orderId);

            $response = $this
                ->helper
                ->checkHashCode(
                    $order->getPayment()
                        ->getAdditionalInformation()
                );

            if ($response) {

                // Set Payment Additional Information
                $this
                    ->updatePaymentAdditionalInformation(
                        $post,
                        $order->getPayment()
                    );

                // Add Comment
                $order
                    ->addStatusHistoryComment(
                        __("OnPay - Payment declined")
                    );
                $order->save();

                $this
                    ->orderManagement
                    ->cancel($order->getId());
            }
        }

        return $response;
    }
    /**
     * Update Payment Additional Information
     *
     * @param $post    all post values
     * @param $payment payment object
     *
     * @return boolean
     */
    public function updatePaymentAdditionalInformation($post, $payment)
    {
        $additionInformation = $payment->getAdditionalInformation();
        $additionInformation = array_merge($additionInformation, $post);
        $payment->setAdditionalInformation($additionInformation);
        $payment->save();
    }
    /**
     * Update Transaction Information
     *
     * @param $post  all post values
     * @param $order order abject
     *
     * @return boolean
     */
    public function updateTransactionInformation($post, $order)
    {
        $payment = $order->getPayment();

        $payment->setLastTransId($post['onpay_uuid']);
        $payment->setTransactionId($post['onpay_uuid']);

        if ($post['onpay_3dsecure']) {
            $payment->setCcSecureVerify($post['onpay_3dsecure']);
        }

        switch ($post['onpay_method']) {
            case 'card':
                $payment->setCcType($post['onpay_cardtype']);
                $payment->setCcLast4(substr($post['onpay_cardmask'], -4));
                break;
        }

        $transaction = $this
            ->transactionBuilder
            ->setPayment($payment)->setOrder($order)
            ->setTransactionId($post['onpay_uuid'])
            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array)$post])
            ->setFailSafe(false)
            ->build(Transaction::TYPE_ORDER);

        $payment->save();
        $transaction->save();
    }
}
