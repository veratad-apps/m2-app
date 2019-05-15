<?php

        namespace Veratad\AgeVerification\Observer;

        use Magento\Framework\ObjectManager\ObjectManager;
        use Magento\Framework\Exception\CouldNotSaveException;

        class ProcessOrder implements \Magento\Framework\Event\ObserverInterface {


          protected $_veratadCall;
          protected $customerSession;
          protected $_isExcluded;
          private $scopeConfig;
          protected $helper;
          protected $_veratadHistory;
          protected $_veratadAccount;
          private $responseFactory;
          private $url;
          protected $orderRepository;

          public function __construct(
            \Veratad\AgeVerification\Model\Query\Api $veratadCall,
            \Veratad\AgeVerification\Model\Customer\Status $isExcluded,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Veratad\AgeVerification\Helper\Data $helper,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Veratad\AgeVerification\Model\VeratadAccountFactory $account,
            \Magento\Framework\App\ResponseFactory $responseFactory,
            \Magento\Framework\UrlInterface $url,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
          ) {
           $this->_veratadCall = $veratadCall;
            $this->_isExcluded = $isExcluded;
            $this->customerSession = $customerSession;
            $this->scopeConfig = $scopeConfig;
            $this->helper = $helper;
            $this->_veratadHistory = $history;
            $this->_veratadAccount = $account;
            $this->responseFactory = $responseFactory;
            $this->url = $url;
            $this->orderRepository = $orderRepository;
          }



            public function execute(\Magento\Framework\Event\Observer $observer ) {


              $enabled = $this->scopeConfig->getValue('veratad/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

              if($enabled){

                //current data
                $order_ids = $observer->getEvent()->getOrderIds();
                $order_id = $order_ids[0];
                $order = $this->orderRepository->get($order_id);
                $billing = $order->getBillingAddress()->getData();
                $shipping = $order->getShippingAddress()->getData();



                if ($this->customerSession->isLoggedIn()) {
                  $customer_id = $this->customerSession->getCustomer()->getId();
                  //check to see if customer is excluded
                  $excluded = $this->_isExcluded->isExcluded($customer_id, $billing, $shipping);
                }else{
                  $customer_id = "NONE";
                  $excluded = null;
                }

                if(!$excluded){
                  $isVerified = $this->_veratadCall->veratadCaller($billing, $shipping, $order_id, $customer_id);
                  if (!$isVerified){
                    if ($this->customerSession->isLoggedIn()) {
                    $this->helper->setVeratadActionOnAccount("FAIL", $customer_id);
                    }
                    $order->setVeratadAction("FAIL");
                    $order->save();
                    $redirectionUrl = $this->url->getUrl("ageverification/dcams/display?id=$order_id");
                    $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                    return $this;
                  }else{
                    $order->setVeratadAction("PASS");
                    $order->save();
                    if ($this->customerSession->isLoggedIn()) {
                    $this->helper->setVeratadActionOnAccount("PASS", $customer_id);
                    }
                  }
                }else{
                  $order->setVeratadAction("PASS");
                  $order->save();
                }
              }

            }
          }
