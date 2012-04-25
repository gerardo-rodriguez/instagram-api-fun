<?php
	Class DesignerModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "designer";
		
		/**
		 * fetchPendingDesigners - Will fetch the pending designers.
		 * @param $status - Dictates which designers to fetch, based on the status passed in.
		 * @return Zend_Db_Table_Rowset - Contains the data sets in an array.
		 */
		public function fetchDesigners($status)
		{
			// our select
			$select = $this->select(false);
			$select->from($this, array(
							'id', 
							'email', 
							'business_name',
							'owner_first_name',
							'owner_last_name',
							'address',
							'city',
							'state',
							'zip',
							'phone_number',
							'fax_number',
							'tax_id_delivery',
							'tax_document_filename',
							'create_date',
							'status'
					))
					->where("designer.status = '$status'");
				
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * fetchDesignerData - Will fetch the data for a given designer.
		 * @param $designerID - The id of the designer to fetch
		 * @return Zend_Db_Table_Rowset - Contains the data sets in an array.
		 */
		public function fetchDesignerData($designerID)
		{
			// our select
			$select = $this->select(false)
							->from($this, array(
										'id', 
										'email', 
										'business_name',
										'owner_first_name',
										'owner_last_name',
										'address',
										'city',
										'state',
										'zip',
										'phone_number',
										'fax_number',
										'tax_id_delivery',
										'tax_document_filename',
										'create_date',
										'status'
								))
								->where('designer.id = ?', $designerID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
		/**
		 * updateCollectionArchiveStatus - Updates the designer status
		 * @param $updatedStatus - The status to update the designer to.
		 * @param $designerID - The id of the designer to update.
		 */
		public function updateDesignerStatus( $updatedStatus, $designerID )
		{
			$data = array(
				'status' => $updatedStatus
			);
			$where = $this->getAdapter()->quoteinto('id = ?', $designerID);
		
			$this->update($data,$where);
		}

	}
?>