<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_CollectionWidget
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_CollectionWidget_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
}