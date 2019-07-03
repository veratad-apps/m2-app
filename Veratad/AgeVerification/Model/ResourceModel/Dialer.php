<?php
		namespace Veratad\AgeVerification\Model\ResourceModel;


		class Dialer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
		{

			public function __construct(
				\Magento\Framework\Model\ResourceModel\Db\Context $context
			)
			{
				parent::__construct($context);
			}

			protected function _construct()
			{
				$this->_init('veratad_dialer', 'veratad_id');
			}

		}
