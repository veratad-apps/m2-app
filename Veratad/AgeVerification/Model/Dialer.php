<?php

			namespace Veratad\AgeVerification\Model;

			class Dialer extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
			{
				const CACHE_TAG = 'veratad_dialer';

				protected $_cacheTag = 'veratad_dialer';

				protected $_eventPrefix = 'veratad_dialer';

				protected function _construct()
				{
					$this->_init('Veratad\AgeVerification\Model\ResourceModel\Dialer');
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
