<?php
namespace Veratad\AgeVerification\Controller\Dcams;
class Display extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
