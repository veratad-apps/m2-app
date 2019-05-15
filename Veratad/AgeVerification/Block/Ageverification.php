<?php

        namespace Veratad\AgeVerification\Block;

        class Ageverification extends \Magento\Framework\View\Element\Template
        {
          private $scopeConfig;
          protected $messageManager;
          protected $_checkoutSession;
          protected $orderRepository;
          protected $_urlInterface;
          protected $_storeManager;
          protected $_order;
          protected $customerSession;

          public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Framework\UrlInterface $urlInterface,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Sales\Model\Order $order,
            \Magento\Customer\Model\Session $customerSession,
            array $data = []
            )
          {

            $this->scopeConfig = $scopeConfig;
            $this->messageManager = $messageManager;
            $this->_checkoutSession = $checkoutSession;
            $this->orderRepository = $orderRepository;
            $this->_order = $order;
            $this->_customerSession = $customerSession;
            parent::__construct($context, $data);
          }

          public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Age Verification'));
        return $this;
    }


          public function getFailTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_fail_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getFailSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_fail_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getSuccessTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_success_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getSuccessSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_success_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getSecondAttemptFailureTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_failure_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getSecondAttemptFailureSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_failure_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getNotEligibleTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_not_eligible_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getNotEligibleSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_not_eligible_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getAttemptsExcededTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_exceeded_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getAttemptsExcededSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_exceeded_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $failtext;
          }

          public function getOrderData($id)
          {
            $order = $this->orderRepository->get($id);
            return $order;

          }

          public function getVeratadOrderId()
          {
            $ordernumber = $this->_checkoutSession->getLastRealOrder();
            $orderId = $ordernumber->getEntityId();
            //$fn = $order->getBillingAddress()->getFirstname();
            //if(empty($orderId)){
            //  echo "There is no Order ID";
            //}else{
            //  return $fn;
          //  }

          return $orderId;

          }

          public function getStoreManagerDataBaseUrl()
       {

           // by default: URL_TYPE_LINK is returned
           return $this->_storeManager->getStore()->getBaseUrl();
       }



  }
