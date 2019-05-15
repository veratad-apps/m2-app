<?php
        namespace Veratad\AgeVerification\Block\Adminhtml\Order\View;


        class Custom extends \Magento\Backend\Block\Template
        {

          protected $request;
          protected $_veratadHistory;
          protected $authSession;
          protected $orderRepository;


          public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\App\Request\Http $request,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Magento\Backend\Model\Auth\Session $authSession,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            array $data = []
            )
          {
            $this->request = $request;
            $this->_veratadHistory = $history;
            $this->authSession = $authSession;
            $this->orderRepository = $orderRepository;
            parent::__construct($context, $data);
          }

          public function getVeratadAccountHistory()
          {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/block.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $customerid = $this->request->getParam('id');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('veratad_history'); //gives table name with prefix

            //Select Data from table
            $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE veratad_customer_id='.$customerid);
            return $result;

          }

          public function getVeratadDetails()
          {

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/block.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $orderid = $this->request->getParam('order_id');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('veratad_history'); //gives table name with prefix

            //Select Data from table
            $result = $connection->fetchAll('SELECT * FROM `'.$tableName.'` WHERE veratad_order_id='.$orderid);
            return $result;

          }

          public function getCurrentUser()
          {
            $user = $this->authSession->getUser();
            $username = $user->getUsername();
            return $username;
          }

          public function veratadOrderId()
          {
            $orderid = $this->request->getParam('order_id');
            return $orderid;
          }

          public function getVeratadActionOrder()
          {
            $orderid = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($orderid);
            $action = $order->getVeratadAction();
            return $action;
          }


        }
