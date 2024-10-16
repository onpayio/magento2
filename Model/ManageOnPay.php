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

namespace OnPay\Magento2\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use OnPay\API\PaymentWindow;
use OnPay\Magento2\Helper\Config;
use OnPay\OnPayAPI;

class ManageOnPay
{
    /** 
     * @var Config $helper
     */
    protected $helper;

    /** 
     * @var OnPayAPI $onPayApi
     */
    protected $onPayApi;

    /** 
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /** 
     * @var OrderManagementInterface $orderManagement
     */
    protected $orderManagement;

    /** 
     * @var BuilderInterface $transactionBuilder
     */
    protected $transactionBuilder;

    /** 
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * __construct function
     *
     * @param Config                   $helper             Helper
     * @param OrderFactory             $orderFactory       order Factory
     * @param OrderManagementInterface $orderManagement    order Management
     * @param BuilderInterface         $transactionBuilder transaction Builder
     */
    public function __construct(
        Config              $helper,
        OrderFactory             $orderFactory,
        OrderManagementInterface $orderManagement,
        BuilderInterface         $transactionBuilder,
        Session                  $checkoutSession
    ) {
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->transactionBuilder = $transactionBuilder;
        $this->checkoutSession = $checkoutSession;

        $tokenStorage = new OnPayTokenStorage($helper);
        $this->onPayApi = new OnPayAPI(
            $tokenStorage, [
            'client_id' => $helper->getClientId(),
            'redirect_uri' => $helper->getAuthorizeUrl(),
            'platform' => 'Magento 2/' . Config::PLUGIN_VERSION . '/' . $this->helper->getMagentoVersion(),
            ]
        );
    }

    /**
     * @return array
     */
    public function getPaymentWindowDesigns()
    {
        return $this->onPayApi->gateway()->getPaymentWindowDesigns()->paymentWindowDesigns;
    }

    /**
     * Return URL to redirect to OAuth2 authorization flow
     *
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return $this->onPayApi->authorize();
    }

    /**
     * Handle OAuth2 result
     *
     * @param  string $code
     * @return void
     */
    public function finishAuthorization($code)
    {
        $this->onPayApi->finishAuthorize($code);

        $gatewayId = $this->onPayApi->gateway()->getInformation()->gatewayId;
        $this->helper->setGatewayId($gatewayId);

        $window_secret = $this->onPayApi->gateway()->getPaymentWindowIntegrationSettings()->secret;
        $this->helper->setWindowSecret($window_secret);
    }

    /**
     * Validate and store Payment accept
     *
     * @param array $post all post values
     * @param bool $saveTransaction If true, the Transaction Info will be stored
     *
     * @return bool
     */
    public function accept($post, $saveTransaction = true)
    {
        $response = false;

        $paymentWindow = new PaymentWindow();
        $paymentWindow->setGatewayId($this->helper->getGatewayId());
        $paymentWindow->setSecret($this->helper->getWindowSecret());

        if ($paymentWindow->validatePayment($post) 
            && intval($post['onpay_errorcode']) === 0
        ) {
            $response = true;
            $orderId = $post['onpay_reference'];

            $order = $this
                ->orderFactory
                ->create();
            $order->loadByIncrementId($orderId);

            $this->updateTransactionInformation($post, $order);
        }

        return $response;
    }

    /**
     * Store Payment decline
     *
     * @param array $post all post values
     *
     * @return bool
     */
    public function decline($post)
    {
        $response = false;

        if (intval($post['onpay_errorcode']) !== 0
        ) {
            $response = true;
            $orderId = $post['onpay_reference'];

            $order = $this
                ->orderFactory
                ->create();
            $order->loadByIncrementId($orderId);

            // Set Payment Additional Information
            $this
                ->updatePaymentAdditionalInformation(
                    $post,
                    $order->getPayment()
                );

            $this->checkoutSession->restoreQuote();

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

        return $response;
    }

    /**
     * Update Payment Additional Information
     *
     * @param array                 $post    all post values
     * @param OrderPaymentInterface $payment payment object
     *
     * @return void
     */
    public function updatePaymentAdditionalInformation($post, OrderPaymentInterface $payment)
    {
        $additionInformation = $payment->getAdditionalInformation();
        $additionInformation = array_merge($additionInformation, $post);
        $payment->setAdditionalInformation($additionInformation);
        $payment->save();
    }

    /**
     * Update Transaction Information
     *
     * @param array          $post  all post values
     * @param OrderInterface $order order abject
     *
     * @return void
     */
    private function updateTransactionInformation($post, OrderInterface $order)
    {
        $payment = $order->getPayment();

        $payment->setLastTransId($post['onpay_uuid']);
        $payment->setTransactionId($post['onpay_uuid']);
        $payment->setIsTransactionClosed(false);

        $transactionComment = __("OnPay - Transaction Authorized.");

        if (array_key_exists('onpay_uuid', $post)) {
            $transactionComment = __(
                "OnPay - Transaction Authorized.
					Authorized Id: %1 -authorized",
                $post['onpay_uuid']
            );

            $payment->setAdditionalInformation("OnpayUUID", $post['onpay_uuid']);
        }

        if (array_key_exists('onpay_3dsecure', $post)) {
            $payment->setCcSecureVerify($post['onpay_3dsecure']);
        }

        if (array_key_exists('onpay_method', $post) 
            && $post['onpay_method'] === 'card' 
            && array_key_exists('onpay_cardtype', $post)
        ) {
            $payment->setCcType($post['onpay_cardtype']);
            if (array_key_exists('onpay_cardmask', $post)) {
                $payment->setCcLast4(substr($post['onpay_cardmask'], -4));
                $payment->setCcNumberEnc($post['onpay_cardmask']);
            }
            if (array_key_exists('onpay_expiry_month', $post)) {
                $payment->setCcExpMonth($post['onpay_expiry_month']);
            }
            if (array_key_exists('onpay_expiry_year', $post)) {
                $payment->setCcExpYear($post['onpay_expiry_year']);
            }
        }

        $transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
        $payment->addTransactionCommentsToOrder($transaction, $transactionComment);

        $payment->setAdditionalInformation(Transaction::RAW_DETAILS, (array)$post);

        try {
            $payment->save();
            $order->save();
        } catch (AlreadyExistsException $e) {
            // We catch already exists exception here, if encountered we're not gonna do anything.
        }
    }
}
