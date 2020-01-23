<?php

        namespace Veratad\AgeVerification\Controller\Agematch;

        use Magento\Framework\App\Action\Context;
        use Magento\Framework\View\Result\PageFactory;
        use Magento\Framework\Controller\Result\JsonFactory;
        use Magento\Customer\Model\Customer;

        class Query extends \Magento\Framework\App\Action\Action
        {

            protected $helper;
            protected $_veratadHistory;
            protected $orderRepository;
            protected $jsonHelper;
            private $scopeConfig;
            protected $messageManager;
            protected $_customerRepoInterface;


            public function __construct(
                Context $context,
                PageFactory $resultPageFactory,
                JsonFactory $resultJsonFactory,
                \Veratad\AgeVerification\Helper\Data $helper,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                  \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
                  \Magento\Framework\Json\Helper\Data $jsonHelper,
                  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                  \Magento\Framework\Message\ManagerInterface $messageManager,
                  \Magento\Customer\Api\CustomerRepositoryInterface $customerRepoInterface
                )
            {

                $this->resultPageFactory = $resultPageFactory;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->helper = $helper;
                $this->_veratadHistory = $history;
                $this->orderRepository = $orderRepository;
                $this->jsonHelper = $jsonHelper;
                $this->scopeConfig = $scopeConfig;
                $this->messageManager = $messageManager;
                $this->_customerRepoInterface = $customerRepoInterface;
                return parent::__construct($context);
            }


            public function execute()
            {

              $fn = $this->getRequest()->getParam('fn');
              $ln = $this->getRequest()->getParam('ln');
              $addr = $this->getRequest()->getParam('addr');
              $city = $this->getRequest()->getParam('city');
              $region = $this->getRequest()->getParam('region');
              $zip = $this->getRequest()->getParam('zip');
              $dob = $this->getRequest()->getParam('dob');
              $ssn = $this->getRequest()->getParam('ssn');
              $phone = $this->getRequest()->getParam('phone');
              $email = $this->getRequest()->getParam('email');
              $order_id = $this->getRequest()->getParam('order_id');
              $customer_id = $this->getRequest()->getParam('customer_id');
              $address_type = "billing";

              $target = array(
                "firstname" => $fn,
                "lastname" => $ln,
                "street" => $addr,
                "city" => $city,
                "region" => $region,
                "postcode" => $zip,
                "ssn" => $ssn,
                "telephone" => $phone,
                "email" => $email,
                "ssn" => $ssn
              );

              $isVerified_check = $this->helper->veratadPost($target, $order_id, $customer_id, $address_type, $dob);
              $isVerified = false;
              if($customer_id){
                $accountNameMatch = $this->helper->nameDetectionAccount($customer_id, $target, $target);
                if($isVerified_check && $accountNameMatch){
                  $isVerified = true;
                }
              }else{
                $isVerified = $isVerified_check;
              }
              $total_attempts = $this->helper->getAmountOfAttempts($order_id);
              $attempts_allowed = $this->helper->getAttemptsAllowed($order_id);

              $attempts_left = ($total_attempts < $attempts_allowed);
              $dcams_site = $this->scopeConfig->getValue('settings/dcams/dcams_site_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
              $dcams_text = $this->scopeConfig->getValue('settings/content/dcams_intro', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                if($isVerified){
                  $return = array(
                    "action" => "PASS",
                    "attempts_left" => $attempts_left
                  );
                  $text = $this->scopeConfig->getValue('settings/content/agematch_success_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                  $this->messageManager->getMessages(true);
                  $this->messageManager->addSuccess(__($text));
                }elseif(!$isVerified && $attempts_left){
                  $return = array(
                    "action" => "FAIL",
                    "attempts_left" => $attempts_left
                  );
                  $text = $this->scopeConfig->getValue('settings/content/agematch_failure_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                  $this->messageManager->getMessages(true);
                  $this->messageManager->addError(__($text));
                }elseif(!$isVerified && !$attempts_left){
                  $return = array(
                    "action" => "FAIL",
                    "attempts_left" => $attempts_left
                  );
                  $text = $this->scopeConfig->getValue('settings/content/agematch_exceeded_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                  $this->messageManager->getMessages(true);
                  if(!$dcams_site){
                    $this->messageManager->addError(__($text));
                  }else{
                    $this->messageManager->addWarning(__($dcams_text));
                  }
                }

            $json_result = $this->resultJsonFactory->create();
            return $json_result->setData($return);
        }
      }
