<?php
	Class BasketItemModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "basket_item";
		
		public function fetchAllBasketItems($params)
		{
			$basketID = $params['basketID'];
			$itemID = $params['itemID'];

			$select = $this->select()
							->where('basket_id = ?', $basketID)
							->where('item_id = ?', $itemID);
			// die($select->__toString());
			return $this->fetchAll($select);
		}
	}
?>