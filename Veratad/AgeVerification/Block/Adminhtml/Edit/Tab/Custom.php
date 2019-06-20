<?php
        namespace Veratad\AgeVerification\Block\Adminhtml\Edit\Tab;

        use Magento\Customer\Controller\RegistryConstants;
        use Magento\Ui\Component\Layout\Tabs\TabInterface;

        class Custom extends \Magento\Framework\View\Element\Template implements TabInterface
        {

          protected $authSession;
          protected $_veratadHistory;
          protected $customerRepository;

          public function __construct(
           \Magento\Backend\Block\Template\Context $context,
           \Magento\Framework\Registry $registry,
           \Magento\Backend\Model\Auth\Session $authSession,
           \Veratad\AgeVerification\Model\HistoryFactory $history,
           \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
           array $data = []
         ) {
           $this->_coreRegistry = $registry;
           $this->authSession = $authSession;
           $this->_veratadHistory = $history;
           $this->customerRepository = $customerRepository;
           parent::__construct($context, $data);
         }

      public function getTabLabel()
       {
           return __('Veratad Age Verification');
       }

       public function getTabTitle()
       {
           return __('Veratad Age Verification');
       }

       public function canShowTab()
       {
           if ($this->getCustomerId()) {
               return true;
           }
           return false;
       }

       public function isHidden()
       {
           if ($this->getCustomerId()) {
               return false;
           }
           return true;
       }

       public function getTabClass()
       {
           return '';
       }

       public function getTabUrl()
       {
       //replace the tab with the url you want
           return $this->getUrl('veratadcustomer/*/custom', ['_current' => true]);
       }

       public function isAjaxLoaded()
       {
           return true;
       }

        public function getCustomerId()
        {
          return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        }

        public function getVeratadAccountAction($customerid)
        {
          $history = $this->_veratadHistory->create();
          $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
          $last = end($collection);
          $action = $last['veratad_action'];
          return $action;
        }

        public function getVeratadAccountHistoryHelper($customerid)
        {
          $history = $this->_veratadHistory->create();
          $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
          return $collection;
        }

        public function getCurrentUserVeratad()
        {
          $user = $this->authSession->getUser();
          $username = $user->getUsername();
          return $username;
        }

    }
