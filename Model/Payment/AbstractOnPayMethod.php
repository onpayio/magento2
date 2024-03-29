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

declare(strict_types=1);

namespace OnPay\Magento2\Model\Payment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use OnPay\API\Transaction\DetailedTransaction;
use OnPay\Magento2\Helper\Config;
use OnPay\Magento2\Helper\Currency;
use OnPay\Magento2\Model\OnPayTokenStorage;
use OnPay\OnPayAPI;

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
    protected $helper;

    /**
     * @var Currency
     */
    protected $currencyHelper;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var OnPayAPI
     */
    protected $onPayApi;

    /**
     * Construct Function
     *
     * @param Context                    $context                Concrete implementation for
     * @param Registry                   $registry               Used to manage values in registry
     * @param ExtensionAttributesFactory $extensionFactory       Factory class for instantiation of extension attributes objects.
     * @param AttributeValueFactory      $customAttributeFactory class AttributeValueFactory
     * @param Data                       $paymentData            Render payment information block
     * @param ScopeConfigInterface       $scopeConfig            ScopeConfig
     * @param Logger                     $logger                 Logger for payment related information (request, response, etc.) which is used for debug
     * @param Config                     $helper                 Onpay Helper
     * @param BuilderInterface           $transactionBuilder     Order payment information
     * @param AbstractResource|null      $resource               Abstract resource model
     * @param AbstractDb|null            $resourceCollection     Base items collection class
     * @param array                      $data                   Array
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory      $customAttributeFactory,
        Data                       $paymentData,
        ScopeConfigInterface       $scopeConfig,
        Logger                     $logger,
        Config                     $helper,
        BuilderInterface           $transactionBuilder,
        AbstractResource           $resource = null,
        AbstractDb                 $resourceCollection = null,
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
        $this->onPayApi = new OnPayAPI(
            $tokenStorage, [
            'client_id' => $helper->getClientId(),
            'redirect_uri' => $helper->getAuthorizeUrl()
            ]
        );
    }

    /**
     * Check whether payment method can be used
     *
     * @param  CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     */
    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote) && $this->helper->isEnabled();
    }

    /**
     * @param  int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->helper->isEnabled() && parent::isActive($storeId);
    }

    /**
     * @param  int|null $storeId
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
     * @param float         $amount  Payment Amount
     *
     * @return void
     */
    public function capture($payment, $amount)
    {
        $order = $payment->getOrder();

        $onpayUuid = $this->getOnpayUuid($payment);
        if (null !== $onpayUuid) {
            $minorAmount = $this->currencyHelper->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');
            $captureTransaction = $this->onPayApi->transaction()->captureTransaction($onpayUuid, $minorAmount);
            try {
                $captureTransactionId = $captureTransaction->uuid;
                if ($captureTransactionId) {
                    $payment->setTransactionId($captureTransactionId)
                        ->setPreparedMessage(__('OnPay - Transaction has been successful.'))
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(1);

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
     * @param [type]        $amount  Amount
     *
     * @return void
     */
    public function refund($payment, $amount)
    {
        $order = $payment->getOrder();

        $message = __("OnPay - The invoice can't be refund at this time. Please try again later or make an offline refund.");

        $onpayUuid = $this->getOnpayUuid($payment);
        if (null !== $onpayUuid) {
            $minorAmount = $this->currencyHelper->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');
            $refundTransaction = $this->onPayApi->transaction()->refundTransaction($onpayUuid, $minorAmount);

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
    public function cancel($payment)
    {
        $message = __("OnPay - The invoice can't be cancelled at this time. Please try again later or make an offline cancel.")->getText();
        $this->cancelTransaction($payment, $message);
    }

    /**
     * Vpod Function
     *
     * @param InfoInterface $payment Payment Interface
     *
     * @return void
     */
    public function void($payment)
    {
        $message = __("OnPay - The invoice can't be voided at this time. Please try again later or make an offline cancel.")->getText();
        $this->cancelTransaction($payment, $message);
    }

    /**
     * @param  $payment
     * @param  string $message
     * @return void
     */
    private function cancelTransaction($payment, string $message)
    {
        $onpayUuid = $this->getOnpayUuid($payment);
        if (null !== $onpayUuid) {
            $refundTransaction = $this->onPayApi->transaction()->cancelTransaction($onpayUuid);

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
     * Get OnPay uuid for payment
     *
     * @param InfoInterface $payment Payment interface @api
     *
     * @return string|null
     */
    private function getOnpayUuid($payment) {
        $info = $payment->getAdditionalInformation();
        if (array_key_exists('OnpayUUID', $info)) {
            return $info['OnpayUUID'];
        }
        return null;
    }
}
