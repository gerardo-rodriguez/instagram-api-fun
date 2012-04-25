<?php
	class ErrorController extends Zend_Controller_Action
	{
		
		public function init() {
			//Set Default layout
			//$this->_helper->layout->setLayout('error');		
		}
		
		public function errorAction()
		{
			die(var_dump($error));
			$error = $this->_getParam('error_handler');
		    switch($error->type) {
				case 'image-error':
					$this->_forward('image-error');
					break;
			
				case 'EXCEPTION_OTHER':
					switch($error->exception->getMessage()) {

					case 'NOT_AUTHORIZED':
		        		$this->_forward('not-authorized');
		        		break;
		        	}
		        	break;
		        		 
		      	default:
		        	break;
		    }
		}
	 
		public function notAuthorizedAction() {
		
		}
		
		public function imageErrorAction() {
			
		}
	}
?>