<?php

			namespace Veratad\AgeVerification\Model;

			class Account extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
			{
				const CACHE_TAG = 'veratad_account';

				protected $_cacheTag = 'veratad_account';

				protected $_eventPrefix = 'veratad_account';

				protected function _construct()
				{
					$this->_init('Veratad\AgeVerification\Model\ResourceModel\Account');
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
