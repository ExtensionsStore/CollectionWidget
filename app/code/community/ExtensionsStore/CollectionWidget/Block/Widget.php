<?php

/**
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */
 
class ExtensionsStore_CollectionWidget_Block_Widget
	extends Mage_Catalog_Block_Product_List 
	implements Mage_Widget_Block_Interface
{
	protected $_template = 'extensions_store/collectionwidget/list.phtml';
	
	/**
	 * Unset toolbar if collection page size is set
	 * 
	 * {@inheritDoc}
	 * @see Mage_Catalog_Block_Product_List::_beforeToHtml()
	 */
	protected function _beforeToHtml(){
	
		if ($this->getPageSize()){
			$this->_defaultToolbarBlock = 'core/template';
		}
		
		return parent::_beforeToHtml();
	}
	
	/**
	 * Get widget collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			
			$storeId = Mage::app()->getStore()->getId();
			//product links
			$currentProduct = Mage::registry('current_product');
			$productLinksType = $this->getProductLinks();
			//recently viewed
			$recentlyViewed = $this->getRecentlyViewed();
				
			if ($currentProduct && $currentProduct->getId() && $productLinksType){
				switch ($productLinksType){
					case Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED :
					case 'related' :
						$this->_productCollection = $currentProduct->getRelatedProductCollection();
						break;
					case Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL :
					case 'up_sell' :
						$this->_productCollection = $currentProduct->getUpSellProductCollection();
						break;
					case Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL :
					case 'cross_sell' :
						$this->_productCollection = $currentProduct->getCrossSellProductCollection();
						break;
					default :
						$this->_productCollection = Mage::getModel('catalog/product')->getCollection();
						break;
				}
			} else if ($recentlyViewed) {
				$this->_productCollection = Mage::getBlockSingleton('reports/product_viewed')->getItemsCollection();;
			} else {
				$this->_productCollection = Mage::getModel('catalog/product')->getCollection();
			}
				
			//select all attributes
			$this->_productCollection->addAttributeToSelect('*');
			
			//standard filters
			$this->_productCollection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_productCollection);
			$this->_productCollection->addAttributeToFilter(array(
					array('attribute'=>'visibility', 'in'=> array(
							Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 
							Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH, 
							Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
			));
		
			//filter by category
			if ($this->getCategoryId()){
				$category = Mage::getModel('catalog/category')->load($this->getCategoryId());
				$this->_productCollection->addCategoryFilter($category);
			}
				
			//filter by attribute
			if ($this->getAttributeFilters()){
				
				$attributeFilters = json_decode(base64_decode($this->getAttributeFilters()), true);
				
				if (is_array($attributeFilters) && count($attributeFilters)>0){
					foreach ($attributeFilters as $attributeFilterAr){
						
						$attributeFilter = new Varien_Object($attributeFilterAr);
							
						$attributeCode = $attributeFilter->getAttributeCode();
						$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);
						$attributeOperator = $attributeFilter->getAttributeOperator();
						$attributeValue = ($attributeFilter->getAttributeOperator() == 'like' || $attributeFilter->getAttributeOperator() == 'nlike') ? '%'.$attributeFilter->getAttributeValue().'%' : $attributeFilter->getAttributeValue();
							
						if ($attributeModel->usesSource()){
								
							$filter = array(
									array('attribute' => $attributeCode, $attributeOperator => $attributeValue),
									array('attribute' => $attributeCode.'_value', $attributeOperator => $attributeValue),//flat tables
							);
							$this->_productCollection->addAttributeToFilter($filter);
								
						} else {
							$this->_productCollection->addAttributeToFilter($attributeCode, array($attributeOperator => $attributeValue));
						}
					}					
				}

			}
			
			//filter by new
			if ($this->getNewsFromDate()){
				$todayStartOfDayDate  = $this->getNewsFromDate(). ' 00:00:00';
					
				$todayEndOfDayDate  = ($this->getNewsToDate()) ? $this->getNewsToDate().' 23:59:59' :  Mage::app()->getLocale()->date()
				->setTime('23:59:59')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
					
				$this->_productCollection->addAttributeToFilter('news_from_date', array('or'=> array(
						0 => array('date' => true, 'to' => $todayEndOfDayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
				), 'left')
				->addAttributeToFilter('news_to_date', array('or'=> array(
						0 => array('date' => true, 'from' => $todayStartOfDayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
				), 'left')
				->addAttributeToFilter(
						array(
								array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
								array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
						)
				);
			}
						
			//sort by bestsellers
			if ($this->getBestsellers()){
				$fromDate = ($this->getBestsellersFromDate()) ? $this->getBestsellersFromDate() : date('Y-m-d', strtotime('-1 MONTH'));
				$toDate = date('Y-m-d');
				$linkTable = $this->_productCollection->getResource()->getTable('catalog/product_super_link');
				$bestsellersTable = $this->_productCollection->getResource()->getTable('sales/bestsellers_aggregated_monthly');
				 $this->_productCollection->getSelect()->columns(array(
				 'qty_ordered' => new Zend_Db_Expr("(SELECT SUM(qty_ordered) FROM {$linkTable} l INNER JOIN {$bestsellersTable} a ON l.product_id = a.product_id WHERE l.parent_id = e.entity_id AND a.store_id = '{$storeId}' AND  a.period BETWEEN '{$fromDate}' AND '{$toDate}')"
				 )));
								
				$orderDirection = ($this->getOrderDirection()) ? $this->getOrderDirection() : 'DESC';
				$this->_productCollection->getSelect()->order("qty_ordered $orderDirection");
				
			} else {
				$attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
				$attributeCodes = $attributeCollection->getColumnValues('attribute_code');
				//sort by attribute
				if ($this->getOrder() && in_array(trim($this->getOrder()), $attributeCodes)){
					$order = trim($this->getOrder());
					$orderDirection = ($this->getOrderDirection()) ? $this->getOrderDirection() : 'ASC';
					$this->_productCollection->addAttributeToSort($order, $orderDirection);
				//sort by position
				} else if ($this->getCategoryId()){
					$orderDirection = ($this->getOrderDirection()) ? $this->getOrderDirection() : 'ASC';
					$this->_productCollection->setOrder('position = 0');
					$this->_productCollection->setOrder('position', $orderDirection);
					$this->_productCollection->getSelect()->order(new Zend_Db_Expr('position = 0, position '. $orderDirection));
				}
			}
			
			//collection limit
			if ($this->getPageSize()){
				$this->_productCollection->setPageSize($this->getPageSize());
			} else {
				$limit = Mage::getSingleton('catalog/session')->getLimitPage();
				$limit = ($limit) ? $limit : Mage::getStoreConfig('catalog/frontend/grid_per_page', Mage::app()->getStore()->getId());
				$this->_productCollection->setPageSize($limit);
			}
			
			//lock collection modification (i.e. toolbar)
			$this->_productCollection->load();
			
			//$sql = (string)$this->_productCollection->getSelect();
		}
			
		return $this->_productCollection;
	}	
}
