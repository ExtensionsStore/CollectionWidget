<?php

/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_CollectionWidget_Block_Adminhtml_System_Config_Form_Field_Array
	extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract 
{
	protected $_template = 'extensions_store/collectionwidget/system/config/form/field/array.phtml';
	
	/**
	 * Add array columns
	 * 
	 */
    public function _prepareToRender()
    {
    	$this->addColumn('attribute_code', array(
            'label' => 'Attribute Code',
        	'type' => 'text',
            'style' => 'width:120px',
    		'class' => 'widget-array-input',
        ));
        
        $operators = array(
        	array('value'=>'eq', 'label'=>$this->__('= Equals')),
        	array('value'=>'neq', 'label'=>$this->__('!= Not Equals')),
        	array('value'=>'like', 'label'=>$this->__('LIKE')),
        	array('value'=>'nlike', 'label'=>$this->__('NOT LIKE')),
        	array('value'=>'in', 'label'=>$this->__('IN')),
        	array('value'=>'nin', 'label'=>$this->__('NOT IN')),
        	array('value'=>'is', 'label'=>$this->__('IS')),
        	array('value'=>'notnull', 'label'=>$this->__('NOT NULL')),
        	array('value'=>'null', 'label'=>$this->__('IS NULL')),
        	array('value'=>'moreq', 'label'=>$this->__('>= More Equal')),
        	array('value'=>'gt', 'label'=> $this->__('> Greater than')),
        	array('value'=>'lt', 'label'=> $this->__('< Less than')),
        	array('value'=>'gteq', 'label'=> $this->__('>= Greater than or equal to')),
        	array('value'=>'lteq', 'label'=> $this->__('<= Less than or equal to')),
        	array('value'=>'from', 'label'=>$this->__('From >=')),
        	array('value'=>'to', 'label'=>$this->__('To <=')),
        );
        
        $this->addColumn('attribute_operator', array(
            'label' => 'Attribute Operator',
        	'type' => 'select',
        	'values' => $operators,
        	'style' => 'width:120px',
    		'class' => 'widget-array-input',
        	'renderer' => '',
        ));
        $this->addColumn('attribute_value', array(
        	'label' => 'Attribute Value',
        	'type' => 'text',
        	'style' => 'width:120px',
    		'class' => 'widget-array-input',
        ));
    	$this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add filter');
    }
    
    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
    	$this->_columns[$name] = array(
    			'label'     => empty($params['label']) ? 'Column' : $params['label'],
    			'size'      => empty($params['size'])  ? false    : $params['size'],
    			'style'     => empty($params['style'])  ? null    : $params['style'],
    			'class'     => empty($params['class'])  ? null    : $params['class'],
    			'type'     	=> empty($params['type'])  ? 'text'    : $params['type'],
    			'values'    => empty($params['values'])  ? null    : $params['values'],
    			'renderer'  => false,
    	);
    	if ((!empty($params['renderer'])) && ($params['renderer'] instanceof Mage_Core_Block_Abstract)) {
    		$this->_columns[$name]['renderer'] = $params['renderer'];
    	}
    }
    
    public function getColumns(){
    	
    	return $this->_columns;
    }
    
    
    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
    	if (empty($this->_columns[$columnName])) {
    		throw new Exception('Wrong column name specified.');
    	}
    	$column     = $this->_columns[$columnName];
    	$type		= $column['type'];
    	$optionArray= $column['values'];
    	$value 		= $this->getValue();
    	$inputName  = 'attribute[#{_id}][' . $columnName . ']';
    
    	if ($column['renderer']) {
    		return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
    		->toHtml();
    	}
    
    	if ($type=='select' && is_array($optionArray) && count($optionArray)>0){
    
    		$cell = '<select name="'.$inputName.'" id="input_#{_id}_' . $columnName . '" class="'.(isset($column['class']) ? $column['class'] : 'select').'" '.(isset($column['style']) ? ' style="'.$column['style'] . '"' : '').'  >';
    
    		foreach ($optionArray as $i=>$option){
    
    			$cell .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
    		}
    
    		$cell .= '</select>';
    
    	} else {
    
    		$cell = '<input type="'.$type.'" id="input_#{_id}_' . $columnName . '" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
    				($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
    				(isset($column['class']) ? $column['class'] : 'input-text') . '"'.
    				(isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '/>';
    	}
    	 
    	return $cell;
    }
    
    /**
     * Decode base64/json enconded row data
     * {@inheritDoc}
     * @see Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract::getArrayRows()
     */
    public function getArrayRows()
    {
    	if (null !== $this->_arrayRowsCache) {
    		return $this->_arrayRowsCache;
    	}
    	$result = array();
    	/** @var Varien_Data_Form_Element_Abstract */
    	$element = $this->getElement();
    	if ($element->getValue()) {
    		$value = json_decode(base64_decode($element->getValue()), true);
    		if (is_array($value)) {
    			foreach ($value as $rowId => $row) {
    				foreach ($row as $key => $value) {
    					$row[$key] = $this->escapeHtml($value);
    				}
    				$row['_id'] = $rowId;
    				$result[$rowId] = new Varien_Object($row);
    				$this->_prepareArrayRow($result[$rowId]);
    			}
    		}
    	}
    	$this->_arrayRowsCache = $result;
    	return $this->_arrayRowsCache;
    }
    
}
