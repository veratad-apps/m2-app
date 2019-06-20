<?php

        namespace Veratad\AgeVerification\Controller\Attempts;

        use Magento\Framework\App\Action\Context;
        use Magento\Framework\View\Result\PageFactory;
        use Magento\Framework\Controller\Result\JsonFactory;

        class CheckAttempts extends \Magento\Framework\App\Action\Action
        {


            protected $helper;
            protected $_veratadHistory;
            protected $orderRepository;
            protected $scopeConfig;


            public function __construct(
                Context $context,
                PageFactory $resultPageFactory,
                JsonFactory $resultJsonFactory,
                \Veratad\AgeVerification\Helper\Data $helper,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
                )
            {

                $this->resultPageFactory = $resultPageFactory;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->helper = $helper;
                $this->_veratadHistory = $history;
                $this->orderRepository = $orderRepository;
                $this->scopeConfig = $scopeConfig;
                return parent::__construct($context);
            }


            public function execute()
            {

              $order_id = $this->getRequest()->getParam('order_id');
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

              if(($billing_action === "FAIL" && ($shipping_action === "PASS" || $shipping_action === null))){
                $eligible = "true";
              }else{
                $eligible = "false";
              }

              if($total_attempts >= $attempts_allowed){
                $return = array(
                  "action" => "false",
                  "attempts_allowed" => $attempts_allowed,
                  "shipping_amount" => $shipping_amount,
                  "billing_amount" => $billing_amount,
                  "eligible" => $eligible,
                  "total_attempts" => $total_attempts
                );
              }else{
                $return = array(
                  "action" => "true",
                  "attempts_allowed" => $attempts_allowed,
                  "shipping_amount" => $shipping_amount,
                  "billing_amount" => $billing_amount,
                  "eligible" => $eligible,
                  "total_attempts" => $total_attempts
                );
              }

              $json_result = $this->resultJsonFactory->create();
              return $json_result->setData($return);
          }
      }
