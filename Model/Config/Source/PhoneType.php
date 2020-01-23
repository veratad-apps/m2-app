<?php


      namespace Veratad\AgeVerification\Model\Config\Source;

      class PhoneType implements \Magento\Framework\Option\ArrayInterface
      {
       public function toOptionArray()
       {
        return [
          ['value' => 'voip', 'label' => __('VOIP')],
          ['value' => 'mobile', 'label' => __('Mobile')],
          ['value' => 'landline', 'label' => __('Landline')]
        ];
       }
      }
