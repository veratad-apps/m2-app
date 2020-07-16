<?php

        namespace Veratad\AgeVerification\Helper;

        use \Magento\Framework\App\Helper\AbstractHelper;
        use Magento\Framework\Controller\Result\JsonFactory;

        class Data extends AbstractHelper
        {

          protected $scopeConfig;
          protected $customerSession;
          protected $authSession;
          protected $request;
          private $httpContext;
          protected $_veratadHistory;
          protected $_veratadAccount;
          protected $indexerFactory;
          protected $_indexerCollectionFactory;
          protected $curlFactory;
          protected $jsonHelper;
          protected $orderRepository;
          protected $_customerRepositoryInterface;
          protected $date;
          protected $_order;
          protected $_checkoutSession;


          public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Customer\Model\Session $customerSession,
              \Magento\Backend\Model\Auth\Session $authSession,
              \Magento\Framework\App\Request\Http $request,
                \Magento\Framework\App\Http\Context $httpContext,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Veratad\AgeVerification\Model\AccountFactory $account,
                \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory,
                \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
                \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
                \Magento\Framework\Json\Helper\Data $jsonHelper,
              \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
              \Magento\Sales\Model\Order $order,
              \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
              JsonFactory $resultJsonFactory,
              \Magento\Checkout\Model\Session $checkoutSession,
              \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
            )
          {
            $this->scopeConfig = $scopeConfig;
            $this->customerSession = $customerSession;
            $this->authSession = $authSession;
            $this->request = $request;
            $this->httpContext = $httpContext;
            $this->_veratadHistory = $history;
            $this->_veratadAccount = $account;
            $this->indexerFactory = $indexerFactory;
            $this->_indexerCollectionFactory = $indexerCollectionFactory;
            $this->curlFactory = $curlFactory;
            $this->jsonHelper = $jsonHelper;
            $this->orderRepository = $orderRepository;
            $this->_order = $order;
            $this->_checkoutSession = $checkoutSession;
            $this->customerRepository = $_customerRepositoryInterface;
            $this->resultJsonFactory = $resultJsonFactory;
            $this->date = $date;
          }


            public function executeIndexCustomerGrid()
            {
              $this->indexerFactory->create()->load('customer_grid')->reindexAll();
            }

          public function getCurrentUserVeratad()
          {
            $user = $this->authSession->getUser();
            $username = $user->getUsername();
            return $username;
          }

          public function setVeratadActionOnAccount($action, $customerid)
          {
            $customer = $this->customerRepository->getById($customerid);
            $customer->setCustomAttribute('veratad_action', $action);
            $this->executeIndexCustomerGrid();
          }


          public function nameDetection($target, $shipping){

                 $target_firstname = $target['firstname'];
                 $target_lastname = $target['lastname'];
                 $shipping_firstname = $shipping['firstname'];
                 $shipping_lastname = $shipping['lastname'];

                 $targetName = strtolower($target_firstname . $target_lastname);
                 $shippingName = strtolower($shipping_firstname . $shipping_lastname);

                 $match = ($targetName === $shippingName);

                 return $match;

            }

               public function nameDetectionAccount($customerid, $billing, $shipping){

                    $customer = $this->customerRepository->getById($customerid);
                    $customer_firstname = $customer->getFirstname();
                    $customer_lastname = $customer->getLastname();

                      $billing_firstname = $billing['firstname'];
                      $billing_lastname = $billing['lastname'];
                      $shipping_firstname = $shipping['firstname'];
                      $shipping_lastname = $shipping['lastname'];

                      $billingName = strtolower($billing_firstname . $billing_lastname);
                      $shippingName = strtolower($shipping_firstname . $shipping_lastname);
                      $customerName = strtolower($customer_firstname . $customer_lastname);

                      $match = (($billingName === $shippingName) && ($shippingName === $customerName));

                      return $match;

                }

                public function checkZipException($zip){
                  $match = null;
                  $age_to_check = null;
                  $zip_exceptions = $this->scopeConfig->getValue('state_ages/age_zip/age_to_check_zip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                  if($zip_exceptions){
                    $zips = json_decode($zip_exceptions, true);

                    foreach($zips as $zip_here){
                      $zip_exception = $zip_here['zip'];
                      if (strpos($zip_exception, '-') !== false) {
                        $zip_range = explode('-', $zip_exception);
                        $low = $zip_range[0];
                        $high = $zip_range[1];
                        if($zip >= $low && $zip <= $high ){
                          $match = true;
                        }
                      }
                      if($zip_exception === $zip || $match){
                        $age = $zip_here['age'];
                        $age_to_check = $age;
                        break;
                      }
                    }
                  }

                  return $age_to_check;
                }

                public function checkStateException($state)
                {
                  $age_to_check = null;
                  $state_exceptions = $this->scopeConfig->getValue('state_ages/age_state/age_to_check', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                  if($state_exceptions){
                    $states = json_decode($state_exceptions, true);
                    $state_on_target = strtolower($state);
                    foreach($states as $state_here){

                      $state_exception = strtolower($state_here['state']);

                      if($state_exception === $state_on_target){
                        $age = $state_here['age'];
                        $age_to_check = $age;
                        break;
                      }
                    }
                  }

                  return $age_to_check;
                }

                public function ageToCheck($state, $zip)
                {
                  $global_age = $this->scopeConfig->getValue('state_ages/global/global_age', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                  $zip_exception = $this->checkZipException($zip);
                  $state_exception = $this->checkStateException($state);

                  if($zip_exception){
                    $age_to_check = $zip_exception;
                  }elseif($state_exception){
                    $age_to_check = $state_exception;
                  }else{
                    $age_to_check = $global_age;
                  }

                  return $age_to_check;
                }

               public function veratadPost($target, $orderid, $customerid, $address_type, $dob){

                 $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/veratad.log');
                 $logger = new \Zend\Log\Logger();
                 $logger->addWriter($writer);

                 $dcams_id = $orderid .'veratad_dcams@veratadmagento.com';

                 $user = $this->scopeConfig->getValue('settings/agematch/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $pass = $this->scopeConfig->getValue('settings/agematch/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $rules = $this->scopeConfig->getValue('settings/agematch/agematchrules', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $endpoint = $this->scopeConfig->getValue('settings/agematch/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $test_mode = $this->scopeConfig->getValue('settings/general/test_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $test_key = null;
                 if($test_mode){
                   $test_key = $this->scopeConfig->getValue('settings/general/test_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 }
                 $yob = substr($dob, 0, 4);
                 $current_year = $this->date->date()->format('Y');

                 if ($yob >= 1900 && $yob < $current_year){
                   $dob = $dob;
                 }else{
                   $dob = "";
                 }

                  $fn = $target['firstname'];
                  $ln = $target['lastname'];
                  $addr = $target['street'];
                  $addr_clean = str_replace("\n", ' ', $addr);
                  $city = $target['city'];
                  $state = $target['region'];
                  $zip = $target['postcode'];
                  $phone = $target['telephone'];
                  $email = $target['email'];

                   if (array_key_exists('ssn', $target)) {
                     $ssn = $target['ssn'];
                   }else{
                     $ssn = "";
                   }

                   $age = $this->ageToCheck($state, $zip);

                   if(!$age){
                     $age = "21+";
                   }

                   $data = array(
                     "user" => $user,
                     "pass" => $pass,
                     "service" => "AgeMatch5.0",
                     "rules" => $rules,
                     "reference" => $email,
                         "target" => array(
                           "fn" => $fn,
                           "ln" => $ln,
                           "addr" => $addr_clean,
                           "city" => $city,
                           "state" => $state,
                           "zip" => $zip,
                           "age" => $age,
                           "dob" => $dob,
                           "ssn" => $ssn,
                           "phone" => $phone,
                           "email" => $email,
                           "test_key" => $test_key
                         )
                     );

                    $data_string = json_encode($data);

                    $httpAdapter = $this->curlFactory->create();
                    $httpAdapter->write(\Zend_Http_Client::POST, $endpoint, '1.1', ["Content-Type:application/json"],$data_string);
                    $result = $httpAdapter->read();
                    $body = \Zend_Http_Response::extractBody($result);

                    $array_result = $this->jsonHelper->jsonDecode($body);

                     $data['pass'] = "xxxx";
                     $data['target']['ssn'] = "xxxx";
                     $log_query = json_encode($data);
                     $logger->info('query:' . $log_query);

                     $logger->info('response:' . $body);

                     $action = $array_result['result']['action'];
                     $detail = $array_result['result']['detail'];
                     $timestamp = $array_result['meta']['timestamp'];
                     $confirmation = $array_result['meta']['confirmation'];
                     $manual = "FALSE";

                     if($orderid){
                       $order = $this->orderRepository->get($orderid);
                       $order->setVeratadAction($action);
                       $order->save();
                       $orderid = $orderid;
                     }else{
                       $orderid = "NONE";
                     }

                     $this->_veratadHistory->create()->setData(
                       array("veratad_action" => $action,
                       "veratad_detail" => $detail,
                       "veratad_confirmation" => $confirmation,
                       "veratad_timestamp" => $timestamp,
                       "veratad_override" => $manual,
                       "veratad_order_id" => $orderid,
                       "veratad_dcams_id" => $dcams_id,
                       "veratad_customer_id" => $customerid,
                       "veratad_address_type" => $address_type,
                     ))->save();


                     if($customerid){
                       $this->_veratadAccount->create()->setData(
                         array("veratad_action" => $action,
                         "veratad_detail" => $detail,
                         "veratad_confirmation" => $confirmation,
                         "veratad_timestamp" => $timestamp,
                         "veratad_override" => $manual,
                         "veratad_order_id" => $orderid,
                         "veratad_customer_id" => $customerid,
                       ))->save();
                     }



                     $final = ($action === "PASS");

                     return $final;

              }

              public function getVeratadAccountAction()
              {
                  $customerid = $this->request->getParam('id');
                  $history = $this->_veratadHistory->create();
                  $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
                  $last = end($collection);
                  $action = $last['veratad_action'];
                  return $action;
              }

              public function getVeratadAccountActionById($customerid)
              {
                  $history = $this->_veratadAccount->create();
                  $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
                  $last = end($collection);
                  $action = $last['veratad_action'];
                  return $action;
              }

              public function getVeratadCustomerId()
              {
                  $customerid = $this->request->getParam('id');
                  return $customerid;
              }

              public function getVeratadAccountHistoryHelper()
              {
                $customerid = $this->request->getParam('id');
                $history = $this->_veratadHistory->create();
                $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
                return $result;
              }

              public function getVeratadAccountDetails()
              {

                $customerid = $this->request->getParam('id');
                $history = $this->_veratadHistory->create();
                $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
                return $result;
              }

              public function checkAttempts($order_id)
              {

                $history = $this->_veratadHistory->create();
                $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();

                $billing_amount = 0;
                $shipping_amount = 0;
                $shipping_action = null;
                $billing_action = null;
                foreach ($collection as $record){
                  $address_type = $record['veratad_address_type'];
                  if ($address_type === "billing"){
                    $billing_amount++;
                    $billing_action = $record['veratad_action'];
                  }elseif($address_type === "shipping"){
                    $shipping_amount++;
                    $shipping_action = $record['veratad_action'];
                  }
                }

                $attempts_allowed_config = $this->scopeConfig->getValue('settings/agematch/agematchattempts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                if($shipping_amount === 0){
                  $attempts_allowed = $attempts_allowed_config;
                }else{
                  $attempts_allowed = $attempts_allowed_config + 1;
                }

                $total_attempts = count($collection);

              //check if they are eligible for a 2nd try

                if(($billing_action === "FAIL" && $total_attempts < $attempts_allowed && ($shipping_action === "PASS" || $shipping_action === null))){
                  $eligible = "true";
                }else{
                  $eligible = "false";
                }

                return $eligible;
            }


            public function getAmountOfAttempts($order_id)
            {
              $history = $this->_veratadHistory->create();
              $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();
              return count($collection);
            }

            public function getAttemptsAllowed($order_id)
            {
              $history = $this->_veratadHistory->create();
              $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();

              $billing_amount = 0;
              $shipping_amount = 0;
              $shipping_action = null;
              $billing_action = null;
              foreach ($collection as $record){
                $address_type = $record['veratad_address_type'];
                if ($address_type === "billing"){
                  $billing_amount++;
                  $billing_action = $record['veratad_action'];
                }elseif($address_type === "shipping"){
                  $shipping_amount++;
                  $shipping_action = $record['veratad_action'];
                }
              }

              $attempts_allowed_config = $this->scopeConfig->getValue('settings/agematch/agematchattempts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

              if($shipping_amount === 0){
                $attempts_allowed = $attempts_allowed_config;
              }else{
                $attempts_allowed = $attempts_allowed_config + 1;
              }

              return $attempts_allowed;
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
                'email' => $order->getBillingAddress()->getData("email"),
                'phone' => $order->getBillingAddress()->getData("telephone"),
                'customer_id' => $order->getCustomerId(),
                'action' => $order->getVeratadAction()
              );

              return $data;
            }

        }
