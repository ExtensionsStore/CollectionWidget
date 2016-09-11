<?php

/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */


class ExtensionsStore_CollectionWidget_Model_Adminhtml_Observer
{
	/**
	 * 
	 * @param Varien_Event_Observer $observer
	 * @return Varien_Event_Observer
	 */
	public function addFormElementTypeArray(Varien_Event_Observer $observer) {

		$block = $observer->getBlock();
		
		if ($block->getNameInLayout()=='wysiwyg_widget.options'){
			$fieldset = $block->getMainFieldSet();
			$fieldset->addType('array', 'ExtensionsStore_CollectionWidget_Block_Adminhtml_Widget_Form_Element_Array');
		}
		
		return $observer;
	}
	
	
}