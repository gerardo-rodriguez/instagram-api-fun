<?php
	Class ItemModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "item";
		
		/**
		 * fetchCollectionItems - Fetches the items for the collection.
		 */
		public function fetchCollectionItems( $collectionID )
		{
			// our select
			$select = $this->select(true);
			$select->where('collection_id = ?', $collectionID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>