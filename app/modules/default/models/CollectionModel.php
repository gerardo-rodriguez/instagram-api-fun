<?php
	Class CollectionModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "collection";
		
		/**
		 * fetchSingleCollectionData - Fetches the data for the collection.
		 */
		public function fetchSingleCollection( $collectionID )
		{
			// our select
			$select = $this->select(true);
			$select->where('collection.id = ?', $collectionID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * fetchActiveCollections - Will fetch the active collections.
		 */
		public function fetchActiveCollections()
		{
			// our select
			$select = $this->select(true);
			$select->where('is_archived = 0');
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>