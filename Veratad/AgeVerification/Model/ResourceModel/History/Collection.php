<?php

    namespace Veratad\AgeVerification\Model\ResourceModel\History;

    class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
    	protected $_idFieldName = 'veratad_id';
    	protected $_eventPrefix = 'veratad_history_collection';
    	protected $_eventObject = 'history_collection';

    	/**
    	 * Define resource model
    	 *
    	 * @return void
    	 */
    	protected function _construct()
    	{
    		$this->_init('Veratad\AgeVerification\Model\History', 'Veratad\AgeVerification\Model\ResourceModel\History');
    	}

    }
