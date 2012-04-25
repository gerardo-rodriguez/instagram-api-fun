<?php
	Class ItemModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "item";
		
		/**
		 * fetchCollectionItemsData - Fetches the item data for given collection
		 */
		public function fetchCollectionItemData( $collectionID )
		{
			// our select
			$select = $this->select(true);
			$select->setIntegrityCheck(false)
					// ->joinLeft('item_image', 'item_image.item_id = item.id', array())
					// ->joinLeft('image', 'item_image.image_id = image.id', array('filename'))
					->where('collection_id = ?', $collectionID);
					
			// die( $select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * fetchSingleItemData - Fetches the item data for given item id
		 */
		public function fetchSingleItemData( $itemID )
		{
			// our select
			$select = $this->select(true);
			$select->setIntegrityCheck(false)
					->where('id = ?', $itemID);
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>