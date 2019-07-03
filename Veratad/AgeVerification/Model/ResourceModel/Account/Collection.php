<?php

    namespace Veratad\AgeVerification\Model\ResourceModel\Account;

    class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
    	protected $_idFieldName = 'veratad_id';
    	protected $_eventPrefix = 'veratad_account_collection';
    	protected $_eventObject = 'account_collection';

    	/**
    	 * Define resource model
    	 *
    	 * @return void
    	 */
    	protected function _construct()
    	{
    		$this->_init('Veratad\AgeVerification\Model\Account', 'Veratad\AgeVerification\Model\ResourceModel\Account');
    	}

    }
