<?php

namespace Veratad\AgeVerification\Block\Adminhtml\Menu\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Dialer extends AbstractFieldArray
{
    protected $_typeblockOptions;
    protected $_cmsblockOptions;
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'file',
            [
                'label' => __('Audio File'),
                'size' => '400px',
                'class' => 'required-entry'
            ]
        );

        $this->addColumn(
            'state',
            [
                'label' => __('State'),
                'size' => '400px',
                'class' => 'required-entry'
            ]
        );

        $this->addColumn(
            'time',
            [
                'label' => __('Hour'),
                'size' => '200px',
                'class' => 'required-entry'
            ]
        );


        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Dialer');
    }

    protected function _getGroupTypeRenderer()
    {
        if (!$this->_typeblockOptions) {
            $this->_typeblockOptions = $this->getLayout()->createBlock(
                \Veratad\AgeVerification\Block\Adminhtml\Menu\Config\Backend\TypeOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeblockOptions->setClass('type_group_select');
        }
        return $this->_typeblockOptions;
    }

    protected function _getGroupCmsBlocRenderer()
    {
        if (!$this->_cmsblockOptions) {
            $this->_cmsblockOptions = $this->getLayout()->createBlock(
                \Veratad\AgeVerification\Block\Adminhtml\Menu\Config\Backend\CmsBlockOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_cmsblockOptions->setClass('cmsbloc_group_select');
        }
        return $this->_cmsblockOptions;
    }

}
