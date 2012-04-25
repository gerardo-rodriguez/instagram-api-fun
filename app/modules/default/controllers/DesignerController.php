<?php
	//-------------------------------------------------
	// Imports
	//-------------------------------------------------
	require_once( '../app/forms/DesignerForm.php' );
	require_once( '../app/forms/DesignerUpdateDetailsForm.php' );
	require_once( '../app/forms/DesignerUpdatePasswordForm.php' );
	require_once( '../app/forms/DesignerUpdateDocumentationForm.php' );
	require_once( '../app/forms/LoginForm.php' );
	require_once( '../app/forms/QuantityForm.php' );
	require_once( '../app/modules/default/models/DesignerModel.php' );
	require_once( '../app/modules/default/models/CollectionModel.php' );
	require_once( '../app/modules/default/models/ItemModel.php' );
	require_once( '../app/modules/default/models/BasketModel.php' );
	require_once( '../app/modules/default/models/BasketItemModel.php' );
	require_once( '../app/modules/default/models/AdminModel.php' );
	
	/**
	 * DesignerController
	 *
	 * The controller for the Designer view.
	 *  
	 * @author Gerardo Rodriguez
	 * @created 01/01/2012
	 */

	class DesignerController extends Zend_Controller_Action
	{
		//-------------------------------------------------
		// Properties
		//-------------------------------------------------
		private $loginForm;
		private $registerDesignerForm;
		private $updateDesignerDetailsForm;
		private $quantityFormArr;
		private $config;
		private $designerModel;
		private $collectionModel;
		private $basketModel;
		private $basketItemModel;
		private $adminModel;
		private $itemModel;
		private $authHelper;
		private $redirector;
		private $mailHelper;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('default_logged_in');
			
			//Access to helpers
			$this->authHelper = $this->_helper->getHelper('Authenticate');
			$this->redirector = $this->_helper->getHelper('Redirector');
			$this->mailHelper = $this->_helper->getHelper('Mail');
			
			// Setup our models
			$this->designerModel = new DesignerModel();
			$this->collectionModel = new CollectionModel();
			$this->itemModel = new ItemModel();
			$this->basketModel = new BasketModel();
			$this->basketItemModel = new BasketItemModel();
			$this->adminModel = new AdminModel();

			/* Config via ini */
			$this->config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));
		}
		
		//-------------------------------------------------
		// Public Methods
		//-------------------------------------------------
		public function indexAction()
		{
			$this->goToDesignerLogin();
		}
		
		/**
		 * public logoutAction - Handles the logout action
		 */
		public function logoutAction()
		{
			// Logout of the current session namespace
			$this->authHelper->logout('Designer_Session_Namespace');
			$this->goToDesignerLogin();
		}
		/**
		 * public loginAction - Handles the login action.
		 */
		public function loginAction()
		{
			// check to see if we're logged in
			$this->checkToSeeIfAlreadyLoggedIn();

			//Sets alternative layout
			$this->_helper->layout->setLayout('default_logged_out');

			// create/show the login form
			$this->loginForm = new LoginForm();
			$this->loginForm->setAttribs(array(
				'action' => '/designer/login/',
				'id' => 'designerLoginForm',
				'name' => 'designerLoginForm'
			));
			$this->view->loginForm = $this->loginForm;

			$this->handleDesignerLoginSubmit();
		}
		/**
		 * public applyAction - Handles the apply action, which maps to the apply view.
		 */
		public function applyAction()
		{
			// check to see if we're logged in
			$this->checkToSeeIfAlreadyLoggedIn();

			//Sets alternative layout
			$this->_helper->layout->setLayout('default_logged_out');

			// create/show the register form
			$this->registerDesignerForm = new DesignerForm();
			$this->registerDesignerForm->setAttribs(array(
				'action' => '/designer/apply/',
				'id' => 'registerDesignerForm',
				'name' => 'registerDesignerForm'
			));
			$this->view->registerDesignerForm = $this->registerDesignerForm;
			
			$this->handleDesignerRegisterSubmit();
		}
		/**
		 * public updateAction - Handles the update action, which maps to the update view.
		 */
		public function updateAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();
		}
		/**
		 * public updateDetailsAction - Handles the update details action, which maps to the update details view.
		 */
		public function updateDetailsAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			// create/show the update designer form
			$this->updateDesignerDetailsForm = new DesignerUpdateDetailsForm();
			$this->updateDesignerDetailsForm->setAttribs(array(
				'action' => '/designer/update-details/',
				'id' => 'updateDesignerDetailsForm',
				'name' => 'updateDesignerDetailsForm'
			));

			// grab the current identity in session
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			
			// Zend_Debug::dump($identityObject);
			
			$designerData = $this->designerModel->fetchDesignerFormPopulateData($identityObject->id)->toArray();
			$designerData = $designerData[0];
			
			// Zend_Debug::dump($designerData);
			
			$this->updateDesignerDetailsForm->populate($designerData);
			
			$this->view->updateDesignerDetailsForm = $this->updateDesignerDetailsForm;
			
			$this->handleDesignerUpdateDetailsSubmit();
		}
		/**
		 * public updatePasswordAction - Handles the update password action, which maps to the update password view.
		 */
		public function updatePasswordAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			// create/show the update designer form
			$updateDesignerPasswordForm = new DesignerUpdatePasswordForm();
			$updateDesignerPasswordForm->setAttribs(array(
				'action' => '/designer/update-password/',
				'id' => 'updateDesignerPasswordForm',
				'name' => 'updateDesignerPasswordForm'
			));

			// grab the current identity in session
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			
			$this->view->updateDesignerPasswordForm = $updateDesignerPasswordForm;
			
			$this->handleDesignerUpdatePasswordSubmit($updateDesignerPasswordForm);
		}
		/**
		 * public updatePasswordAction - Handles the update password action, which maps to the update password view.
		 */
		public function updateDocumentationAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			// create/show the update designer form
			$updateDesignerDocumentForm = new DesignerUpdateDocumentationForm();
			$updateDesignerDocumentForm->setAttribs(array(
				'action' => '/designer/update-documentation/',
				'id' => 'updateDesignerDocumentForm',
				'name' => 'updateDesignerDocumentForm'
			));

			// grab the current identity in session
			// $identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			
			$this->view->updateDesignerDocumentForm = $updateDesignerDocumentForm;
			
			$this->handleDesignerUpdateDocumentationSubmit($updateDesignerDocumentForm);
		}
		/**
		 * public collectionsAction - Handles the collections action
		 */
		public function collectionsAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();
			
			$this->view->collectionsDataArr = $this->collectionModel->fetchActiveCollections();
			
			$this->getBasketData();
		}
		/**
		 * public viewCollectionAction - Handles the view-collection action
		 */
		public function viewCollectionAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();
			
			// let's grab the collection id
			$collectionID = $this->getRequest()->getParam('collectionID');

			// Let's grab the data for the designer
			$collectionDataArr = $this->collectionModel->fetchSingleCollection($collectionID)->toArray();
			$this->view->collectionDataArr = $collectionDataArr[0];

			$itemsDataArr = $this->itemModel->fetchCollectionItems($collectionID)->toArray();
			$this->view->itemsDataArr = $itemsDataArr;

			// create basket
			$this->getBasketData();
		}
		/**
		 * public addItemToBasketAction - Adds an item to the users cart
		 */
		public function addItemToBasketAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();
			
			// let's grab the item id
			$itemID = $this->getRequest()->getParam('itemID');
			$collectionID = $this->getRequest()->getParam('collectionID');
			
			// grab the current identity in session
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			
			// retrieve item data as we'll need it as well.
			$where = $this->itemModel->getAdapter()->quoteInto('id = ?', $itemID);
			$itemDataArr = $this->itemModel->fetchAll($where);
			$itemDataArr = $itemDataArr[0];
			
			$params = array(
				'basketID' => $identityObject->basket_id,
				'itemID' => $itemID
			);
			// first check to see if the item already exists in our cart
			$basketResultArr = $this->basketItemModel->fetchAllBasketItems($params)->toArray();
			
			if( count($basketResultArr) )
			{
				// update the quantity
				$quantity = (int)$basketResultArr[0]['quantity'] + 1;
				$totalItemPrice = $quantity * $itemDataArr['unit_price'];

				// update data
				$updateData = array(
					'quantity' => $quantity,
					'total_item_price' => $totalItemPrice
				);
				$where = $this->basketItemModel->getAdapter()->quoteInto('id = ?', $basketResultArr[0]['id']);
				$this->basketItemModel->update($updateData, $where);
			} else {
				// add fresh item
				// let's add this item to this basket
				$insertData = array(
					'basket_id' => $identityObject->basket_id,
					'item_id' => $itemID,
					'quantity' => 1,
					'total_item_price' => $itemDataArr['unit_price']
				);
				$this->basketItemModel->insert($insertData);
			}
			
			
			// redirect back to the collection view
			$this->redirector->gotoSimple('view-collection','designer','default', array(
				'collectionID' => $collectionID
			));
		}
		/**
		 * public viewBasketAction - Handle the view basket action for the view-basket view
		 */
		public function viewBasketAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			// grab the current identity in session
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			
			// give the view some info for the designer
			$this->view->designerName = $identityObject->owner_first_name . " " . $identityObject->owner_last_name;
			$this->view->designerID = $identityObject->id;
			
			// ask for the items for this users basket
			$basketItemsDataArr = $this->basketModel->fetchBasketItemsData($identityObject->basket_id)->toArray();
			
			// to keep track of grand total
			$grandTotal = 0;
						
			// We'll include the form as part of the basket item array
			foreach( $basketItemsDataArr as &$basketItem ) 
			{
				$formID = 'quantityForm_' . $basketItem['basket_item_id'];

				$quantityForm = new QuantityForm();
				$quantityForm->setQuantityValue($basketItem['quantity']);
				$quantityForm->setBasketItemID($basketItem['basket_item_id']);
				$quantityForm->setItemID($basketItem['item_id']);
				$quantityForm->setFormID($formID);
				$quantityForm->setAttribs(array(
					'action' => '/designer/view-basket/',
					'id' => $formID,
					'name' => 'quantityForm'
				));

				$this->quantityFormArr[$formID] = $quantityForm;
				$basketItem['form'] = $quantityForm;
				
				// add to the grand total
				$grandTotal += $basketItem['total_item_price'];
			}
			unset($basketItem);

			
			$request = $this->getRequest();
			
			// if form submitted
			if( $request->isPost() ) {
				$this->handleUpdateQuantitySubmit();
			} else {
				// pass along data to the view

				$this->view->basketItemsDataArr = $basketItemsDataArr;
				$this->view->grandTotal = $grandTotal;
			}

		}
		/**
		 * public submitOrderAction - Will handle the submit order action
		 */
		public function submitOrderAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			// $designerID = $request->getParam('designerID');
			
			$viewData = array();
			
			// grab the current identity in session
			// give the view some info for the designer
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			$viewData['designerData'] = $identityObject;
			$viewData['todaysDate'] = date('Y-m-d');
			
			Zend_Debug::dump($identityObject);

			// ask for the items for this users basket
			$basketItemsDataArr = $this->basketModel->fetchBasketItemsData($identityObject->basket_id)->toArray();
			
			// to keep track of grand total
			$grandTotal = 0;
						
			// We'll include the form as part of the basket item array
			foreach( $basketItemsDataArr as $basketItem ) 
			{
				// add to the grand total
				$grandTotal += $basketItem['total_item_price'];
			}

			$viewData['basketItemsDataArr'] = $basketItemsDataArr;
			$viewData['grandTotal'] = $grandTotal;
			
			// grab the admin details
			$adminData = $this->adminModel->fetchAdminDetails()->toArray();
			$adminData = $adminData[0];
			
			// put together the designer data
			$designerData = array(
				'business_name' => $identityObject->business_name,
				'owner_first_name' => $identityObject->owner_first_name,
				'owner_last_name' => $identityObject->owner_last_name,
				'email' => $identityObject->email
			);

			
			// send emails
			$this->sendAdminAndDesignerOrderEmail($designerData,$adminData,$viewData);
			
			// update our db with order

			// redirect back to the basket view
			$this->redirector->gotoSimple('view-basket','designer');
		}
		/**
		 * public itemRemoveAction - Will handle the item remove action
		 */
		public function itemRemoveAction()
		{
			// check to make sure we're logged in
			$this->redirectIfNotAuthorized();

			$request = $this->getRequest();
			$basketItemID = $request->getParam('basketItemID');
			
			$where = $this->basketItemModel->getAdapter()->quoteInto('id = ?', $basketItemID);
			$deleted = $this->basketItemModel->delete($where);
			
			// redirect back to the basket view
			$this->redirector->gotoSimple('view-basket','designer');
		}
		/**
		 * public pendingAction - Handles the pending action
		 */
		public function pendingAction()
		{
			//Sets alternative layout
			$this->_helper->layout->setLayout('default_logged_in_no_access');
		}
		/**
		 * public deniedAction - Handles the denied action
		 */
		public function deniedAction()
		{
			//Sets alternative layout
			$this->_helper->layout->setLayout('default_logged_in_no_access');
		}
		//-------------------------------------------------
		// Private Methods
		//-------------------------------------------------
		/**
		 * private getBasketData - Will retreive and feed the basket item data to the view
		 */
		private function getBasketData()
		{
			// retrieve the user date in session
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
			// ask for the items for this users basket
			$basketItemsDataArr = $this->basketModel->fetchBasketItemsData($identityObject->basket_id)->toArray();
			$this->view->basketItemsDataArr = $basketItemsDataArr;
		}
		/**
		 * private redirectIfNotAuthorized - Will redirect us if not logged in
		 */
		private function redirectIfNotAuthorized()
		{
			if( !$this->authHelper->isAuthenticated('Designer_Session_Namespace') ) 
				$this->goToDesignerLogin();
				
			$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');

			switch( $identityObject->status )
			{
				case 'pending':
					$this->redirector->gotoSimple('pending','designer');
					break;
					
				case 'denied':
					$this->redirector->gotoSimple('denied','designer');
					break;
					
				case 'approved':
				default:
				
			}
		}
		/**
		 * private checkToSeeIfLoggedInAsDesigner - Will redirect if logged in as designer
		 */
		private function checkToSeeIfAlreadyLoggedIn()
		{
			if( $this->authHelper->isAuthenticated('Designer_Session_Namespace') ) 
				$this->redirector->gotoSimple('collections','designer');
		}
		/**
		 * private goToDesignerLogin - Will redirect to designer public home view
		 */
		private function goToDesignerLogin()
		{
			// redirect back to home view
			$this->redirector->gotoSimple( 'login', 'designer' );
		}
		/**
		 * private handleDesignerLoginSubmit - Will handle the designer loging POST
		 */
		private function handleDesignerLoginSubmit()
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
						'tableName' => 'designer',
						'identityColumn' => 'email',
						'credentialColumn' => 'hash',
						'credentialTreatment' => 'sha1(CONCAT(salt,md5(?)))',
						'columnsToLeaveOutArr' => array('hash','salt'),
						'identity' => $email,
						'credential' => $password,
						'sessionNamespace' => 'Designer_Session_Namespace'
					);
					
					// let's authenticate!!
					$authResult = $this->authHelper->authenticate( $authParams );

					// If the user is a valid user
					if( $authResult->isValid() )
					{
						$this->redirector->gotoSimple('collections','designer');
					}
					else
					{
						array_push($customErrorMessages, 'Wrong email or password provided. Please try again.');
					}
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private handleDesignerRegisterSubmit - Will handle the designer registration POST
		 */
		private function handleDesignerRegisterSubmit()
		{
			$request = $this->getRequest();
			$form = $this->registerDesignerForm;
			
			// if form submitted
			if( $request->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				// if valid
				if( $form->isValid($request->getPost()) ) {
				
					if( $form->getValue('tax_id_delivery') == 'attach' )
					{
						$document = $form->getValue('tax_id_document');
						// Zend_Debug::dump($document);
						// die();
						if( !$document )
						{
							$form->markAsError();
							$form->tax_id_document->addError('You must attach a document.');
							return;
						}
					}

					// get the info from the form
					$email = $form->getValue('email');
					$password = $form->getValue('password');
					$businessName = $form->getValue('business_name');
					$ownerFirstName = $form->getValue('owner_first_name');
					$ownerLastName = $form->getValue('owner_last_name');
					$address = $form->getValue('address');
					$city = $form->getValue('city');
					$state = $form->getValue('state');
					$zip = $form->getValue('zip');
					$phoneNumber = $form->getValue('phone_number');
					$faxNumber = $form->getValue('fax_number');
					$taxIDDelivery = $form->getValue('tax_id_delivery');
		
					// let's salt it up!
					$salt = sha1(md5($password.time()));
					$hash = sha1($salt.md5($password));
		
					// let's create our inser data
					$insertData = array(
						'email' => $email,
						'hash' => $hash,
						'salt' => $salt,
						'business_name' => $businessName,
						'owner_first_name' => $ownerFirstName,
						'owner_last_name' => $ownerLastName,
						'address' => $address,
						'city' => $city,
						'state' => $state,
						'zip' => $zip,
						'phone_number' => $phoneNumber,
						'fax_number' => $faxNumber,
						'tax_id_delivery' => $taxIDDelivery,
						'create_date' => date('Y-m-d')
					);
		
					// full size file
					if( $form->tax_id_document->isUploaded() )
					{
						// rename the file
						$uniqueToken = md5(uniqid(mt_rand(), true));

						$originalFullFile = pathinfo($form->tax_id_document->getFileName());
						$newFilename = $uniqueToken . '.' . $originalFullFile['extension'];

						rename(
							$form->tax_id_document->getFileName(),
							APPLICATION_PATH . $this->config->paths->designerDocuments . $newFilename
						);

						$insertData['tax_document_filename'] = $newFilename;
					}
					
					// check for duplicate identities
					
					$where = $this->designerModel->getAdapter()->quoteInto('email = ?', $email);
					$duplicateResult = $this->designerModel->fetchAll($where)->toArray();
					
					// Zend_Debug::dump($duplicateResult);
					// die();
					
					if( !$duplicateResult ) {
						$newDesignerID = $this->designerModel->insert($insertData);
					
						if( $newDesignerID ) {
							$form->reset();
						
							// Let's create a basket for the new user.
							$basketData = array(
								// 'user_id' => $newDesignerID,
								'create_date' => date('Y-m-d'),
								'modify_date' => date('Y-m-d')
							);
							$newBasketID = $this->basketModel->insert($basketData);
							
							// add the new basket id to the designer profile
							$basketData = array(
								'basket_id' => $newBasketID
							);
							$where = $this->designerModel->getAdapter()->quoteInto('id = ?', $newDesignerID);
							$this->designerModel->update($basketData, $where);

							// setup our params for the authentication
							$authParams = array(
								'tableName' => 'designer',
								'identityColumn' => 'email',
								'credentialColumn' => 'hash',
								'credentialTreatment' => 'sha1(CONCAT(salt,md5(?)))',
								'columnsToLeaveOutArr' => array('hash','salt'),
								'identity' => $email,
								'credential' => $password,
								'sessionNamespace' => 'Designer_Session_Namespace'
							);
							//$isAuthValid = 
							$this->authHelper->authenticate( $authParams );
							
							// send an email informing the admin of the new registrant
							$adminData = $this->adminModel->fetchAdminDetails()->toArray();
							$adminData = $adminData[0];

							
							$this->sendAdminAndDesignerRegistrationEmail($insertData, $adminData);

							// $this->sendDesignerEmail($insertData, $adminData);
							
							// If the user authentication is a valid
							// if( $isAuthValid )
							// {
								$this->goToDesignerLogin();
							// }
							// else
							// {
								// array_push($customErrorMessages, 'Wrong email or password provided. Please try again.');
							// }
						} else {
							array_push($customErrorMessages, 'We were unable to create a new user. Please try again.');
						}
					} else {
						array_push($customErrorMessages, 'The email provided is already in our system. Please use a different email address.');
					}
		
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private handleDesignerUpdateDocumentationSubmit - Will handle the update document POST
		 * @param $form - The form that was submitted
		 */
		private function handleDesignerUpdateDocumentationSubmit($form)
		{
			$request = $this->getRequest();
			// $form = $this->registerDesignerForm;
			
			// if form submitted
			if( $request->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				// if valid
				if( $form->isValid($request->getPost()) ) {
		
					// let's create our update data
					$updateData = array();
		
					// full size file
					if( $form->tax_id_document->isUploaded() )
					{
						// rename the file
						$uniqueToken = md5(uniqid(mt_rand(), true));

						$originalFullFile = pathinfo($form->tax_id_document->getFileName());
						$newFilename = $uniqueToken . '.' . $originalFullFile['extension'];
						
						// receive the file
						$form->tax_id_document->receive();

						rename(
							$form->tax_id_document->getFileName(),
							APPLICATION_PATH . $this->config->paths->designerDocuments . $newFilename
						);

						$updateData['tax_document_filename'] = $newFilename;
						$updateData['tax_id_delivery'] = 'attach';

						// grab the current identity
						$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
						$where = $this->designerModel->getAdapter()->quoteInto('id = ?', $identityObject->id);
						$rowsUpdated = $this->designerModel->update($updateData, $where);
				
						if( $rowsUpdated ) {
							$form->reset();
					
							//$this->goToDesignerLogin();
							array_push($customSuccessMessages, 'We successfully uploaded a new document for your profile.');
						} else {
							array_push($customErrorMessages, 'Your profile was not updated as no changes were made.');
						}
					} else {
						array_push($customErrorMessages, 'Your document failed to upload. Please try again.');
					}
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private handleDesignerUpdateDetailsSubmit - Will handle the profile details POST submit
		 */
		private function handleDesignerUpdateDetailsSubmit()
		{
			$request = $this->getRequest();
			$form = $this->updateDesignerDetailsForm;
			
			// if form submitted
			if( $request->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				// if valid
				if( $form->isValid($request->getPost()) ) {
				
					// get the info from the form
					$email = $form->getValue('email');
					$businessName = $form->getValue('business_name');
					$ownerFirstName = $form->getValue('owner_first_name');
					$ownerLastName = $form->getValue('owner_last_name');
					$address = $form->getValue('address');
					$city = $form->getValue('city');
					$state = $form->getValue('state');
					$zip = $form->getValue('zip');
					$phoneNumber = $form->getValue('phone_number');
					$faxNumber = $form->getValue('fax_number');

					// let's create our inser data
					$updateData = array(
						'email' => $email,
						'business_name' => $businessName,
						'owner_first_name' => $ownerFirstName,
						'owner_last_name' => $ownerLastName,
						'address' => $address,
						'city' => $city,
						'state' => $state,
						'zip' => $zip,
						'phone_number' => $phoneNumber,
						'fax_number' => $faxNumber
					);
		
					$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');

					// Zend_Debug::dump($identityObject);

					$where = $this->designerModel->getAdapter()->quoteInto('id = ?', $identityObject->id);
					$rowsUpdated = $this->designerModel->update($updateData,$where);
				
					if( $rowsUpdated ) {
						// $form->reset();
						
						// reauthenticate/update the session info
						$identityObject->email = $email;
						$identityObject->business_name = $businessName;
						$identityObject->owner_last_name = $ownerLastName;
						$identityObject->owner_first_name = $ownerFirstName;
						$identityObject->address = $address;
						$identityObject->city = $city;
						$identityObject->state = $state;
						$identityObject->zip = $zip;
						$identityObject->phone_number = $phoneNumber;
						$identityObject->fax_number = $faxNumber;
					
						// $this->redirector->gotoSimple('update','designer');
						array_push($customSuccessMessages, 'We successfully updated your profile.');

					} else {
						array_push($customErrorMessages, 'Your profile was not updated as no changes were made.');
					}
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private handleDesignerUpdatePasswordSubmit - Will handle the update password POST submit
		 * @param $form - The form that was submitted
		 */
		private function handleDesignerUpdatePasswordSubmit($form)
		{
			$request = $this->getRequest();
			// $form = $this->updateDesignerPasswordForm;
			
			// if form submitted
			if( $request->isPost() )
			{
				$customErrorMessages = array();
				$customSuccessMessages = array();

				// if valid
				if( $form->isValid($request->getPost()) ) {
				
					// get the info from the form
					$currPassword = $form->getValue('current_password');
					$newPassword = $form->getValue('new_password');
					// $newPasswordConfirm = $form->getValue('new_password_confirm');
		
					$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
					$email = $identityObject->email;

					// setup our params for the authentication
					$authParams = array(
						'tableName' => 'designer',
						'identityColumn' => 'email',
						'credentialColumn' => 'hash',
						'credentialTreatment' => 'sha1(CONCAT(salt,md5(?)))',
						'columnsToLeaveOutArr' => array('hash','salt'),
						'identity' => $email,
						'credential' => $currPassword,
						'sessionNamespace' => 'Designer_Session_Namespace'
					);
					
					// let's authenticate!!
					$authResult = $this->authHelper->authenticate( $authParams );

					// If the credentials are valid
					if( $authResult->isValid() )
					{
						// update password

						// let's salt it up!
						$salt = sha1(md5($newPassword.time()));
						$hash = sha1($salt.md5($newPassword));

						// let's create our inser data
						$updateData = array(
							'salt' => $salt,
							'hash' => $hash
						);

						$where = $this->designerModel->getAdapter()->quoteInto('id = ?', $identityObject->id);
						$rowsUpdated = $this->designerModel->update($updateData,$where);
				
						if( $rowsUpdated ) {
							$form->reset();
						
							// $this->redirector->gotoSimple('update','designer');
							array_push($customSuccessMessages, 'We successfully updated your password.');
						} else {
							array_push($customErrorMessages, 'Your profile was not updated as no changes were made.');
						}
					}
					else
					{
						array_push($customErrorMessages, 'The current password does not match the one on file. Please try again.');
					}
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private handleUpdateQuantitySubmit - Will handle the quantity update submit POST
		 */
		private function handleUpdateQuantitySubmit()
		{
			$request = $this->getRequest();
			
			// if form submitted
			if( $request->isPost() )
			{
				$formID = $request->getParam('form_id');
				$form = $this->quantityFormArr[$formID];

				$customErrorMessages = array();
				$customSuccessMessages = array();

				
				// if valid
				if( $form->isValid($request->getPost()) ) {
			
					// get the info from the form
					$basketItemID = $form->getValue('basket_item_id');
					$itemID = $form->getValue('item_id');
					$quantity = $form->getValue('quantity');
				
					$where = $this->itemModel->getAdapter()->quoteInto('id = ?', $itemID);
					$itemArr = $this->itemModel->fetchAll($where)->toArray();
	
					$itemArr = $itemArr[0];
				
					// Zend_Debug::dump($itemArr);
					// die();

					// let's create our inser data
					$updateData = array(
						'quantity' => $quantity,
						'total_item_price' => money_format('%i', $quantity * $itemArr['unit_price'])
					);
					$where = $this->basketItemModel->getAdapter()->quoteInto('id = ?', $basketItemID);
					$rowsUpdated = $this->basketItemModel->update($updateData,$where);
				
					// if( $rowsUpdated ) {
						// $form->reset();

						// grab the current identity in session
						$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
		
						// ask for the items for this users basket
						$basketItemsDataArr = $this->basketModel->fetchBasketItemsData($identityObject->basket_id)->toArray();

						// to keep track of grand total
						$grandTotal = 0;

						// We'll include the form as part of the basket item array
						foreach( $basketItemsDataArr as &$basketItem ) 
						{
							$formID = 'quantityForm_' . $basketItem['basket_item_id'];

							// drop in the already instantiated form
							$basketItem['form'] = $this->quantityFormArr[$formID];

							// add to the grand total
							$grandTotal += $basketItem['total_item_price'];
						}
						unset($basketItem);

						// pass the data to the view
						$this->view->basketItemsDataArr = $basketItemsDataArr;
						$this->view->grandTotal = $grandTotal;
					// } else {
						// array_push($customErrorMessages, 'We were unable to update the quantity. Please try again.');
					// }
				} else {
					echo 'WTF';

						// grab the current identity in session
						$identityObject = $this->authHelper->getIdentity('Designer_Session_Namespace');
		
						// ask for the items for this users basket
						$basketItemsDataArr = $this->basketModel->fetchBasketItemsData($identityObject->basket_id)->toArray();

						// to keep track of grand total
						$grandTotal = 0;

						// We'll include the form as part of the basket item array
						foreach( $basketItemsDataArr as &$basketItem ) 
						{
							$formID = 'quantityForm_' . $basketItem['basket_item_id'];
							// drop in the already instantiated form
							$basketItem['form'] = $this->quantityFormArr[$formID];

							// add to the grand total
							$grandTotal += $basketItem['total_item_price'];
						}
						unset($basketItem);

						// pass the data to the view
						$this->view->basketItemsDataArr = $basketItemsDataArr;
						$this->view->grandTotal = $grandTotal;
				}
			
				$this->view->customSuccessMessages = $customSuccessMessages;
				$this->view->customErrorMessages = $customErrorMessages;
			}
		}
		/**
		 * private sendAdminAndDesignerOrderEmail - Will send an admin email after submitting order.
		 */
		private function sendAdminAndDesignerOrderEmail($designerData, $adminData, $viewData)
		{
			// grab the current logged in user data
			//$adminDetails = Zend_Auth::getInstance()->getStorage()->read();
			
			// create view object for the email
			$adminHtml = new Zend_View();
			$adminHtml->setScriptPath(APPLICATION_PATH . '/../app/templates/email/');

			// assign valeues
			$adminHtml->assign('todaysDate', $viewData['todaysDate']);
			$adminHtml->assign('businessName', $designerData['business_name']);
			$adminHtml->assign('businessOwnerName', $designerData['owner_first_name']." ".$designerData['owner_last_name']);
			$adminHtml->assign('basketItemsDataArr', $viewData['basketItemsDataArr']);
			$adminHtml->assign('grandTotal', $viewData['grandTotal']);
			// $adminHtml->assign('loginURL', $this->config->urls->admin);

			// render view
			$adminBody = $adminHtml->render('toAdmin_submittedOrderEmail.phtml');
			
			$adminMessageData = array(
				'sender' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'recipient' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'email' => array(
					'subject' => 'New Order Submitted',
					'body' => $adminBody
				)
			);
			$this->mailHelper->sendMessage($adminMessageData);




			// create view object for the email
			$designerHtml = new Zend_View();
			$designerHtml->setScriptPath(APPLICATION_PATH . '/../app/templates/email/');

			// assign valeues
			$designerHtml->assign('todaysDate', $viewData['todaysDate']);
			$designerHtml->assign('businessName', $designerData['business_name']);
			$designerHtml->assign('businessOwnerName', $designerData['owner_first_name']." ".$designerData['owner_last_name']);
			$designerHtml->assign('basketItemsDataArr', $viewData['basketItemsDataArr']);
			$designerHtml->assign('grandTotal', $viewData['grandTotal']);
			// $designerHtml->assign('loginURL', $this->config->urls->designer);

			// render view
			$designerBody = $designerHtml->render('toDesigner_submittedOrderEmail.phtml');

			$designerMessageData = array(
				'sender' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'recipient' => array(
					'email' => $designerData['email'],
					'name' => $designerData['owner_first_name' ] . " " . $designerData['owner_last_name']
				),
				'email' => array(
					'subject' => 'New Order Submitted',
					'body' => $designerBody
				)
			);
			$this->mailHelper->sendMessage($designerMessageData);
		}
		/**
		 * private sendAdminAndDesignerRegistrationEmail - Will send an admin email after registering.
		 */
		private function sendAdminAndDesignerRegistrationEmail($designerData, $adminData)
		{
			// grab the current logged in user data
			//$adminDetails = Zend_Auth::getInstance()->getStorage()->read();
			
			// create view object for the email
			$adminHtml = new Zend_View();
			$adminHtml->setScriptPath(APPLICATION_PATH . '/../app/templates/email/');

			// assign valeues
			$adminHtml->assign('businessName', $designerData['business_name']);
			$adminHtml->assign('businessOwnerName', $designerData['owner_first_name']." ".$designerData['owner_last_name']);
			$adminHtml->assign('loginURL', $this->config->urls->admin);

			// render view
			$adminBody = $adminHtml->render('toAdmin_newApplicantEmail.phtml');
			
			$adminMessageData = array(
				'sender' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'recipient' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'email' => array(
					'subject' => 'New Designer Application Submitted',
					'body' => $adminBody
				)
			);
			$this->mailHelper->sendMessage($adminMessageData);




			// create view object for the email
			$designerHtml = new Zend_View();
			$designerHtml->setScriptPath(APPLICATION_PATH . '/../app/templates/email/');

			// assign valeues
			$designerHtml->assign('businessOwnerName', $designerData['owner_first_name']." ".$designerData['owner_last_name']);
			$designerHtml->assign('loginURL', $this->config->urls->designer);

			// render view
			$designerBody = $designerHtml->render('toDesigner_newApplicantEmail.phtml');

			$designerMessageData = array(
				'sender' => array(
					'email' => $adminData['email'],
					'name' => $adminData['first_name' ] . " " . $adminData['last_name']
				),
				'recipient' => array(
					'email' => $designerData['email'],
					'name' => $designerData['first_name' ] . " " . $designerData['last_name']
				),
				'email' => array(
					'subject' => 'New Designer Application Submitted',
					'body' => $designerBody
				)
			);
			$this->mailHelper->sendMessage($designerMessageData);
		}
	}
?>