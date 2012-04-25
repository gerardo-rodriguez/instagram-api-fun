<?php
	//-------------------------------------------------
	// Imports
	//-------------------------------------------------
	require_once( '../app/modules/admin/models/AdminModel.php' );
	require_once( '../app/forms/LoginForm.php' );

	/**
	 * Admin Index Controller
	 *
	 * The controller for the Admin Index view.
	 *  
	 * @author Gerardo Rodriguez
	 * @created 12/20/2011
	 */
	class Admin_IndexController extends Zend_Controller_Action
	{
		//-------------------------------------------------
		// Properties
		//-------------------------------------------------
		private $adminModel;
		private $loginForm;
		private $authHelper;
		private $redirector;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');
			//$this->Model = new Model();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('admin_logged_out');
			
			//Access to helpers
			$this->authHelper = $this->_helper->getHelper('Authenticate');
			$this->redirector = $this->_helper->getHelper('Redirector');
		}
		//-------------------------------------------------
		// Public Methods
		//-------------------------------------------------
		public function indexAction()
		{
			// redirect if logged in
			if( $this->authHelper->isAuthenticated('Admin_Session_Namespace') ) $this->redirector->gotoSimple('index','collections','admin');
			
			// create/show the login form
			$this->loginForm = new LoginForm();
			$this->view->loginForm = $this->loginForm;

			$this->handleAdminLogin();
		}
		/**
		 * public logoutAction - Handles the logout action
		 */
		public function logoutAction()
		{
			// logout of the current session namespace
			$this->authHelper->logout('Admin_Session_Namespace');
			$this->redirector->gotoSimple('index','index','admin');
		}
		//-------------------------------------------------
		// Private Methods
		//-------------------------------------------------
		/**
		 * private handleAdminLogin - Will handle the login POST
		 */
		private function handleAdminLogin()
		{
			$request = $this->getRequest();
			$form = $this->loginForm;

			// if form submitted
			if( $request->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				// if valid
				if( $form->isValid($request->getPost()) ) {
					
					// get the info from the form
					$email = $form->getValue('email');
					$password = $form->getValue('password');
					
					// setup our params for the authentication
					$authParams = array(
						'tableName' => 'admin',
						'identityColumn' => 'email',
						'credentialColumn' => 'hash',
						'credentialTreatment' => 'sha1(CONCAT(salt,md5(?)))',
						'columnsToLeaveOutArr' => array('hash','salt'),
						'identity' => $email,
						'credential' => $password,
						'sessionNamespace' => 'Admin_Session_Namespace'
					);
					
					$result = $this->authHelper->authenticate( $authParams );
										
					switch ($result->getCode()) {
 
						case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
					        /** do stuff for nonexistent identity **/
 
						case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
					        /** do stuff for invalid credential **/
							array_push($customErrorMessages, 'Wrong email or password provided. Please try again.');
					        break;
 
						case Zend_Auth_Result::SUCCESS:
					        /** do stuff for successful authentication **/
							$this->redirector->gotoSimple('index','collections','admin');
					        break;
 
						default:
					        /** do stuff for other failure **/
							array_push($customErrorMessages, 'An error occured. Please try again.');
					        break;
					}
				}

				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
	}
	
?>