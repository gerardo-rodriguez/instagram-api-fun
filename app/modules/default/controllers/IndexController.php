<?php

	class IndexController extends Zend_Controller_Action
	{
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');
			//$this->Model = new Model();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('default');
			
			//Access to helper
			//$helper = $this->_helper->HelperName;
		}
		
		public function indexAction()
		{
			$this->_helper->getHelper('Redirector')->gotoSimple('index','designer');
		}
	}
?>