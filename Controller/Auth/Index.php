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

namespace OnPay\Magento2\Controller\Auth;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use OnPay\Magento2\Model\ManageOnPay;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var TypeListInterface
     */
    protected TypeListInterface $cacheTypeList;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $_coreSession;

    /**
     * @var string|null
     */
    protected ?string $adminFrontName;

    /**
     * @var ManageOnPay
     */
    protected ManageOnPay $manageOnPay;

    /**
     * @param Context                 $context
     * @param PageFactory             $pageFactory
     * @param ManageOnPay             $manageOnPay
     * @param StoreManagerInterface   $storeManager
     * @param TypeListInterface       $cacheTypeList
     * @param SessionManagerInterface $coreSession
     * @param AreaList                $areaList
     */
    public function __construct(
        Context                 $context,
        PageFactory             $pageFactory,
        ManageOnPay             $manageOnPay,
        StoreManagerInterface   $storeManager,
        TypeListInterface       $cacheTypeList,
        SessionManagerInterface $coreSession,
        AreaList                $areaList
    ) {
        $this->_pageFactory = $pageFactory;
        $this->manageOnPay = $manageOnPay;
        $this->_storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->_coreSession = $coreSession;
        $this->adminFrontName = $areaList->getFrontName('adminhtml');
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $key = $this->getRequest()->getParam('key');
        $code = $this->getRequest()->getParam('code');
        if (isset($key)) {
            $this->_coreSession->setAuthKey($key);
        }
        $AuthKey = $this->_coreSession->getAuthKey();

        $baseurl = $this
            ->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        if (!isset($code)) {
            $redirect = $this
                ->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl($this->manageOnPay->getAuthorizeUrl());
            return $redirect;
        } else {
            $this->manageOnPay->finishAuthorization($code);

            $this
                ->cacheTypeList
                ->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

            $url = $baseurl . $this->adminFrontName . '/admin/system_config/edit/section/payment/key/' . $AuthKey . '/';

            $redirect = $this
                ->resultFactory
                ->create(ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl($url);
            return $redirect;
        }
    }
}
