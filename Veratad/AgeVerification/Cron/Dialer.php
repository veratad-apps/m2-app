<?php

    namespace Veratad\AgeVerification\Cron;

    class Dialer
    {

      protected $date;
      protected $_OrderCollection;
      protected $orderRepository;
      protected $helper;


      public function __construct(
          \Magento\Framework\Stdlib\DateTime\DateTime $date,
          \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $_OrderCollection,
          \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
          \Veratad\AgeVerification\Helper\Cron $helper
      )
      {
           $this->date = $date;
           $this->_OrderCollection = $_OrderCollection;
           $this->orderRepository = $orderRepository;
           $this->helper = $helper;
      }


    	public function execute()
    	{


        //get orders with pending status
        $orders = $this->_OrderCollection->create()
        ->addAttributeToFilter('veratad_dialer', 'PENDING')->getData();

    		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dialer.log');
    		$logger = new \Zend\Log\Logger();
    		$logger->addWriter($writer);

        $logger->info("pending_orders_to_call: " . json_encode($orders));

        $orders_to_call = $this->helper->getOrdersForDial($orders);
        $logger->info("orders_to_call = " . json_encode($orders_to_call));
        foreach($orders_to_call as $dialer){
          $phone = $dialer['phone'];
          $orderid = $dialer['order_id'];
          $this->helper->veratadPostDialer($phone, $orderid);
        }
    	}
    }
