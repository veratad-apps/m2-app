<?php
        namespace Veratad\AgeVerification\Block\Adminhtml\Order\View;


        class Custom extends \Magento\Backend\Block\Template
        {

          protected $request;
          protected $_veratadHistory;
          protected $authSession;
          protected $orderRepository;
          protected $_escaper;


          public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\App\Request\Http $request,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Magento\Backend\Model\Auth\Session $authSession,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Framework\Escaper $_escaper,
            array $data = []
            )
          {
            $this->request = $request;
            $this->_veratadHistory = $history;
            $this->authSession = $authSession;
            $this->orderRepository = $orderRepository;
            $this->_escaper = $_escaper;
            parent::__construct($context, $data);
          }

          public function getVeratadAccountHistory()
          {
            $customerid = $this->request->getParam('id');
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_customer_id', array('eq' => $customerid))->getData();
            return $collection;
          }

          public function getVeratadDetails()
          {
            $order_id = $this->request->getParam('order_id');
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();
            return $collection;
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
            $order_id = $this->request->getParam('order_id');
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();
            $last = end($collection);
            $action = $last['veratad_action'];
            return $action;
          }

          public function getDcamsFront()
          {
            $order_id = $this->request->getParam('order_id');
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();
            $last = end($collection);
            $front = $last['veratad_id_front'];
            return $front;
          }

          public function getDcamsBack()
          {
            $order_id = $this->request->getParam('order_id');
            $history = $this->_veratadHistory->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $order_id))->getData();
            $last = end($collection);
            $back = $last['veratad_id_back'];
            return $back;
          }


        }
