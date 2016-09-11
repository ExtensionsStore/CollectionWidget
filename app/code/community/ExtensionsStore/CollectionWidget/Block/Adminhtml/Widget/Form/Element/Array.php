<?php

/**
 * Adminhtml widget form array element
 *
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */
class ExtensionsStore_CollectionWidget_Block_Adminhtml_Widget_Form_Element_Array 
	extends Varien_Data_Form_Element_Abstract 
{
    
	/**
	 * Render out the system config form element as a widget form element
	 * 
	 * @return string
	 */
    public function getElementHtml()
    {
    	$form = $this->getForm();
    	$parent = $form->getParent();
    	$layout = $parent->getLayout();
    	$arrayBlock = $layout->createBlock('collectionwidget/adminhtml_system_config_form_field_array', $this->getName(), $this->getData());
    	
    	$arrayBlock->setElement($this);
    	
    	$html = $arrayBlock->toHtml();
    	$html.= $this->getAfterElementHtml();
    	$htmlId = $arrayBlock->getHtmlId();
    	
    	$rows = $arrayBlock->getArrayRows();
    	$columns = $arrayBlock->getColumns();
    	$selectedValues = json_decode(base64_decode($this->getValue()), true);
    	 
    	//select options
    	if (is_array($rows) && count($rows)>0 && is_array($columns) && count($columns)>0){
    		
    		$html.= '<script>
    			';
    		foreach ($rows as $i=>$row){
    			foreach ($columns as $columnName=>$column){
    				if ($column['type']=='select'){
    					$html.= '$$("select[name=\"attribute['.$i.']['.$columnName.']\"]").each(function(select, selectIndex){
			    			$(select).select("option").each(function(option, optionIndex){
			    				if ("'.$selectedValues[$i][$columnName].'" == option.value){
			    					option.selected = true;
			    				}
							});
						});
			    		';    					
    				}
    			}
    		}
    		
    		$html.= '</script>
    		';
    	}
    	 
    	return $html;
    }
}
