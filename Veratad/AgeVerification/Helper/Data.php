<?php

        namespace Veratad\AgeVerification\Helper;

        use \Magento\Framework\App\Helper\AbstractHelper;

        class Data extends AbstractHelper
        {

          protected $scopeConfig;
          protected $customerSession;
          protected $authSession;
          protected $request;
          protected $_objectManager;
          private $httpContext;
          protected $_veratadHistory;
          protected $_veratadAccount;
          protected $indexerFactory;
          protected $_indexerCollectionFactory;


          public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Customer\Model\Session $customerSession,
              \Magento\Backend\Model\Auth\Session $authSession,
              \Magento\Framework\App\Request\Http $request,
                \Magento\Framework\ObjectManager\ObjectManager $objectManager,
                \Magento\Framework\App\Http\Context $httpContext,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Veratad\AgeVerification\Model\VeratadAccountFactory $account,
                \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory,
                \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
            )
          {
            $this->scopeConfig = $scopeConfig;
            $this->customerSession = $customerSession;
            $this->authSession = $authSession;
            $this->request = $request;
            $this->_objectManager = $objectManager;
            $this->httpContext = $httpContext;
            $this->_veratadHistory = $history;
            $this->_veratadAccount = $account;
            $this->indexerFactory = $indexerFactory;
            $this->_indexerCollectionFactory = $indexerCollectionFactory;
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

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('customer_entity'); //gives table name with prefix

            $sql = "UPDATE `$tableName` SET `veratad_action` = '".$action."' WHERE `entity_id` = '".$customerid."'";
            $connection->query($sql);

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

                      $customer = $this->_veratadAccount->create();
                      $customer->load($customerid,'veratad_customer_id');

                      $billing_firstname = $billing['firstname'];
                      $billing_lastname = $billing['lastname'];
                      $shipping_firstname = $shipping['firstname'];
                      $shipping_lastname = $shipping['lastname'];
                      $customer_firstname = $customer['veratad_fn'];
                      $customer_lastname = $customer['veratad_ln'];

                      $billingName = strtolower($billing_firstname . $billing_lastname);
                      $shippingName = strtolower($shipping_firstname . $shipping_lastname);
                      $customerName = strtolower($customer_firstname . $customer_lastname);

                      $match = (($billingName === $shippingName) && ($shippingName === $customerName));

                      return $match;

                }

               public function veratadPost($target, $orderid, $customerid, $address_type){

                 $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/veratad.log');
                 $logger = new \Zend\Log\Logger();
                 $logger->addWriter($writer);

                 $logger->info("target =" . json_encode($target));

                 $user = $this->scopeConfig->getValue('settings/agematch/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $pass = $this->scopeConfig->getValue('settings/agematch/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $rules = $this->scopeConfig->getValue('settings/agematch/agematchrules', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $endpoint = $this->scopeConfig->getValue('settings/agematch/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                 $global_age_to_check = $this->scopeConfig->getValue('age/general_age/global_age', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                   $fn = $target['firstname'];
                   $ln = $target['lastname'];
                   $addr = $target['street'];
                   $addr_clean = str_replace("\n", ' ', $addr);
                   $city = $target['city'];
                   $state = $target['region'];
                   $zip = $target['postcode'];
                   $phone = $target['telephone'];
                   $email = $target['email'];
                   /*
                   $custom_attr = $target['customAttributes'];
                   foreach ($custom_attr as $attr){
                     $code = $attr['attribute_code'];
                     if($code === "veratad_dob"){
                       $dob = $attr['value'];
                     }
                   }
                   */


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
                           "age" => $global_age_to_check,
                           "dob" => "",
                           "phone" => $phone,
                           "email" => $email
                         )
                     );

                     $data_string = json_encode($data);
                     $data['pass'] = "xxxxx";
                     $log_query = json_encode($data);
                     $logger->info('query:' . $log_query);

                     $ch = curl_init();
                     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                     curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                     curl_setopt($ch, CURLOPT_URL, $endpoint);
                     curl_setopt($ch, CURLOPT_FAILONERROR, 0);
                     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                     curl_setopt($ch, CURLOPT_TIMEOUT, 200);
                     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                     $result = curl_exec($ch);

                     $logger->info('response:' . $result);

                     $array_result = json_decode($result, true);

                     $action = $array_result['result']['action'];
                     $detail = $array_result['result']['detail'];
                     $timestamp = $array_result['meta']['timestamp'];
                     $confirmation = $array_result['meta']['confirmation'];
                     $manual = "FALSE";


                     $this->_veratadHistory->create()->setData(
                       array("veratad_action" => $action,
                       "veratad_detail" => $detail,
                       "veratad_confirmation" => $confirmation,
                       "veratad_timestamp" => $timestamp,
                       "veratad_override" => $manual,
                       "veratad_order_id" => $orderid,
                       "veratad_customer_id" => $customerid,
                       "veratad_address_type" => $address_type,
                     ))->save();

                     if($customerid){
                       $this->_veratadAccount->create()->setData(
                         array("veratad_action" => $action,
                         "veratad_detail" => $detail,
                         "veratad_confirmation" => $confirmation,
                         "veratad_timestamp" => $timestamp,
                         "veratad_fn" => $fn,
                         "veratad_ln" => $ln,
                         "veratad_customer_id" => $customerid
                       ))->save();


                       $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                       $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                       $connection = $resource->getConnection();
                       $tableName = $resource->getTableName('customer_entity'); //gives table name with prefix

                       $sql = "UPDATE `$tableName` SET `veratad_action` = '".$action."' WHERE `entity_id` = '".$customerid."'";
                       $connection->query($sql);

                     }


                     $final = ($action === "PASS");

                     return $final;

              }

              public function getVeratadAccountAction()
              {
                  $customerid = $this->request->getParam('id');
                  $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                  $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                  $connection = $resource->getConnection();
                  $tableName = $resource->getTableName('customer_entity'); //gives table name with prefix

                  //Select Data from table
                  $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE entity_id='.$customerid);
                  return $result;
              }

              public function getVeratadCustomerId()
              {
                  $customerid = $this->request->getParam('id');
                  return $customerid;
              }

              public function getVeratadAccountHistoryHelper()
              {
                $customerid = $this->request->getParam('id');
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('veratad_history'); //gives table name with prefix

                //Select Data from table
                $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE veratad_customer_id='.$customerid);
                return $result;

              }

              public function getVeratadAccountDetails()
              {

                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/helper.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);

                $customerid = $this->request->getParam('id');
                $logger->info("Customer ID = $customerid");
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('veratad_account'); //gives table name with prefix

                //Select Data from table
                $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE veratad_customer_id='.$customerid);
                return $result;
              }

          }
