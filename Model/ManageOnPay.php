<?php

namespace OnPay\OnPay\Model;

use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * ManageOnPay class
 */
class ManageOnPay
{
    /**
     * helper variable
     *
     * @var \OnPay\OnPay\Helper\Config
     */
    public $helper;

    /**
     * orderFactory variable
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    public $orderFactory;

    /**
     * __construct function
     *
     * @param \OnPay\OnPay\Helper\Config $helper
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

    public function accept($post)
    {
        $response = false;

        if(isset($post['onpay_reference'])) {

            $orderId= $post['onpay_reference'];

            $order = $this->orderFactory->create();
            $order->loadByIncrementId($orderId);

            $response = $this->helper->checkHashCode($order->getPayment()->getAdditionalInformation());

            if($response) {

                $this->updateTransactionInformation($post, $order);

                if(isset($post['onpay_uuid'])){

                    $order->addStatusHistoryComment(__("OnPay - Transaction Authorized. Authorized Id: %1 -authorized", $post['onpay_uuid']));
                    $order->save();
                }
            }
        }

        return $response;
    }

    public function decline($post)
    {
        $response = false;

        if(isset($post['onpay_reference'])) {

            $orderId= $post['onpay_reference'];

            $order = $this->orderFactory->create();
            $order->loadByIncrementId($orderId);

            $response = $this->helper->checkHashCode($order->getPayment()->getAdditionalInformation());

            if($response) {

                // Set Payment Additional Information
                $this->updatePaymentAdditionalInformation($post, $order->getPayment());

                // Add Comment
                $order->addStatusHistoryComment(__("OnPay - Payment declined"));
                $order->save();

                $this->orderManagement->cancel($order->getId());
            }
        }

        return $response;
    }

    public function updatePaymentAdditionalInformation($post, $payment)
    {
        $additionInformation = $payment->getAdditionalInformation();
        $additionInformation= array_merge($additionInformation, $post);
        $payment->setAdditionalInformation($additionInformation);
        $payment->save();
        return ;
    }

    public function updateTransactionInformation($post, $order)
    {
        $payment = $order->getPayment();

        $payment->setLastTransId($post['onpay_uuid']);
        $payment->setTransactionId($post['onpay_uuid']);
        
        if($post['onpay_3dsecure']) {
            $payment->setCcSecureVerify($post['onpay_3dsecure']);
        }
        

        switch($post['onpay_method']) {
            case 'card':
                $payment->setCcType($post['onpay_cardtype']);
                $payment->setCcLast4(substr($post['onpay_cardmask'], -4));
                break;
        }

        $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($post['onpay_uuid'])
            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $post])
            ->setFailSafe(false)
            ->build(Transaction::TYPE_ORDER);

        $payment->save();
        $transaction->save();

        return ;
    }
}
