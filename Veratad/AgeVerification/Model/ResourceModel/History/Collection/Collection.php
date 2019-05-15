<?php

namespace Veratad\AgeVerification\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
    $this->_init(
        'Veratad\AgeVerification\Model\History',
        'Veratad\AgeVerification\Model\ResourceModel\History'
    );

    }
}
