<?php
	Class AdminModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "admin";
		
		/**
		 * fetchCollectionItems - Fetches the items for the collection.
		 */
		public function fetchAdminDetails()
		{
			// our select
			$select = $this->select(true);
			$select->from($this, array(
										'email', 
										'first_name',
										'last_name'
								))
					->where('admin.id = ?', 1);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>