<?php
namespace Veratad\AgeVerification\Model\Query;

class Api
{

    private $scopeConfig;
    protected $helper;
    protected $_veratadHistory;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Veratad\AgeVerification\Helper\Data $helper,
        \Veratad\AgeVerification\Model\HistoryFactory $history
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->_veratadHistory = $history;
    }

    public function veratadCaller($billing, $shipping, $orderid, $customerid)
    {

      $result = false;

      $verification_type = $this->scopeConfig->getValue('settings/general/to_verify', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/caller.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info("verification type = $verification_type");
      if($verification_type === "billing"){
        //billing only post
        $billing_verified = $this->helper->veratadPost($billing, $orderid, $customerid, "billing");
        if($billing_verified){
          $result = true;
        }
      }elseif($verification_type === "shipping"){
        //shipping only post
        $shipping_verified = $this->helper->veratadPost($shipping, $orderid, $customerid, "shipping");
        if($shipping_verified){
          $result = true;
        }

      }elseif($verification_type === "both"){
        //both post
        $billing_verified = $this->helper->veratadPost($billing, $orderid, $customerid, "billing");
        $shipping_verified = $this->helper->veratadPost($shipping, $orderid, $customerid, "shipping");

        if($billing_verified && $shipping_verified){
          $result = true;
        }

      }elseif($verification_type === "auto_detect"){
        //name check then decide what to post

        $nameMatch = $this->helper->nameDetection($billing, $shipping);
        if($nameMatch){
          $billing_verified = $this->helper->veratadPost($billing, $orderid, $customerid, "billing");
          if($billing_verified){
            $result = true;
          }
        }else{
          $billing_verified = $this->helper->veratadPost($billing, $orderid, $customerid, "billing");
          $shipping_verified = $this->helper->veratadPost($shipping, $orderid, $customerid, "shipping");
          if($billing_verified && $shipping_verified){
            $result = true;
          }
        }
      }

      return $result;

    }
}
