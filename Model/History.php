<?php

			namespace Veratad\AgeVerification\Model;

			class History extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
			{
				const CACHE_TAG = 'veratad_history';

				protected $_cacheTag = 'veratad_history';

				protected $_eventPrefix = 'veratad_history';

				protected function _construct()
				{
					$this->_init('Veratad\AgeVerification\Model\ResourceModel\History');
				}

				public function getIdentities()
				{
					return [self::CACHE_TAG . '_' . $this->getId()];
				}

				public function getDefaultValues()
				{
					$values = [];

					return $values;
				}
			}
