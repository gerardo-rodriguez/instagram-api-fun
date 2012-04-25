<?php
	Class AdminModel extends Zend_Db_Table_Abstract
	{
		protected $_name = "admin";
		
/*
		public function fetchCurrentAdminUsers()
		{
			$select = $this->select(false);
			$select->setIntegrityCheck(false)
				->from($this, array('first_name','last_name','email','create_date'));

			return $this->fetchAll($select);
		}
*/
	}
?>