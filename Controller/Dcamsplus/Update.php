<?php

        namespace Veratad\AgeVerification\Controller\Dcamsplus;

        use Magento\Framework\App\Action\Context;
        use Magento\Framework\View\Result\PageFactory;
        use Magento\Framework\Controller\Result\JsonFactory;
        use Magento\Customer\Model\Customer;

        class Update extends \Magento\Framework\App\Action\Action
        {

            protected $helper;
            protected $_veratadHistory;
            protected $_veratadAccount;
            protected $orderRepository;
            protected $jsonHelper;
            private $scopeConfig;
            protected $messageManager;
            protected $curlFactory;
            protected $curlClient;
            protected $_httpClientFactory;
            protected $zendClient;


            public function __construct(
                Context $context,
                PageFactory $resultPageFactory,
                JsonFactory $resultJsonFactory,
                \Veratad\AgeVerification\Helper\Data $helper,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Veratad\AgeVerification\Model\AccountFactory $account,
                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                  \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
                  \Magento\Framework\Json\Helper\Data $jsonHelper,
                  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                  \Magento\Framework\Message\ManagerInterface $messageManager,
                  \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
                  \Magento\Framework\HTTP\Client\Curl $curl,
                  \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
                  \Zend\Http\Client $zendClient
                )
            {

                $this->resultPageFactory = $resultPageFactory;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->helper = $helper;
                $this->_veratadHistory = $history;
                $this->_veratadAccount = $account;
                $this->orderRepository = $orderRepository;
                $this->jsonHelper = $jsonHelper;
                $this->scopeConfig = $scopeConfig;
                $this->messageManager = $messageManager;
                $this->curlFactory = $curlFactory;
                $this->curl = $curl;
                $this->_httpClientFactory = $httpClientFactory;
                $this->zendClient = $zendClient;
                return parent::__construct($context);
            }


            public function execute()
            {

              $user = $this->scopeConfig->getValue('settings/dcams/dcams_user', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
              $pass = $this->scopeConfig->getValue('settings/dcams/dcams_pass', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

              $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dcams.log');
              $logger = new \Zend\Log\Logger();
              $logger->addWriter($writer);

              $endpoint = "https://verataddev.com/magentoProxy/getByEmail.php";
              $id = $this->getRequest()->getParam('email');
              $order_id = $this->getRequest()->getParam('order');
              $customer_id = $this->getRequest()->getParam('customer');

              $request = array(
                "service" => "getByEmail",
                "email" => $id
              );

              $data_string = json_encode($request);

          $headers = [
            'Content-type: application/json',
            'Username: '.$user.'',
            'Password: '.$pass.''
          ];

            $httpAdapter = $this->curlFactory->create();
            $httpAdapter->write(\Zend_Http_Client::POST, $endpoint, '1.1', $headers, $data_string);
            $result = $httpAdapter->read();
            $body = \Zend_Http_Response::extractBody($result);

            $logger->info($body);

            $array_result = $this->jsonHelper->jsonDecode($body);

            $status = $array_result['statusid'];
            $front = $array_result['idfront'];
            $back = $array_result['idback'];

            if($status === "2"){
              $action = "PASS";
            }else{
              $action = "PENDING";
            }

              $this->_veratadHistory->create()->setData(
                array("veratad_action" => $action,
                "veratad_detail" => "DCAMS+",
                "veratad_confirmation" => "",
                "veratad_timestamp" => "",
                "veratad_override" => "NONE",
                "veratad_order_id" => $order_id,
                "veratad_dcams_id" => $id,
                "veratad_override_user" => "NONE",
                "veratad_customer_id" => $customer_id,
                "veratad_id_front" => $front,
                "veratad_id_back" => $back
              ))->save();

              if($customer_id){
                $this->_veratadAccount->create()->setData(
                  array("veratad_action" => $action,
                  "veratad_detail" => "DCAMS+",
                  "veratad_confirmation" => "",
                  "veratad_timestamp" => "",
                  "veratad_override" => "NONE",
                  "veratad_override_user" => "NONE",
                  "veratad_customer_id" => $customer_id,
                ))->save();
              }


              $order = $this->orderRepository->get($order_id);
              $order->setVeratadAction($action);
              $order->save();
        }
      }
