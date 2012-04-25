<?php
	require_once( '../app/modules/admin/models/ItemModel.php' );

	Class CollectionModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "collection";
		
		/**
		 * fetchSingleCollectionData - Fetches the data for the collection.
		 */
		public function fetchSingleCollectionData( $collectionID )
		{
			// our select
			$select = $this->select(true);
			$select->setIntegrityCheck(false)
					// ->from($this, array('first_name','last_name','email','create_date','is_complete'));
					// ->distinct()
					// ->joinLeft('collection_image', 'collection_image.collection_id = collection.id', array())
					// ->joinLeft('image', 'collection_image.image_id = image.id', array('filename'))
					->where('collection.id = ?', $collectionID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * updateCollectionArchiveStatus - Updates the archive/unarchive status for a collection
		 */
		public function updateCollectionArchiveStatus( $archiveAction, $collectionID )
		{
			
			if( $collectionID )
			{
				$archiveStatus;
				
				switch( $archiveAction )
				{
					case 'archive':
						$archiveStatus = '1';
						break;
						
					case 'unarchive':
						$archiveStatus = '0';
						break;
				}

				$data = array(
					'archive_date' => date('Y-m-d'),
					'is_archived' => $archiveStatus
				);
				$where = $this->getAdapter()->quoteinto('id = ?', $collectionID);
				
				$this->update($data,$where);
			}
		}
		/**
		 * fetchActiveCollections - Will fetch the active collections.
		 */
		public function fetchActiveCollections()
		{
			// our select
			$select = $this->select(true);
			$select->setIntegrityCheck(false)
					->where('is_archived = 0');
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * fetchArchivedCollections - Will fetch the archived collections.
		 */
		public function fetchArchivedCollections()
		{
			// our select
			$select = $this->select(true);
			$select->setIntegrityCheck(false)
					->where('is_archived = 1');
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * Will delete a given collection.
		 */
		public function deleteCollection($collectionID)
		{
			$itemModel = new ItemModel();
			
			$collectionWhere = $this->getAdapter()->quoteInto('id = ?', $collectionID);
			$collectionRowsDeleted = $this->delete($collectionWhere);
			
			$itemWhere = $itemModel->getAdapter()->quoteInto('collection_id = ?', $collectionID);
			$itemRowsDeleted = $itemModel->delete($itemWhere);
		}
	}
?>