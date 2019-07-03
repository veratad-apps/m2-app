<?php
namespace Veratad\AgeVerification\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPlugin
{
    protected $logger;
    private $scopeConfig;

    public function __construct(
      \Psr\Log\LoggerInterface $logger,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterProcess(LayoutProcessor $subject, array $jsLayout)
    {

      $dob_visible = $this->scopeConfig->getValue('settings/agematch/dobvisible', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $dob_required = $this->scopeConfig->getValue('settings/agematch/dobrequired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

      if($dob_visible){
        $visible = true;
      }else{
        $visible = false;
      }

      if($dob_required){
        $required = true;
      }else{
        $required = false;
      }

        $customAttributeCode = 'veratad_dob';

        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'tooltip' => [
                    'description' => 'Please enter your accurate Date of Birth. We are going to verify your age.',
                ],
            ],

            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => 'Date of Birth',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                'required-entry' => $required
            ],
            'options' => [],
            'placeholder' => 'YYYYMMDD',
            'filterBy' => null,
            'customEntry' => null,
            'visible' => $visible,
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

        return $jsLayout;
    }
}
