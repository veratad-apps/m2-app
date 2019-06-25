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
              $eligible = $this->helper->checkAttempts($order_id);
              $return = array(
                "action" => $eligible
              );

              if($eligible === "false"){
                $text = $this->scopeConfig->getValue('settings/content/agematch_exceeded_subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->messageManager->getMessages(true);
                $this->messageManager->addError(__($text));
              }

              $json_result = $this->resultJsonFactory->create();
              return $json_result->setData($return);
            }

          }
