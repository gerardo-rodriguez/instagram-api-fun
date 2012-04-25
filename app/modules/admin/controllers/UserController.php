<?php
	//-------------------------------------------------
	// Imports
	//-------------------------------------------------
	require_once( '../app/modules/admin/models/AdminModel.php' );

	/**
	 * Admin User Controller
	 *
	 * The controller for the Admin User views.
	 *  
	 * @author Gerardo Rodriguez
	 * @created 12/20/2011
	 */
	class Admin_UserController extends Zend_Controller_Action
	{
		private $adminModel;
		private $updateAdminDetailForm;
		private $updateAdminPasswordForm;
		private $redirector;
		private $authHelper;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');

			// if not logged in, redirect to the login form
			$this->authHelper = $this->_helper->getHelper('Authenticate');
			if( !$this->authHelper->isAuthenticated('Admin_Session_Namespace') ) $this->_helper->getHelper('Redirector')->gotoSimple('index','index','admin');

			// our models
			$this->adminModel = new AdminModel();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('admin_logged_in');
			
			//Access to helpers
			$this->redirector = $this->_helper->getHelper('Redirector');

			// Create the forms
			$this->createAdminForms();
		}
		//-------------------------------------------------
		// Public Methods
		//-------------------------------------------------
		public function indexAction()
		{
			$this->redirector->gotoSimple('edit','user','admin');
		}
		
		public function editAction()
		{
			// grab the current auth details in session
			$userDetails = Zend_Auth::getInstance()->getStorage()->read();

			// Let's pass along our data for the view
			$this->view->admin_first_name = $userDetails->first_name;
			$this->view->admin_last_name = $userDetails->last_name;
			$this->view->admin_email = $userDetails->email;
		}
		/**
		 * public editdetailsAction - Handles the 'editdetails' action
		 */
		public function editdetailsAction()
		{
			// grab the identity in session
			$identityObject = $this->authHelper->getIdentity('Admin_Session_Namespace');
			// set up the form data
			$formData = array(
				'first_name' => $identityObject->first_name,
				'last_name' => $identityObject->last_name,
				'email' => $identityObject->email
			);
			// populate the form
			$this->updateAdminDetailForm->populate($formData);
			
			// if form submitted
			if( $this->getRequest()->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				$request = $this->getRequest();
				$form = $this->updateAdminDetailForm;
				
				// if not valid
				if( $form->isValid($request->getPost()) ) {

					// grab the form values
					$newFirstName = $form->getValue('first_name');
					$newLastName = $form->getValue('last_name');
					$newAdminEmail = $form->getValue('email');
					
					// grab the current logged in user data
					$userDetails = Zend_Auth::getInstance()->getStorage()->read();

					$updateData = array();
					
					$updateData['first_name'] = $newFirstName;
					$userDetails->first_name = $newFirstName; // update the data in session for the current user

					$updateData['last_name'] = $newLastName;
					$userDetails->last_name = $newLastName; // update the data in session for the current user

					$updateData['email'] = $newAdminEmail;
					$userDetails->email = $newAdminEmail; // update the data in session for the current user

					$where = $this->adminModel->getAdapter()->quoteInto('id = ?', $userDetails->id);
					$rowUpdated = $this->adminModel->update($updateData, $where);
					
					if( $rowUpdated )
						$this->redirector->gotoSimple('edit','user','admin');
					else 
						array_push($customErrorMessages, "No updates were made as no details were changed.");

				}
				$this->view->customErrorMessages = $customErrorMessages;
				$this->view->customSuccessMessages = $customSuccessMessages;
			}
		}
		/**
		 * editpasswordAction - Handles the edit password action
		 */
		public function editpasswordAction()
		{
			// if form submitted
			if( $this->getRequest()->isPost() )
			{
				$customErrorMessages = array();
				// $customSuccessMessages = array();

				$request = $this->getRequest();
				$form = $this->updateAdminPasswordForm;
				
				// if not valid
				if( $form->isValid($request->getPost()) ) {
					// grab submitted data
					$currentPassword = $form->getValue('password_current');
					$newPassword = $form->getValue('password_new');
					$verifyPassword = $form->getValue('password_verification');
					
					if( $newPassword != $verifyPassword )
					{
						array_push($customErrorMessages, "The New Password and New Password Verification must match.");
					}
					else
					{
						$userDetails = Zend_Auth::getInstance()->getStorage()->read();
						
						// Zend_Debug::dump($userDetails);

						$where = $this->adminModel->getAdapter()->quoteInto('id = ?', $userDetails->id);
						$adminDataArr = $this->adminModel->fetchAll($where)->toArray();
						
						$hashOnFile = $adminDataArr[0]['hash'];
						$saltOnFile = $adminDataArr[0]['salt'];
						
						$currentHash = sha1($saltOnFile.md5($currentPassword));
						
						if( $currentHash != $hashOnFile )
						{
							array_push($customErrorMessages, "Your password does not match the passord on file. Please try again.");
						}
						else
						{
							// let's salt it up!!
							$salt = sha1(md5($newPassword.time()));
							$hash = sha1($salt.md5($newPassword));
						
							$updateData = array(
								'hash' => $hash,
								'salt' => $salt
							);
							$rowUpdated = $this->adminModel->update($updateData, $where);
							
							if( $rowUpdated )
								$this->redirector->gotoSimple('edit','user','admin');
							else
								array_push($customErrorMessages, "Unable to update your password. Please try again.");
						}
						
						// Zend_Debug::dump($adminDataArr);
					}
				}
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		//-------------------------------------------------
		// Private Methods
		//-------------------------------------------------
		/**
		 * private createAdminForms - Creates up our admin user forms
		 */
		private function createAdminForms()
		{
			// create the admin detail form
			$this->updateAdminDetailForm = new Zend_Form();
			$this->updateAdminDetailForm
				->setAttribs(array(
					'id'=>'updateAdminDetailForm'
				))
				->setAction('/admin/user/editdetails')
				->setMethod('post');
			
			// let's create our form elements/inputs
			$this->updateAdminDetailForm->addElements(array(
				array( // our first name input field
					'text', 'first_name', array(
						'label' => 'First Name: ',
						'required' => false,
						// 'autofocus' => 'autofocus',
						'validators' => array(
							// array(
							// 	'NotEmpty', true, array(
							// 		'messages' => array(
							// 			Zend_Validate_NotEmpty::IS_EMPTY => 'A first name is required.'
							// 		)
							// 	)
							// ),
/*
							array(
								'Alpha', true, array(
									'allowWhiteSpace' => true
								)
							)
*/
						)
					)
				),
				array( // our last name text input field
					'text', 'last_name', array(
						'label' => 'Last Name: ',
						'required' => false,
						'validators' => array(
							// array(
							// 	'NotEmpty', true, array(
							// 		'messages' => array(
							// 			Zend_Validate_NotEmpty::IS_EMPTY => 'A last name is required.'
							// 		)
							// 	)
							// ),
/*
							array(
								'Alpha', true, array(
									'allowWhiteSpace' => true
								)
							)
*/
						)
					)
				),
				array( // our email text input field
					'text', 'email', array(
						'label' => 'Email: ',
						'required' => false,
						'validators' => array(
						// 	array(
						// 		'NotEmpty', true, array(
						// 			'messages' => array(
						// 				Zend_Validate_NotEmpty::IS_EMPTY => 'An email address is required.'
						// 			)
						// 		)
						// 	),
							'EmailAddress'
						)
					)
				),
				array( // our form submit button
					'button', 'submit', array(
						'label' => 'Update Admin Information',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));
			
			// create the admin password form
			$this->updateAdminPasswordForm = new Zend_Form();
			$this->updateAdminPasswordForm
				->setAttribs(array(
					'id'=>'updateAdminPasswordForm'
				))
				->setAction('/admin/user/editpassword')
				->setMethod('post');
			
			// let's create our form elements/inputs
			$this->updateAdminPasswordForm->addElements(array(
				array( // our current password input field
					'password', 'password_current', array(
						'label' => 'Current Password: ',
						'required' => true,
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'Your current password is required.'
									)
								)
							)
						)
					)
				),
				array( // our new password input field
					'password', 'password_new', array(
						'label' => 'New Password: ',
						'required' => true,
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A new password is required.'
									)
								)
							)
						)
					)
				),
				array( // our password verification input field
					'password', 'password_verification', array(
						'label' => 'New Password Verification: ',
						'required' => true,
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'This password must match your New Password.'
									)
								)
							)
						)
					)
				),
				array( // our form submit button
					'button', 'submit', array(
						'label' => 'Update Admin Password',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));

			// Allow our view access to the form.
			$this->view->updateAdminDetailForm = $this->updateAdminDetailForm;
			$this->view->updateAdminPasswordForm = $this->updateAdminPasswordForm;
		}
	}
	
?>