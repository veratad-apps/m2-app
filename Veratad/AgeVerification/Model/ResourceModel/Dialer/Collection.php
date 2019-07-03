<?php

    namespace Veratad\AgeVerification\Model\ResourceModel\Dialer;

    class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
    	protected $_idFieldName = 'veratad_id';
    	protected $_eventPrefix = 'veratad_dialer_collection';
    	protected $_eventObject = 'dialer_collection';

    	/**
    	 * Define resource model
    	 *
    	 * @return void
    	 */
    	protected function _construct()
    	{
    		$this->_init('Veratad\AgeVerification\Model\Dialer', 'Veratad\AgeVerification\Model\ResourceModel\Dialer');
    	}

    }
