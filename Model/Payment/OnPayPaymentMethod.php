<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace OnPay\OnPay\Model\Payment;

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class OnPayPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'onpaypaymentmethod';

    protected $_code = self::CODE;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    protected $_isInitializeNeeded = true;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \OnPay\OnPay\Helper\Config $helper,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->helper = $helper;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceRepository = $invoiceRepository;
        $this->_transaction = $transaction;
    }



    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $tranactionId = $payment->getLastTransId();

        $order = $payment->getOrder();

        if($tranactionId) {
            
            $response = $this->helper->connectToOnPayTransaction('capture', $tranactionId, 'POST', $amount);

            try {
                if(isset($response['data'])){
    
                    $data = $response['data'];

                    $captureTranactionId = '';
                    if(isset($data['history'])){
                        $history = $data['history'];
                        foreach($history as $his){
                            if($his['action'] == 'capture'){
                                $captureTranactionId = $his['uuid'];
                            }
                        }

                    }

                    if($captureTranactionId){

                        $payment->setTransactionId($captureTranactionId)
                            ->setPreparedMessage(__('OnPay - Transaction has been successful.'))
                            ->setShouldCloseParentTransaction(true)
                            ->setIsTransactionClosed(1)
                            ->setAdditionalInformation($data);
                        
                        $this->updatePaymentDetails($payment, $data);

                        $transaction = $this->transactionBuilder->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($captureTranactionId)
                            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $payment->getAdditionalInformation()])
                            ->setFailSafe(false)
                            ->build(Transaction::TYPE_CAPTURE);

                        $transaction->save();

                        
                    }
    
                }
    
            } catch (\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }

        }else{
            throw new \Exception('Payment is not authorized.');
        }
        return $this;

    }

    private function updatePaymentDetails($payment, $data)
    {
        if(!empty($data['card_type']))
        {
            $payment->setCcExpYear($data['expiry_year']);
            $payment->setCcExpMonth($data['expiry_month']);
        }

        $payment->save();

        return;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $tranactionId=$this->getTransactionNumber($payment->getAdditionalInformation());

        $message = "OnPay - The invoice can't be refund at this time. Please try again later or make an offline refund.";

        if($tranactionId) {
            
            $response = $this->helper->connectToOnPayTransaction('refund', $tranactionId, 'POST', $amount);

            if(isset($response['data'])){

                $data = $response['data'];

                $refundTranactionId = '';
                if(isset($data['history'])){
                    $history = $data['history'];
                    foreach($history as $his){
                        if($his['action'] == 'refund'){
                            $refundTranactionId = $his['uuid'];
                        }
                    }

                }

                if($refundTranactionId) {
                    $payment->setTransactionId($refundTranactionId)
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(1)
                        ->setAdditionalInformation($data);
                }
                

                return $this;
            }else if(isset($response['errors']) && count($response['errors']) && isset($response['errors'][0]['message'])){
                $message = $response['errors'][0]['message'];
            }
        }

        throw new \Exception($message);
    }

    private function getTransactionNumber($data)
    {
        $transactionNumber= '';

        if(count($data) && isset($data['transaction_number'])) {
            $transactionNumber = $data['transaction_number'];
        }

        return $transactionNumber;
    }
}

