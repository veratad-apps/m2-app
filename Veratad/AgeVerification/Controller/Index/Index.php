<?php

        namespace Veratad\AgeVerification\Controller\Index;

        class Index extends \Magento\Framework\App\Action\Action
        {
        	protected $_pageFactory;
          private $scopeConfig;
          protected $messageManager;
          protected $customerSession;

        	public function __construct(
        		\Magento\Framework\App\Action\Context $context,
        		\Magento\Framework\View\Result\PageFactory $pageFactory,
              \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
              \Magento\Framework\Message\ManagerInterface $messageManager,
               \Magento\Customer\Model\SessionFactory $customerSession)
        	{
        		$this->_pageFactory = $pageFactory;
            $this->scopeConfig = $scopeConfig;
            $this->messageManager = $messageManager;
            $this->customerSession = $customerSession;
        		return parent::__construct($context);
        	}

        	public function execute()
        	{
            $resultPage = $this->_pageFactory->create();
            return $resultPage;
        	}
          public function getValue()
          {
            return $this->customerSession->getId(); //Get value from customer session
          }
        }
