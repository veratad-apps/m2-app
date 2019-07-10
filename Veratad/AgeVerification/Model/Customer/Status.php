<?php

    namespace Veratad\AgeVerification\Model\Customer;

    class Status
    {

        private $scopeConfig;
        protected $helper;
        protected $_customerRepositoryInterface;

        public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Veratad\AgeVerification\Helper\Data $helper,
              \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
        ) {
            $this->scopeConfig = $scopeConfig;
            $this->helper = $helper;
            $this->_customerRepositoryInterface = $customerRepositoryInterface;
        }

        public function isExcluded($customer_id, $billing, $shipping)
        {

          if ($customer_id) {

              $av = $this->helper->getVeratadAccountActionById($customer_id);
              $accountNameMatch = $this->helper->nameDetectionAccount($customer_id, $billing, $shipping);
              $customer = $this->_customerRepositoryInterface->getById($customer_id);
              $customerGroupID = $customer->getGroupId();
              $groups_excluded = $this->scopeConfig->getValue('settings/customer_groups_veratad/customer_group_list_veratad', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
              $result = ((strpos($groups_excluded, $customerGroupID) !== false) || ($av === "PASS" && $accountNameMatch === true));
        }else{
              $result = false;
            }
              return $result;
        }
    }
