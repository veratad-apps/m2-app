<?php

        namespace Veratad\AgeVerification\Observer;

        use Magento\Framework\Exception\CouldNotSaveException;

        class ProcessOrder implements \Magento\Framework\Event\ObserverInterface {


          protected $_veratadCall;
          protected $customerSession;
          protected $_isExcluded;
          private $scopeConfig;
          protected $helper;
          protected $helperCron;
          protected $_veratadHistory;
          private $responseFactory;
          private $url;
          protected $orderRepository;
          protected $checkoutSession;
          protected $quoteRepository;

          public function __construct(
            \Veratad\AgeVerification\Model\Query\Api $veratadCall,
            \Veratad\AgeVerification\Model\Customer\Status $isExcluded,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Veratad\AgeVerification\Helper\Data $helper,
            \Veratad\AgeVerification\Helper\Cron $helperCron,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Magento\Framework\App\ResponseFactory $responseFactory,
            \Magento\Framework\UrlInterface $url,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
          ) {
           $this->_veratadCall = $veratadCall;
            $this->_isExcluded = $isExcluded;
            $this->customerSession = $customerSession;
            $this->scopeConfig = $scopeConfig;
            $this->helper = $helper;
            $this->helperCron = $helperCron;
            $this->_veratadHistory = $history;
            $this->responseFactory = $responseFactory;
            $this->url = $url;
            $this->orderRepository = $orderRepository;
            $this->checkoutSession = $checkoutSession;
            $this->quoteRepository = $quoteRepository;
          }



            public function execute(\Magento\Framework\Event\Observer $observer ) {

              $enabled = $this->scopeConfig->getValue('settings/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

              if($enabled){

                //current data
                $order_ids = $observer->getEvent()->getOrderIds();
                $order_id = $order_ids[0];
                $order = $this->orderRepository->get($order_id);
                $billing = $order->getBillingAddress()->getData();
                $shipping = $order->getShippingAddress()->getData();

                //check dialer state
                $billing_state = $order->getBillingAddress()->getData("region");
                $states_to_call = $this->helperCron->getStatesToCall();
                foreach($states_to_call as $states){
                  $state = $states['state'];
                }

                $quote_id = $order->getQuoteId();
                $quote = $this->quoteRepository->get($quote_id);
                $dob = $quote->getVeratadDob();


                if ($this->customerSession->isLoggedIn()) {
                  $customer_id = $this->customerSession->getCustomer()->getId();
                  //check to see if customer is excluded
                  $excluded = $this->_isExcluded->isExcluded($customer_id, $billing, $shipping);
                }else{
                  $customer_id = null;
                  $excluded = null;
                }

                if(!$excluded){
                  $isVerified = $this->_veratadCall->veratadCaller($billing, $shipping, $order_id, $customer_id, $dob);
                  if (!$isVerified){
                    if ($this->customerSession->isLoggedIn()) {
                    $this->helper->setVeratadActionOnAccount("FAIL", $customer_id);
                    }
                    $order->setVeratadAction("FAIL");
                    $order->save();
                    $redirectionUrl = $this->url->getUrl("ageverification/dcams/display");
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

                  $this->_veratadHistory->create()->setData(
                    array("veratad_action" => "PASS",
                    "veratad_detail" => "Verified or Excluded Customer",
                    "veratad_confirmation" => "",
                    "veratad_timestamp" => "",
                    "veratad_override" => "",
                    "veratad_order_id" => $order_id,
                    "veratad_dcams_id" => "",
                    "veratad_customer_id" => $customer_id,
                    "veratad_address_type" => "",
                  ))->save();

                  $order->setVeratadAction("PASS");
                  $order->save();
                }
              }

            }
          }
