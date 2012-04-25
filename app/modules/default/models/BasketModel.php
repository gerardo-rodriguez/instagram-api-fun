<?php
	Class BasketModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "basket";
		
		public function fetchBasketItemsData($basketID)
		{
			$select = $this->select(false);
			$select->setIntegrityCheck(false);
			$select->from($this, array())
					->join('basket_item', 'basket_item.basket_id = basket.id', array('basket_item_id' => 'id','quantity','total_item_price'))
					->join('item', 'basket_item.item_id = item.id', array(
						'item_id' => 'id','collection_id','photo_reference','stock_number','description','notes','unit_price'
					))
					->where('basket.id = ?', $basketID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>