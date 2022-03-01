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

declare(strict_types=1);

namespace OnPay\OnPay\Model\Payment;

use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * OnPayPaymentMethod OnPay\OnPay\Model\Payment\OnPayPaymentMethod
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
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

    protected $helper;

    protected $transactionBuilder;

    protected $invoiceRepository;

    protected $_transaction;

    /**
     * Construct Function
     *
     * @param \Magento\Framework\Model\Context                                $context                Concrete implementation for
     * @param \Magento\Framework\Registry                                     $registry               Used to manage values in registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory               $extensionFactory       Factory class for instantiation of extension attributes objects.
     * @param \Magento\Framework\Api\AttributeValueFactory                    $customAttributeFactory class AttributeValueFactory
     * @param \Magento\Payment\Helper\Data                                    $paymentData            Render payment information block
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig            ScopeConfig
     * @param \Magento\Payment\Model\Method\Logger                            $logger                 Logger for payment related information (request, response, etc.) which is used for debug
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null    $resource               Abstract resource model
     * @param \Magento\Framework\Data\Collection\AbstractDb|null              $resourceCollection     Base items collection class
     * @param \OnPay\OnPay\Helper\Config                                      $helper                 Onpay Helper
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder     Order payment information
     * @param InvoiceRepositoryInterface                                      $invoiceRepository      Invoice repository interface.
     * @param \Magento\Framework\DB\Transaction                               $transaction            connection by name
     * @param array                                                           $data                   Array
     */
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
        \OnPay\OnPay\Helper\Config $helper,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\DB\Transaction $transaction,
        array $data = []
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

    /**
     * Capture Payment
     *
     * @param \Magento\Payment\Model\InfoInterface $payment Payment interface @api
     * @param [type]                               $amount  Payment Amount
     *
     * @return void
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $tranactionId = $payment->getLastTransId();

        $order = $payment->getOrder();

        if ($tranactionId) {

            $response = $this->helper->connectToOnPayTransaction('capture', $tranactionId, 'POST', $amount);

            try {
                if (isset($response['data'])) {

                    $data = $response['data'];

                    $captureTranactionId = '';
                    if (isset($data['history'])) {
                        $history = $data['history'];
                        foreach ($history as $his) {
                            if ($his['action'] == 'capture') {
                                $captureTranactionId = $his['uuid'];
                            }
                        }
                    }

                    if ($captureTranactionId) {

                        $payment->setTransactionId($captureTranactionId)
                            ->setPreparedMessage(__('OnPay - Transaction has been successful.'))
                            ->setShouldCloseParentTransaction(true)
                            ->setIsTransactionClosed(1)
                            ->setAdditionalInformation($data);

                        $this->_updatePaymentDetails($payment, $data);

                        $transaction = $this->transactionBuilder->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($captureTranactionId)
                            ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $payment->getAdditionalInformation()])
                            ->setFailSafe(false)
                            ->build(Transaction::TYPE_CAPTURE);

                        $transaction->save();
                    }
                }
            } catch (\ValidatorException $ex) {
                throw new \ValidatorException($ex->getMessage());
            }
        } else {
            throw new \ValidatorException('Payment is not authorized.');
        }
        return $this;
    }

    /**
     * Update Payment Details
     *
     * @param [type] $payment Payment
     * @param [type] $data    Payment Data
     *
     * @return void
     */
    private function _updatePaymentDetails($payment, $data)
    {
        if (!empty($data['card_type'])) {
            $payment->setCcExpYear($data['expiry_year']);
            $payment->setCcExpMonth($data['expiry_month']);
        }

        $payment->save();
    }

    /**
     * Refund Function
     *
     * @param \Magento\Payment\Model\InfoInterface $payment Payment Interface
     * @param [type]                               $amount  Amount
     *
     * @return void
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $tranactionId = $this->_getTransactionNumber($payment->getAdditionalInformation());

        $message = "OnPay - The invoice can't be refund at this time. Please try again later or make an offline refund.";

        if ($tranactionId) {

            $response = $this->helper->connectToOnPayTransaction('refund', $tranactionId, 'POST', $amount);

            if (isset($response['data'])) {

                $data = $response['data'];

                $refundTranactionId = '';
                if (isset($data['history'])) {
                    $history = $data['history'];
                    foreach ($history as $his) {
                        if ($his['action'] == 'refund') {
                            $refundTranactionId = $his['uuid'];
                        }
                    }
                }

                if ($refundTranactionId) {
                    $payment->setTransactionId($refundTranactionId)
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(1)
                        ->setAdditionalInformation($data);
                }

                return $this;
            } elseif (isset($response['errors']) && count($response['errors']) && isset($response['errors'][0]['message'])) {
                $message = $response['errors'][0]['message'];
            }
        }

        throw new \ValidatorException($message);
    }

    /**
     * Get Transaction Number
     *
     * @param [type] $data Data
     *
     * @return void
     */
    private function _getTransactionNumber($data)
    {
        $transactionNumber = '';

        if (count($data) && isset($data['transaction_number'])) {
            $transactionNumber = $data['transaction_number'];
        }

        return $transactionNumber;
    }
}
