<?php

/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_CollectionWidget_Block_Adminhtml_Attribute_Filter extends Mage_Widget_Block_Adminhtml_Widget_Options
{
	/**
	 * Prepare chooser element HTML
	 *
	 * @param Varien_Data_Form_Element_Abstract $element Form Element
	 * @return Varien_Data_Form_Element_Abstract
	 */
	public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		
		//$element->setData('after_element_html', $addAttributeFilterButton->toHtml());
		return $element;
	}
}
