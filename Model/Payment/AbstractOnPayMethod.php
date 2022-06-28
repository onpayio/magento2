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

namespace OnPay\Magento2\Model\Payment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use OnPay\API\Transaction\DetailedTransaction;
use OnPay\Magento2\Helper\Config;
use OnPay\Magento2\Helper\Currency;
use OnPay\Magento2\Model\OnPayTokenStorage;
use OnPay\OnPayAPI;

/**
 * OnPayPaymentMethod OnPay\Magento2\Model\Payment\OnPayPaymentMethod
 *
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @link      https://intelligodenmark.dk
 */
abstract class AbstractOnPayMethod extends AbstractMethod
{
    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var Config
     */
    protected Config $helper;

    /**
     * @var Currency
     */
    protected Currency $currencyHelper;

    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $transactionBuilder;

    /**
     * @var OnPayAPI
     */
    protected OnPayAPI $onPayApi;

    /**
     * Construct Function
     *
     * @param Context $context Concrete implementation for
     * @param Registry $registry Used to manage values in registry
     * @param ExtensionAttributesFactory $extensionFactory Factory class for instantiation of extension attributes objects.
     * @param AttributeValueFactory $customAttributeFactory class AttributeValueFactory
     * @param Data $paymentData Render payment information block
     * @param ScopeConfigInterface $scopeConfig ScopeConfig
     * @param Logger $logger Logger for payment related information (request, response, etc.) which is used for debug
     * @param AbstractResource|null $resource Abstract resource model
     * @param AbstractDb|null $resourceCollection Base items collection class
     * @param Config $helper Onpay Helper
     * @param BuilderInterface $transactionBuilder Order payment information
     * @param array $data Array
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory      $customAttributeFactory,
        Data                       $paymentData,
        ScopeConfigInterface       $scopeConfig,
        Logger                     $logger,
        AbstractResource           $resource = null,
        AbstractDb                 $resourceCollection = null,
        Config                     $helper,
        BuilderInterface           $transactionBuilder,
        array                      $data = []
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
        $this->currencyHelper = new Currency();
        $this->transactionBuilder = $transactionBuilder;

        $tokenStorage = new OnPayTokenStorage($helper);
        $this->onPayApi = new OnPayAPI($tokenStorage, [
            'client_id' => $helper->getWebsiteUrl(),
            'redirect_uri' => $helper->getAuthorizeUrl()
        ]);
    }

    /**
     * Check whether payment method can be used
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && $this->helper->isEnabled();
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->helper->isEnabled() && parent::isActive($storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getInstructions($storeId = null): string
    {
        return (string)$this->getConfigData('instructions', $storeId);
    }

    /**
     * Capture Payment
     *
     * @param InfoInterface $payment Payment interface @api
     * @param float                               $amount  Payment Amount
     *
     * @return void
     */
    public function capture(InfoInterface $payment, $amount)
    {
        $transactionNumber = $payment->getLastTransId();
        $order = $payment->getOrder();

        if ($transactionNumber) {
            $minorAmount = $this->currencyHelper->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');
            $captureTransaction = $this->onPayApi->transaction()->captureTransaction($transactionNumber, $minorAmount);

            try {
                $captureTransactionId = $captureTransaction->uuid;
                if ($captureTransactionId) {
                    $payment->setTransactionId($captureTransactionId)
                        ->setPreparedMessage(__('OnPay - Transaction has been successful.'))
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(1)
                    ;

                    $payment->setCcExpYear($captureTransaction->expiryYear);
                    $payment->setCcExpMonth($captureTransaction->expiryMonth);

                    $transaction = $this->transactionBuilder->setPayment($payment)
                        ->setOrder($order)
                        ->setTransactionId($captureTransactionId)
                        ->setFailSafe(false)
                        ->build(Transaction::TYPE_CAPTURE);

                    $transaction->save();
                }
            } catch (ValidatorException $ex) {
                throw new ValidatorException($ex->getMessage());
            }
        } else {
            throw new ValidatorException('Payment is not authorized.');
        }
        return $this;
    }

    /**
     * Refund Function
     *
     * @param InfoInterface $payment Payment Interface
     * @param [type]                               $amount  Amount
     *
     * @return void
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $transactionNumber = $payment->getLastTransId();
        $order = $payment->getOrder();

        $message = __("OnPay - The invoice can't be refund at this time. Please try again later or make an offline refund.");

        if ($transactionNumber) {
            $minorAmount = $this->currencyHelper->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');
            $refundTransaction = $this->onPayApi->transaction()->refundTransaction($transactionNumber, $minorAmount);

            $refundTransactionId = $refundTransaction->uuid;
            if ($refundTransactionId) {
                $payment->setTransactionId($refundTransactionId)
                    ->setShouldCloseParentTransaction(true)
                    ->setIsTransactionClosed(1);

                return;
            }
        }

        throw new ValidatorException($message);
    }

    /**
     * Cancel Function
     *
     * @param InfoInterface $payment Payment Interface
     *
     * @return void
     */
    public function cancel(InfoInterface $payment)
    {
        $message = __("OnPay - The invoice can't be cancelled at this time. Please try again later or make an offline cancel.");
        $this->cancelTransaction($payment, $message);
    }

    /**
     * Vpod Function
     *
     * @param InfoInterface $payment Payment Interface
     *
     * @return void
     */
    public function void(InfoInterface $payment)
    {
        $message = __("OnPay - The invoice can't be voided at this time. Please try again later or make an offline cancel.");
        $this->cancelTransaction($payment, $message);
    }

    private function cancelTransaction(InfoInterface $payment, string $message) {
        $transactionNumber = $payment->getLastTransId();
        if ($transactionNumber) {
            $refundTransaction = $this->onPayApi->transaction()->cancelTransaction($transactionNumber);

            $refundTransactionId = $refundTransaction->uuid;
            if ($refundTransactionId) {
                $payment->setTransactionId($refundTransactionId)
                    ->setShouldCloseParentTransaction(true)
                    ->setIsTransactionClosed(1);

                return;
            }
        }

        throw new ValidatorException($message);
    }
}
