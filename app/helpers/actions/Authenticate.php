<?php
	class Helper_Authenticate extends Zend_Controller_Action_Helper_Abstract
	{
		/**
		 * public authenticate - Authenticates a given user credentials.
		 * @param $params - An array containing data to be used in authentication.
		 * @return Zend_Auth_Result - Returns if the result as a Zend_Auth_Result.
		 */
		public function authenticate( $params )
		{
			$tableName = $params['tableName'];
			$identityColumn = $params['identityColumn'];
			$credentialColumn = $params['credentialColumn'];
			$credentialTreatment = $params['credentialTreatment'];
			$columnsToLeaveOutArr = $params['columnsToLeaveOutArr'];
			
			$identity = $params['identity'];
			$credential = $params['credential'];
			
			$sessionNamespace = $params['sessionNamespace'];
				
			$zendAuth = Zend_Auth::getInstance();
			$zendAuth->setStorage(new Zend_Auth_Storage_Session($sessionNamespace));

			// get the db adapter
			$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
			$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

			// set our authAdapter details
			$authAdapter->setTableName($tableName)
						->setIdentityColumn($identityColumn)
						->setCredentialColumn($credentialColumn)
						->setCredentialTreatment($credentialTreatment);
		
			// give the adapter the submitted data
			$authAdapter->setIdentity($identity)
						->setCredential($credential);
					
			// authenticate the submitted data
			$authResult = $zendAuth->authenticate($authAdapter);
			
			// Zend_Debug::dump($result);
			// die();
			
			if( $authResult->isValid() )
			{
				// get all the info about this user from the db
				// ommit the pwd, as we don't need it
				$userDetails = $authAdapter->getResultRowObject(null, $columnsToLeaveOutArr);
				$zendAuth->getStorage()->write($userDetails);
			}
						
			return $authResult;
		}
		/**
		 * public getIdentity - Will return the identity stored in session
		 * @return mixed|null - The identity object
		 */
		public function getIdentity($sessionNamespace)
		{
			$zendAuth = Zend_Auth::getInstance();
			$zendAuth->setStorage(new Zend_Auth_Storage_Session($sessionNamespace));

			return $zendAuth->getIdentity();
		}
		/**
		 * public isAuthenticated - Will let us know if we've authenticated a user.
		 * @return boolean - Whether or not a user has been authentiaced previously within app.
		 */
		public function isAuthenticated($sessionNamespace)
		{
			$zendAuth = Zend_Auth::getInstance();
			$zendAuth->setStorage(new Zend_Auth_Storage_Session($sessionNamespace));
			// Zend_Debug::dump($zendAuth);
			// die();
			return $zendAuth->hasIdentity();
		}
		/**
		 * public logout - Will clear the identity of current session
		 * @param $sessionNamespace - The Session Namespace to clear.
		 */
		public function logout($sessionNamespace)
		{
			$zendAuth = Zend_Auth::getInstance();
			$zendAuth->setStorage(new Zend_Auth_Storage_Session($sessionNamespace));
			$zendAuth->clearIdentity();
		}
	}
?>