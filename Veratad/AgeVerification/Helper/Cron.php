<?php

        namespace Veratad\AgeVerification\Helper;

        use \Magento\Framework\App\Helper\AbstractHelper;

        class Cron extends AbstractHelper
        {

          protected $scopeConfig;
          protected $_veratadHistory;
          protected $_veratadDialer;
          protected $orderRepository;
          protected $curlFactory;
          protected $jsonHelper;
          protected $date;


          public function __construct(
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Veratad\AgeVerification\Model\HistoryFactory $history,
            \Veratad\AgeVerification\Model\DialerFactory $dialer,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
            \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Stdlib\DateTime\DateTime $date
            )
          {
            $this->scopeConfig = $scopeConfig;
            $this->_veratadHistory = $history;
            $this->_veratadDialer = $dialer;
            $this->orderRepository = $orderRepository;
            $this->curlFactory = $curlFactory;
            $this->jsonHelper = $jsonHelper;
            $this->date = $date;
          }

          public function getStatesToCall()
          {
            $states_to_call = $this->scopeConfig->getValue('settings/dialer/dialer_config', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $return = json_decode($states_to_call, true);
            return $return;
          }

          public function isOrderDialer($billing_state, $shipping_state)
          {
            $result = false;
            $states_to_call = $this->getStatesToCall();
	if($states_to_call){
            foreach($states_to_call as $states){
              $state = $states['state'];
              $state_to_check = strtolower($state);
              $billing_state_to_check = strtolower($billing_state);
              $shipping_state_to_check = strtolower($shipping_state);
              if($billing_state_to_check === $state_to_check || $shipping_state_to_check === $state_to_check){
                $result = true;
                return $result;
                exit;
              }
            }
	}
          }

          public function getOrdersForDial($orders)
          {
            $states_to_call = $this->getStatesToCall();
            $current_hour = $this->date->gmtDate('H');
            $result = array();
            foreach($states_to_call as $states){
              $state = $states['state'];
              $time = $states['time'];
              if($time === $current_hour){
                $is_allowed_per_time = true;
              }else{
                $is_allowed_per_time = false;
              }

              $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dialer.log');
              $logger = new \Zend\Log\Logger();
              $logger->addWriter($writer);

              $logger->info("current hour = $current_hour time to call = $time is allowed = $is_allowed_per_time");

              foreach($orders as $order){
                $id = $order['entity_id'];
                $order_data = $this->orderRepository->get($id);
                $billing_state = $order_data->getBillingAddress()->getData("region");
                $shipping_state = $order_data->getShippingAddress()->getData("region");
                $phone = $order_data->getBillingAddress()->getData("telephone");
                $action = $order['veratad_action'];
                if($action === "PASS" && $is_allowed_per_time && ($billing_state === $state || $shipping_state === $state)){
                  $result[] = array(
                    'order_id' => $id,
                    'phone' => $phone
                  );
                }

              }
              $id = null;
              $phone = null;
              $action = null;
            }
            return $result;
          }

          public function isCallMade($orderid)
          {
            $history = $this->_veratadDialer->create();
            $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $orderid))->getData();
            if($collection){
              return true;
            }else{
              return false;
            }
          }

          public function veratadPostDialer($phone, $orderid){

            if(!$this->isCallMade($orderid)){

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/veratad.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $logger->info("iscallmade " . $this->isCallMade($orderid));

            $user = $this->scopeConfig->getValue('settings/agematch/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $pass = $this->scopeConfig->getValue('settings/agematch/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $excluded_phone_types = $this->scopeConfig->getValue('settings/dialer/dialer_excluded', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $phone_types = (explode( ',', $excluded_phone_types ));
            $phone_types_send = array_values($phone_types);

            $endpoint = "https://production.idresponse.com/process/comprehensive/gateway";

              $data = array(
                "user" => $user,
                "pass" => $pass,
                "service" => "PhoneMatch5.0.Dialer",
                "reference" => $orderid,
                    "target" => array(
                      "phone" => $phone
                    ),
                    "options" => array(
                      "outofband" => array(
                        "message" => "test.message",
			                  "do_not_send_to_types" => $phone_types_send
                      )
                    )
                );

               $data_string = json_encode($data);

               $httpAdapter = $this->curlFactory->create();
               $httpAdapter->write(\Zend_Http_Client::POST, $endpoint, '1.1', ["Content-Type:application/json"],$data_string);
               $result = $httpAdapter->read();
               $body = \Zend_Http_Response::extractBody($result);

               $array_result = $this->jsonHelper->jsonDecode($body);

                $data['pass'] = "xxxx";
                $log_query = json_encode($data);
                $logger->info('query:' . $log_query);
                $logger->info('response:' . $body);

                $action = $array_result['result']['action'];
                $detail = $array_result['result']['detail'];
                $timestamp = $array_result['meta']['timestamp'];
                $confirmation = $array_result['meta']['confirmation'];
                if (array_key_exists('continuations', $array_result)) {
                  $token_save = $array_result['continuations']['outofband']['template']['token'];
                }else{
                  $token_save = null;
                }


                $this->_veratadDialer->create()->setData(
                  array("veratad_action" => $action,
                  "veratad_detail" => $detail,
                  "veratad_token" => $token_save,
                  "veratad_confirmation" => $confirmation,
                  "veratad_timestamp" => $timestamp,
                  "veratad_order_id" => $orderid
                ))->save();

                $order = $this->orderRepository->get($orderid);
                $order->setVeratadDialer($action);
                $order->save();
            }
         }

         public function getPendingDialers(){
           $history = $this->_veratadDialer->create();
           $collection = $history->getCollection()->addFieldToFilter('veratad_action', array('eq' => 'PENDING'))->getData();
           return $collection;
         }

         public function setDialerAction($orderid, $action, $detail){
           $history = $this->_veratadDialer->create();
           $collection = $history->getCollection()->addFieldToFilter('veratad_order_id', array('eq' => $orderid));
           $collection->getFirstItem()->setData('veratad_action',$action)->setData('veratad_detail',$detail)->save();
         }

         public function veratadPostStatus($token, $orderid){

           $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/veratad.log');
           $logger = new \Zend\Log\Logger();
           $logger->addWriter($writer);

           $endpoint = "https://production.idresponse.com/process/continue";

             $data = array(
                   "dialer" => array(
                     "return" => "status"
                   ),
                   "token" => $token
               );

              $data_string = json_encode($data);

              $httpAdapter = $this->curlFactory->create();
              $httpAdapter->write(\Zend_Http_Client::POST, $endpoint, '1.1', ["Content-Type:application/json"],$data_string);
              $result = $httpAdapter->read();
              $body = \Zend_Http_Response::extractBody($result);

              $array_result = $this->jsonHelper->jsonDecode($body);

               $log_query = json_encode($data);
               $logger->info('query:' . $log_query);
               $logger->info('response:' . $body);

               $action = $array_result['result']['action'];
               $detail = $array_result['result']['detail'];
               $timestamp = $array_result['meta']['timestamp'];
               $confirmation = $array_result['meta']['confirmation'];

               $this->setDialerAction($orderid, $action, $detail);

               $order = $this->orderRepository->get($orderid);
               $order->setVeratadDialer($action);
               $order->save();
           }

        }
