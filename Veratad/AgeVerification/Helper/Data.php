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
          protected $indexerFactory;
          protected $_indexerCollectionFactory;
          protected $curlFactory;
          protected $jsonHelper;
          protected $orderRepository;
          protected $_customerRepositoryInterface;


          public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Customer\Model\Session $customerSession,
              \Magento\Backend\Model\Auth\Session $authSession,
              \Magento\Framework\App\Request\Http $request,
                \Magento\Framework\ObjectManager\ObjectManager $objectManager,
                \Magento\Framework\App\Http\Context $httpContext,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory,
                \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
                \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
                \Magento\Framework\Json\Helper\Data $jsonHelper,
              \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
              \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface
            )
          {
            $this->scopeConfig = $scopeConfig;
            $this->customerSession = $customerSession;
            $this->authSession = $authSession;
            $this->request = $request;
            $this->_objectManager = $objectManager;
            $this->httpContext = $httpContext;
            $this->_veratadHistory = $history;
            $this->indexerFactory = $indexerFactory;
            $this->_indexerCollectionFactory = $indexerCollectionFactory;
            $this->curlFactory = $curlFactory;
            $this->jsonHelper = $jsonHelper;
            $this->orderRepository = $orderRepository;
            $this->customerRepository = $_customerRepositoryInterface;
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

                    $httpAdapter = $this->curlFactory->create();
                    $httpAdapter->write(\Zend_Http_Client::POST, $endpoint, '1.1', ["Content-Type:application/json"],$data_string);
                    $result = $httpAdapter->read();
                    $body = \Zend_Http_Response::extractBody($result);

                    $array_result = $this->jsonHelper->jsonDecode($body);

                     $data['pass'] = "xxxxx";
                     $log_query = json_encode($data);
                     $logger->info('query:' . $log_query);

                     $logger->info('response:' . $body);

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

                     $order = $this->orderRepository->get($orderid);
                     $order->setVeratadAction($action);
                     $order->save();

                     /*
                     if($customerid){
                       $customer = $this->_customerFactory->create();
                       $logger->info("customer model =" . json_encode($customer));
                       $customer->setCustomAttribute('veratad_action', $action);
                       $customer->save();
                     }
                     */

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
                  $history = $this->_veratadHistory->create();
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

          }