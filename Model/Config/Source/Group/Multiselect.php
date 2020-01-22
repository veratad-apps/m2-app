<?php

        namespace Veratad\AgeVerification\Model\Config\Source\Group;

        use \Magento\Customer\Model\ResourceModel\Group\Collection;

        class Multiselect implements \Magento\Framework\Option\ArrayInterface {

        protected $_customerGroup;

        protected $_options;

        public function __construct( Collection $customerGroup ) {
        $this->_customerGroup = $customerGroup;
        }

        public function toOptionArray() {
        if (!$this->_options) {
        $this->_options = $this->_customerGroup->toOptionArray();
        }
        return $this->_options;
        }
        }
