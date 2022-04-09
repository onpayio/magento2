<?php

/**
 * OnPay Magento2 module
 *
 * @category  Payment_Method
 * @package   Onpay_Magento
 * @author    Julian F. Christmas <jc@intelligodenmark.dk>
 * @copyright 2022 Team.blue Denmark A/S
 * @license   GPL-2.0+
 *
 * @magento-module
 * Plugin Name: OnPay Magento2
 * Plugin URI: https://onpay.io
 * Description: Collect payments using OnPay.io as PSP
 * Author: Julian F. Christmas
 * Version: 1.0.0
 * Author URI: https://intelligodenmark.dk
 */

namespace OnPay\Magento2\Controller\Auth;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public $_storeManager;

    protected $resourceConfig;

    protected $cacheTypeList;

    protected $_backendUrl;

    protected $_urlBuider;

    protected $_coreSession;

    public $adminFrontName;

    protected $notifierPool;

    protected $manageOnPay;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory, \Magento\Framework\Controller\ResultFactory $resultFactory, \OnPay\OnPay\Model\ManageOnPay $manageOnPay, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Config\Model\ResourceModel\Config $resourceConfig, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Magento\Backend\Model\UrlInterface $backendUrl, \Magento\Backend\Model\UrlInterface $urlBuilder, \Magento\Framework\Session\SessionManagerInterface $coreSession, \Magento\Framework\App\AreaList $areaList, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\Notification\NotifierInterface $notifierPool, \Magento\Framework\HTTP\Client\Curl $curl)
    {
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->resultFactory = $resultFactory;
        $this->_storeManager = $storeManager;
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->_backendUrl = $backendUrl;
        $this->_urlBuider = $urlBuilder;
        $this->_coreSession = $coreSession;
        $this->adminFrontName = $areaList->getFrontName('adminhtml');
        $this->messageManager = $messageManager;
        $this->notifierPool = $notifierPool;
        $this->_curl = $curl;
        parent::__construct($context);
    }

    public function execute()
    {

        $key = $this->getRequest()->getParam('key');
        $code = $this->getRequest()->getParam('code');
        if (isset($key)) {
            $this->_coreSession->setAuthKey($key);
        }
        $AuthKey = $this->_coreSession->getAuthKey();

        $onepayUri = 'https://manage.onpay.io/oauth2/authorize';

        $gatewayId = '';
        $windowSecret = '';
        $access_token = '';

        $baseurl = $this
            ->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $storeName = $baseurl;

        $client_id = '?client_id=' . $storeName;
        $redirect_uri = '&redirect_uri=' . $baseurl . 'onpay/auth/index?auth';
        $response_type = '&response_type=code';
        $code_challenge_method = '&code_challenge_method=S256';

        if (!isset($code)) {
            $AuthUrl = $onepayUri . $client_id . $redirect_uri . $response_type . $code_challenge_method;
            $redirect = $this
                ->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl($AuthUrl);
            return $redirect;
        } else {

            $redirect_uri_path = $baseurl . 'onpay/auth/index?auth';

            $postData = [
                'client_id' => $storeName,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirect_uri_path,
                'code_verifier' => ''
            ];
            $this->_curl->post('https://api.onpay.io/oauth2/access_token', $postData);

            $response = $this->_curl->getBody();

            $response = json_decode($response);
            if (!isset($response->error)) {
                $access_token = $response->access_token;
            }

            $headers = [
                "Authorization" => "Bearer {$access_token}",
                "Content-Type" => "application/json"
            ];

            $this->_curl->setHeaders($headers);
            $this->_curl->get('https://api.onpay.io/v1/gateway/information');

            $gateway_response_json = $this->_curl->getBody();
            $gateway_response = json_decode($gateway_response_json);
            if (isset($gateway_response->data)) {
                $gatewayId = $gateway_response
                    ->data->gateway_id;
            }

            $headers = [
                "Authorization" => "Bearer {$access_token}",
                "Content-Type" => "application/json"
            ];

            $this->_curl->setHeaders($headers);
            $this->_curl->get('https://api.onpay.io/v1/gateway/window/v3/integration');

            $window_secret_response = $this->_curl->getBody();

            $window_secret_response = json_decode($window_secret_response);

            if (isset($window_secret_response->data)) {
                $windowSecret = $window_secret_response
                    ->data->secret;
            }

            $this
                ->resourceConfig
                ->saveConfig('payment/onpaypaymentmethod/gateway_id', $gatewayId, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, 0);

            $this
                ->resourceConfig
                ->saveConfig('payment/onpaypaymentmethod/window_secret', $windowSecret, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, 0);

            $this
                ->resourceConfig
                ->saveConfig('payment/onpaypaymentmethod/api_key', $access_token, \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, 0);

            $this
                ->cacheTypeList
                ->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            $params = ['section' => 'payment'];
            $url = $baseurl . $this->adminFrontName . '/admin/system_config/edit/section/payment/key/' . $AuthKey . '/';

            $redirect = $this
                ->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl($url);
            return $redirect;
        }
    }
}
