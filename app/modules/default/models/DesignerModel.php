<?php
	Class DesignerModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "designer";
		
		/**
		 * public fetchDesignerFormPopulateData
		 */
		public function fetchDesignerFormPopulateData($designerID)
		{
			// Zend_Debug::dump($designerID);
			// our select
			$select = $this->select(false); // set to false to get specific data using ->from() and ->where()
			$select->from($this, array(
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
										'tax_document_filename'
								))
					->where('designer.id = ?', $designerID);
					
			// die($select->__toString());
			//Select from table
			return $this->fetchAll($select);
		}
	}
?>