<?php

				namespace Veratad\AgeVerification\Model\ResourceModel;
				class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
				{
				/**
				* Define main table
				*/
				protected function _construct()
				{
				$this->_init('veratad_history', 'id');   //here id is the primary key of custom table
				}
				}
