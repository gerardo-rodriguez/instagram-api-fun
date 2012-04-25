<?php

	/**
	* Helper_Mail is an action helper used to send emails
	*/
	class Helper_Mail extends Zend_Controller_Action_Helper_Abstract
	{
		
		public function sendMessage( $params ) 
		{
			// create mail object
			$mail = new Zend_Mail('utf-8');

			// configure base stuff
			$mail->setSubject( $params['email']['subject'] )
				->setFrom(
					$params['sender']['email'], 
					$params['sender']['name']
				)
				->addTo(
					$params['recipient']['email'], 
					$params['recipient']['name']
				)
				->setBodyHtml( $params['email']['body'] )
				->send();
		}
	}
?>