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


            public function __construct(
                Context $context,
                PageFactory $resultPageFactory,
                JsonFactory $resultJsonFactory,
                \Veratad\AgeVerification\Helper\Data $helper,
                \Veratad\AgeVerification\Model\HistoryFactory $history,
                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
                )
            {

                $this->resultPageFactory = $resultPageFactory;
                $this->resultJsonFactory = $resultJsonFactory;
                $this->helper = $helper;
                $this->_veratadHistory = $history;
                $this->orderRepository = $orderRepository;
                return parent::__construct($context);
            }


            public function execute()
            {

              $order_id = $this->getRequest()->getParam('order_id');
              $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
              $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
              $connection = $resource->getConnection();
              $tableName = $resource->getTableName('veratad_history'); //gives table name with prefix

              //Select Data from table
              $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE veratad_order_id='.$order_id);

              $billing_amount = 0;
              $shipping_amount = 0;
              $shipping_action = null;
              $billing_action = null;
              foreach ($result as $record){
                $address_type = $record['veratad_address_type'];
                if ($address_type === "billing"){
                  $billing_amount++;
                  $billing_action = $record['veratad_action'];
                }elseif($address_type === "shipping"){
                  $shipping_amount++;
                  $shipping_action = $record['veratad_action'];
                }
              }

              if($shipping_amount === 0){
                $attempts_allowed = 2;
              }else{
                $attempts_allowed = 3;
              }

              $total_attempts = count($result);

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
                  "eligible" => $eligible
                );
              }else{
                $return = array(
                  "action" => "true",
                  "attempts_allowed" => $attempts_allowed,
                  "shipping_amount" => $shipping_amount,
                  "billing_amount" => $billing_amount,
                  "eligible" => $eligible
                );
              }

              $json_result = $this->resultJsonFactory->create();
              return $json_result->setData($return);
          }
      }
