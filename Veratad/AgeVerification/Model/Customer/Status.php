<?php

namespace Veratad\AgeVerification\Model\Customer;

  use Magento\Framework\ObjectManager\ObjectManager;


class Status
{

    private $scopeConfig;
    protected $_objectManager;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Veratad\AgeVerification\Helper\Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->helper = $helper;
    }

    public function isExcluded($customer_id, $billing, $shipping)
    {

      if ($customer_id) {

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
          $av = $customerObj->getVeratadAction();

          $accountNameMatch = $this->helper->nameDetectionAccount($customer_id, $billing, $shipping);

          $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/excluded.log');
          $logger = new \Zend\Log\Logger();
          $logger->addWriter($writer);
          $logger->info("account name match = " . $accountNameMatch);

          $customerGroupID = $customerObj->getGroupId();
          $groups_excluded = $this->scopeConfig->getValue('settings/customer_groups/customer_groups', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

          $result = ((strpos($groups_excluded, $customerGroupID) !== false) || ($av === "PASS" && $accountNameMatch === true));

    }else{
          $result = false;
        }
          return $result;
    }
}
