<?php

    namespace Veratad\AgeVerification\Block;

    use Magento\Framework\View\Element\Template;

    class Ageverification extends Template
    {

          protected $request;
          private $scopeConfig;
          protected $orderRepository;
          protected $_storeManager;
          protected $_escaper;

          public function __construct(
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\Escaper $_escaper,
            array $data = []
            )
          {
            parent::__construct($context, $data);
            $this->request = $request;
            $this->scopeConfig = $scopeConfig;
            $this->_storeManager = $storeManager;
            $this->orderRepository = $orderRepository;
            $this->_escaper = $_escaper;
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
          public function getOrderData()
          {
            $id = $this->request->getParam('id');
            $id = str_replace("/", "", $id);
            $order = $this->orderRepository->get($id);
            $order->getBillingAddress()->getFirstname();
            $order->getBillingAddress()->getLastname();
            $order->getBillingAddress()->getData("street");

            $data = array(
              'fn' => $order->getBillingAddress()->getFirstname(),
              'ln' => $order->getBillingAddress()->getLastname(),
              'addr' => $order->getBillingAddress()->getData("street"),
              'zip' => $order->getBillingAddress()->getData("postcode"),
              'id' => $id,
              'customer_id' => $order->getCustomerId()
            );

            return $data;
          }


          public function getStoreManagerDataBaseUrl()
       {
           // by default: URL_TYPE_LINK is returned
           return $this->_storeManager->getStore()->getBaseUrl();
       }
}
