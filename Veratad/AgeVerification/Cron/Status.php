<?php

    namespace Veratad\AgeVerification\Cron;

    class Status
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

        $pendings = $this->helper->getPendingDialers();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dialer_status.log');
    		$logger = new \Zend\Log\Logger();
    		$logger->addWriter($writer);
    		$logger->info("pendings = " . json_encode($pendings));


        foreach ($pendings as $pending){
          $token = $pending['veratad_token'];
          $order_id = $pending['veratad_order_id'];
          $this->helper->veratadPostStatus($token, $order_id);
        }


      }
    }
