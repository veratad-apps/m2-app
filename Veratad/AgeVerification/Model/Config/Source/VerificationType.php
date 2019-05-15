<?php


      namespace Veratad\AgeVerification\Model\Config\Source;

      class VerificationType implements \Magento\Framework\Option\ArrayInterface
      {
       public function toOptionArray()
       {
        return [
          ['value' => 'billing', 'label' => __('Billing Only')],
          ['value' => 'shipping', 'label' => __('Shipping Only')],
          ['value' => 'both', 'label' => __('Both')],
          ['value' => 'auto_detect', 'label' => __('Auto Detect Name Difference')]
        ];
       }
      }
