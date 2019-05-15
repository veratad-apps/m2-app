<?php
namespace Veratad\AgeVerification\Block;

class Display extends \Magento\Framework\View\Element\Template
{

	protected $_messageManager;
	protected $customerSession;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Framework\Message\ManagerInterface $messageManager,
	\Magento\Customer\Model\Session $customerSession
	)
	{

		 parent::__construct($context);
		 $this->_messageManager = $messageManager;
         $this->customerSession = $customerSession;
	}

  public function error($copy)
  {

        $this->_messageManager->addError($copy);
  }

	public function getCustomerId()
    {

        return $this->customerSession->getCustomer()->getId();
    }

}
