<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Veratad\AgeVerification\Controller\Adminhtml\Index;

class Custom extends \Magento\Customer\Controller\Adminhtml\Index
{

    public function execute()
    {

        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }

}
