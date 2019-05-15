<?php
        namespace Veratad\AgeVerification\Block\Adminhtml\Customer\View;

        class Custom extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View
        {


            public function getTabLabel()
            {
                return __('Age Verification');
            }

            public function getTabTitle()
            {
                return __('Age Verification');
            }

        }
