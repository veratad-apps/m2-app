<?php

				namespace Veratad\AgeVerification\Model\ResourceModel;

				class VeratadAccount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
				{
				/**
				* Define main table
				*/
				protected function _construct()
				{
				$this->_init('veratad_account', 'id');
				}
				}
