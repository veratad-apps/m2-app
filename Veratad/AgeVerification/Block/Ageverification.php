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
          protected $_checkoutSession;
          protected $_order;
          protected $messageManager;
          protected $_veratadHistory;

          public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Escaper $_escaper,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Sales\Model\Order $order,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            array $data = []
            )
          {
            parent::__construct($context, $data);
            $this->request = $request;
            $this->scopeConfig = $scopeConfig;
            $this->_storeManager = $storeManager;
            $this->orderRepository = $orderRepository;
            $this->_escaper = $_escaper;
            $this->_checkoutSession = $checkoutSession;
            $this->_order = $order;
            $this->_veratadHistory = $history;
            $this->messageManager = $messageManager;
          }


        public function _prepareLayout()
        {
           parent::_prepareLayout();
           $this->pageConfig->getTitle()->set(__('Age Verification'));
           return $this;
         }


         public function getFailSubTitle()
          {
            return $this->scopeConfig->getValue('settings/content/agematch_fail_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
          }

          public function getSuccessSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_success_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->messageManager->addSuccess(__($failtext));
          }

          public function getSecondAttemptFailureSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_failure_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->messageManager->addError(__($failtext));
          }

          public function getNotEligibleSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_not_eligible_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->messageManager->addError(__($failtext));
          }

          public function getAttemptsExcededSubTitle()
          {
            $failtext = $this->scopeConfig->getValue('settings/content/agematch_exceeded_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->messageManager->addError(__($failtext));
          }
          public function getOrderData()
          {
            $order_session = $this->_order->load($this->_checkoutSession->getLastOrderId());
            $id = $order_session->getId(); //order ID
            $order = $this->orderRepository->get($id);

            $data = array(
              'fn' => $order->getBillingAddress()->getFirstname(),
              'ln' => $order->getBillingAddress()->getLastname(),
              'addr' => $order->getBillingAddress()->getData("street"),
              'state' => $order->getBillingAddress()->getData("region"),
              'zip' => $order->getBillingAddress()->getData("postcode"),
              'id' => $id,
              'customer_id' => $order->getCustomerId()
            );

            return $data;
          }

          public function getDcamsSiteName()
          {
           return $this->scopeConfig->getValue('settings/dcams/dcams_site_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         }

         public function getDcamsRules()
         {
          return $this->scopeConfig->getValue('settings/dcams/dcams_rules', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getDobVisible()
        {
         return $this->scopeConfig->getValue('settings/agematch/dobvisible_additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getDobRequired()
        {
         return $this->scopeConfig->getValue('settings/agematch/dobrequired_additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getSsnVisible()
        {
         return $this->scopeConfig->getValue('settings/agematch/ssnvisible_additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getSsnRequired()
        {
         return $this->scopeConfig->getValue('settings/agematch/ssnrequired_additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getDcamsSuccessMessage()
        {
         return $this->scopeConfig->getValue('settings/content/dcams_success', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getDcamsFailureMessage()
        {
         return $this->scopeConfig->getValue('settings/content/dcams_failure', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        public function getDcamsId()
        {
            $order_session = $this->_order->load($this->_checkoutSession->getLastOrderId());
            $id = $order_session->getId(); //order ID
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $id))->getData();
            $last = end($collection);
            $dcams_id = $last['veratad_dcams_id'];
            return $dcams_id;
        }

        public function getAgeState($state)
        {
          $global_age_to_check = $this->scopeConfig->getValue('age/general_age/global_age', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

          if(!$global_age_to_check){
            $age = "21+";
          }

          $state_to_check = strtolower($state);
          $state_age_requirement = $this->scopeConfig->getValue('age/general_age/'.$state_to_check, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
          if($state_age_requirement){
            $age = $state_age_requirement;
          }else{
            $age = $age;
          }

          return $age;
        }

          public function getStoreManagerDataBaseUrl()
       {
           // by default: URL_TYPE_LINK is returned
           return $this->_storeManager->getStore()->getBaseUrl();
       }
}
