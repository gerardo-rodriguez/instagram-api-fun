<?php

	require_once( '../app/modules/admin/models/CollectionModel.php' );
	require_once( '../app/modules/admin/models/ItemModel.php' );
	require_once( '../app/modules/admin/models/ImageModel.php' );
	require_once( '../app/modules/admin/models/CollectionImageModel.php' );
	require_once( '../app/libs/phpthumb/ThumbLib.inc.php' );

	/**
	 * Admin_CollectionsController - The controller for our Admin Collection view.
	 */
	class Admin_CollectionsController extends Zend_Controller_Action
	{
		private $collectionModel;
		private $itemModel;
		private $imageModel;
		private $collectionImageModel;
		private $addItemForm;
		private $addCollectionForm;
		private $editCollectionForm;
		private $config;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');
			// if not logged in, redirect to the login form
			$authHelper = $this->_helper->getHelper('Authenticate');
			if( !$authHelper->isAuthenticated('Admin_Session_Namespace') ) $this->_helper->getHelper('Redirector')->gotoSimple('index','index','admin');

			// our models
			$this->collectionModel = new CollectionModel();
			$this->itemModel = new ItemModel();
			$this->imageModel = new ImageModel();
			$this->collectionImageModel = new CollectionImageModel();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('admin_logged_in');
			
			//Access to helper
			//$helper = $this->_helper->HelperName;
			

			/* Config via ini */
			$this->config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));

			// echo $this->config->test . "<br />";
			// echo __FILE__ . "<br />";
			// die(APPLICATION_PATH . $this->config->paths->collectionsImages);
		}
		
		public function indexAction()
		{
			//var_dump( $this->getRequest()->getParams() );
			
			

			// Create the form
			$this->createAddCollectionForm();

			// Handle the collection submit request
			$this->handleCollectionSubmission();

			// grab our current collections
			$collectionDataArr = $this->collectionModel->fetchActiveCollections()->toArray();
			// grab our archived collections
			$archivedCollectionDataArr = $this->collectionModel->fetchArchivedCollections()->toArray();

			// pass them along to allow the view access
			$this->view->collectionDataArr = $collectionDataArr;
			$this->view->archivedCollectionDataArr = $archivedCollectionDataArr;
			
		}
		/**
		 * editAction - Will handle the edit action.
		 */
		public function editAction()
		{
			// let's grab the collection & item id
			$collectionID = $this->getRequest()->getParam('collectionID');
			// let's get the collection data
			$collectionDataArr = $this->collectionModel->fetchSingleCollectionData($collectionID)->toArray();
			$this->view->collectionDataArr = $collectionDataArr[0];

			// Create our edit form
			$this->createEditCollectionForm($collectionDataArr[0]);
			
			$this->handleEditCollectionSubmission($collectionID);
		}
		/**
		 * deleteAction - Will delete a given collection.
		 */
		public function deleteAction()
		{
			// let's archive the collection
			$collectionID = $this->getRequest()->getParam('collectionID');
			
			$this->collectionModel->deleteCollection($collectionID);

			$this->_redirect("/admin/collections/");
		}
		/**
		 * viewAction - Will load up a given collection.
		 */
		public function viewAction()
		{
			// let's grab the collection id
			$collectionID = $this->getRequest()->getParam('collectionID');

			// create the forms
			$this->createAddItemForm($collectionID);

			// handle is an item was submitted
			$this->handleAddItemSubmission($collectionID);

			// let's get the collection data
			$collectionDataArr = $this->collectionModel->fetchSingleCollectionData($collectionID)->toArray();
			$this->view->collectionDataArr = $collectionDataArr[0];
			// let's get the collection items data
			$this->view->itemDataArr = $this->itemModel->fetchCollectionItemData($collectionID)->toArray();
			
		}
		/**
		 * archiveAction - Will archive a given collection.
		 */
		public function archiveAction()
		{
			// let's archive the collection
			$collectionID = $this->getRequest()->getParam('collectionID');
			$this->collectionModel->updateCollectionArchiveStatus('archive',$collectionID);
			
			// redirect back to the collections view
			$this->_redirect("/admin/collections/");
		}
		/**
		 * unarchiveAction - Will unarchive a given collection.
		 */
		public function unarchiveAction()
		{
			// let's archive the collection
			$collectionID = $this->getRequest()->getParam('collectionID');
			$this->collectionModel->updateCollectionArchiveStatus('unarchive',$collectionID);
			
			// redirect back to the collections view
			$this->_redirect("/admin/collections/");
		}
		/**
		 * handleEditCollectionSubmission - Will handle the submission of a collection edit.
		 */
		private function handleEditCollectionSubmission($collectionID)
		{
			$customErrorMessages = array();
			$customSuccessMessages = array();
			
			$request = $this->getRequest();

			// if form submitted
			if( $request->isPost() )
			{
				// if not valid
				if( !$this->editCollectionForm->isValid($request->getPost()) ) {
					// show errors
					echo $this->editCollectionForm->isValid($this->getRequest()->getParams());
				} else {
					$newCollectionName = $this->editCollectionForm->getValue('collection_name');

					// Zend_Debug::dump($this->editCollectionForm->full_image_file->isUploaded() );
					
					$insertData = array(
						'name'=>$newCollectionName
					);

					// rename the image files
					$uniqueToken = md5(uniqid(mt_rand(), true));
				
					// full size file
					if( $this->editCollectionForm->full_image_file->isUploaded() )
					{
						$originalFullFile = pathinfo($this->editCollectionForm->full_image_file->getFileName());
						$newFullFilename = $uniqueToken . '_full.' . $originalFullFile['extension'];
						$this->editCollectionForm->full_image_file->addFilter('Rename', $newFullFilename);
						// receive the file to save it
						$this->editCollectionForm->full_image_file->receive();

						$fullImagePath = APPLICATION_PATH . $this->config->paths->collectionsImages . $newFullFilename;
						$full = PhpThumbFactory::create( $fullImagePath );
						$full->resize(800,2000)->save( $fullImagePath );

						$insertData['full_filename'] = $newFullFilename;
					}

					if( $this->editCollectionForm->thumb_image_file->isUploaded() )
					{
						// thumb size file
						$origianlThumbFile = pathinfo($this->editCollectionForm->thumb_image_file->getFileName());
						$newThumbFilename = $uniqueToken . '_thumb.' . $origianlThumbFile['extension'];
						$this->editCollectionForm->thumb_image_file->addFilter('Rename', $newThumbFilename);
						// receive the file to save it
						$this->editCollectionForm->thumb_image_file->receive();

						$thumbImagePath = APPLICATION_PATH . $this->config->paths->collectionsImages . $newThumbFilename;
						$thumb = PhpThumbFactory::create( $thumbImagePath );
						$thumb->resize(100,100)->save( $thumbImagePath );
						
						$insertData['thumb_filename'] = $newThumbFilename;
					}

					// Zend_Debug::dump($insertData);
					// die();
					$where = $this->collectionModel->getAdapter()->quoteInto('id = ?', $collectionID);
					$updateCollectionID = $this->collectionModel->update($insertData, $where);

				
					// if( $updateCollectionID ) {
						$this->_redirect("/admin/collections/view/collectionID/$collectionID");
					// } else {
						// array_push($customErrorMessages, 'We were unable to update the collection. Please try again.');
					// }
				}
			}
			
			$this->view->customSuccessMessages = $customSuccessMessages;
			$this->view->customErrorMessages = $customErrorMessages;
		}
		/**
		 * handleCollectionSubmission - Will handle the submission of a new collection.
		 */
		private function handleCollectionSubmission()
		{
			$customErrorMessages = array();
			$customSuccessMessages = array();

			$request = $this->getRequest();
			// if form submitted
			if( $request->isPost() )
			{
				// if not valid
				if( !$this->addCollectionForm->isValid($request->getPost()) ) {
					// show errors
					echo $this->addCollectionForm->isValid($this->getRequest()->getParams());
				} else {
					// grab the collection name
					$newCollectionName = $this->addCollectionForm->getValue('collection_name');

					$collectionDataArr = $this->collectionModel->fetchAll('name="'.$newCollectionName.'"')->toArray();

					if( !empty($collectionDataArr) ) {
						array_push($customErrorMessages, 'This collection name already exists. Please enter a new collection name.');
					} else {
						
						// rename the image files
						$uniqueToken = md5(uniqid(mt_rand(), true));
					
						// full size file
						$originalFullFile = pathinfo($this->addCollectionForm->full_image_file->getFileName());
						$newFullFilename = $uniqueToken . '_full.' . $originalFullFile['extension'];
						$this->addCollectionForm->full_image_file->addFilter('Rename', $newFullFilename);
						// receive the file to save it
						$this->addCollectionForm->full_image_file->receive();

						// thumb size file
						$origianlThumbFile = pathinfo($this->addCollectionForm->thumb_image_file->getFileName());
						$newThumbFilename = $uniqueToken . '_thumb.' . $origianlThumbFile['extension'];
						$this->addCollectionForm->thumb_image_file->addFilter('Rename', $newThumbFilename);
						// receive the file to save it
						$this->addCollectionForm->thumb_image_file->receive();


						$fullImagePath = APPLICATION_PATH . $this->config->paths->collectionsImages . $newFullFilename;
						$thumbImagePath = APPLICATION_PATH . $this->config->paths->collectionsImages . $newThumbFilename;

						// try {
							$full = PhpThumbFactory::create( $fullImagePath );
							$thumb = PhpThumbFactory::create( $thumbImagePath );
						// }
						// catch (Expection $e) 
						// {
							//die('error');
							// handle the error here
							// $this->_forward('image-error', 'error');
						// }
						
						$full->resize(700,700)->save( $fullImagePath );
						$thumb->resize(100,100)->save( $thumbImagePath );

						$insertData = array(
							'name' => $newCollectionName,
							'thumb_filename' => $newThumbFilename,
							'full_filename' => $newFullFilename,
							'create_date' => date('Y-m-d')
						);

						$newCollectionID = $this->collectionModel->insert($insertData);
					
						if( $newCollectionID ) {
							$this->addCollectionForm->reset();
							array_push($customSuccessMessages, '"' . $newCollectionName . '" was added below.');
						} else {
							array_push($customErrorMessages, 'We were unable to add the collection. Please try again.');
						}
					}
				}
			}
			
			$this->view->customSuccessMessages = $customSuccessMessages;
			$this->view->customErrorMessages = $customErrorMessages;
		}
		/**
		 * handleAddItemSubmission - Will update the item details for given item.
		 */
		private function handleAddItemSubmission($collectionID)
		{
			$customErrorMessages = array();
			$customSuccessMessages = array();

			$request = $this->getRequest();
			
			// if form submitted
			if( $request->isPost() )
			{
				// if not valid
				if( !$this->addItemForm->isValid($request->getParams()) ) {
					// show errors
					echo $this->addItemForm->isValid($this->getRequest()->getParams());
				} else {
					// grab our data
					$newPhotoReference = $this->addItemForm->getValue('photo_reference');
					$newStockNumber = $this->addItemForm->getValue('stock_number');
					$newDescription = $this->addItemForm->getValue('description');
					$newNotes = $this->addItemForm->getValue('notes');
					$unitPrice = $this->addItemForm->getValue('unit_price');

					$insertData = array(
						'collection_id'=>$collectionID,
						'photo_reference'=>$newPhotoReference,
						'stock_number'=>$newStockNumber,
						'description'=>$newDescription,
						'unit_price'=>$unitPrice,
						'notes'=>$newNotes,
						'create_date'=>date('Y-m-d')
					);
					$newItemID = $this->itemModel->insert($insertData);
					

					if( $newItemID ) {
						// array_push($customSuccessMessages, '');
						$this->addItemForm->reset();
					} else {
						array_push($customErrorMessages, 'We were unable to add the item. Please try again.');
					}
				}
			}
			
			$this->view->customSuccessMessages = $customSuccessMessages;
			$this->view->customErrorMessages = $customErrorMessages;
		}
		/**
		 * createEditCollectionForm - Creates our edit collection form
		 */
		private function createEditCollectionForm($collectionDataArr)
		{
			// die(var_dump($collectionDataArr));
			// create the contact form
			$this->editCollectionForm = new Zend_Form();
			$this->editCollectionForm
				->setAttribs(array(
					'id'=>'editCollectionForm',
					'enctype'=>'multipart/form-data'
				))
				->setAction('/admin/collections/edit/collectionID/'.$collectionDataArr['id'])
				->setMethod('post');
			
			// let's create our form elements/inputs
			$this->editCollectionForm->addElements(array(
				array( // the full_image_file upload
					'file', 'full_image_file', array(
						'label' => 'Full size image *',
						'destination' => APPLICATION_PATH . $this->config->paths->collectionsImages,
						'required' => false,
						'validators' => array(
							array(
								'Count', false, 1
							),
							array(
								'Size', false, 614400 // 600 kb
							),
							array(
								'Extension', false, 'jpg,png,gif'
							)
						)
					)
				),
				array( // the thumb_image_file upload
					'file', 'thumb_image_file', array(
						'label' => 'Thumbnail size image *',
						'destination' => APPLICATION_PATH . $this->config->paths->collectionsImages,
						'required' => false,
						'validators' => array(
							array(
								'Count', false, 1
							),
							array(
								'Size', false, 614400 // 600 kb
							),
							array(
								'Extension', false, 'jpg,png,gif'
							)
						)
					)
				),
				array( // the collection name input
					'text', 'collection_name', array(
						'value' => $collectionDataArr['name'],
						'label' => 'Collection name *',
						'required' => true,
						// 'autofocus' => 'autofocus',
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A collection name is required.'
									)
								)
							)
						)
					)
				),
				array( // our form submit button
					'button', 'submit', array(
						'label' => 'Update Collection',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));
			
			// Allow our view access to the form.
			$this->view->editCollectionForm = $this->editCollectionForm;
		}
		/**
		 * createAddCollectionForm - Creates up our collection add form
		 */
		private function createAddCollectionForm()
		{
			// create the contact form
			$this->addCollectionForm = new Zend_Form();
			$this->addCollectionForm
				->setAttribs(array(
					'id'=>'addCollectionForm',
					'enctype'=>'multipart/form-data'
				))
				->setAction('/admin/collections/')
				->setMethod('post');
			
			// let's create our form elements/inputs
			$this->addCollectionForm->addElements(array(
				array( // the full_image_file upload
					'file', 'full_image_file', array(
						'label' => 'Full size image *',
						'destination' => APPLICATION_PATH . $this->config->paths->collectionsImages,
						'required' => true,
						'validators' => array(
							array(
								'Count', false, 1
							),
							array(
								'Size', false, 614400 /*600 kb*/
							),
							array(
								'Extension', false, 'jpg,png,gif'
							)
						)
					)
				),
				array( // the thumb_image_file upload
					'file', 'thumb_image_file', array(
						'label' => 'Thumbnail size image *',
						'destination' => APPLICATION_PATH . $this->config->paths->collectionsImages,
						'required' => true,
						'validators' => array(
							array(
								'Count', false, 1
							),
							array(
								'Size', false, 614400 /*600 kb*/
							),
							array(
								'Extension', false, 'jpg,png,gif'
							)
						)
					)
				),
				array( // the collection name input
					'text', 'collection_name', array(
						'label' => 'Collection name *',
						'required' => true,
						// 'autofocus' => 'autofocus',
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A collection name is required.'
									)
								)
							)/*,
							array(
								'Regex', true, array(
									'pattern' => '//',
									'messages' => array(
										'regexNotMatch' => 'Your custom error message.'
									)
								)
							)*/
						)
					)
				),
				array( // our form submit button
					'button', 'submit', array(
						'label' => 'Add New Collection',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));
			
			// Allow our view access to the form.
			$this->view->addCollectionForm = $this->addCollectionForm;
		}
		/**
		 * createAddItemForm - Creates our item add form
		 */
		private function createAddItemForm($collectionID)
		{
			// create the contact form
			$this->addItemForm = new Zend_Form();
			$this->addItemForm
				->setAttribs(array(
					'id'=>'addItemForm'
				))
				->setAction("/admin/collections/view/collectionID/$collectionID")
				->setMethod('post');
				
			// let's create our form elements/inputs
			

			$this->addItemForm->addElements(array(
				array( // the photo reference input
					'text', 'photo_reference', array(
						'label' => 'Photo reference *',
						'required' => true,
						// 'autofocus' => 'autofocus',
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A photo reference is required.'
									)
								)
							)
						)
					)
				),
				array( // the stock number input
					'text', 'stock_number', array(
						'label' => 'Stock number *',
						'required' => true,
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A stock number is required.'
									)
								)
							)
						)
					)
				),
				array( // the description input
					'textarea', 'description', array(
						'label' => 'Description *',
						'required' => true,
						'validators' => array(
							array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A description is required.'
									)
								)
							)
						)
					)
				),
				array( // the unit price input
					'text', 'unit_price', array(
						'label' => 'Item price per unit * (example: 2.50 or 25.00)',
						'required' => true,
						'validators' => array(
							array(
								'Regex', true, array(
									'pattern' => '/^\$?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}[0-9]{0,} (\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/',
									'messages' => array(
										'regexNotMatch' => 'A valid amount is required.'
									)
								)
							)
						)
					)
				),
				array( // the notesinput
					'text', 'notes', array(
						'label' => 'Notes',
						// 'required' => true,
						'validators' => array(
							/*array(
								'NotEmpty', true, array(
									'messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => 'A photo reference is required.'
									)
								)
							),
							array(
								'Regex', true, array(
									'pattern' => '//',
									'messages' => array(
										'regexNotMatch' => 'Your custom error message.'
									)
								)
							)*/
						)
					)
				),
				array( // our form submit button
					'button', 'submit', array(
						'label' => 'Add New Item',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));
			
			// Allow our view access to the form.
			$this->view->addItemForm = $this->addItemForm;
		}
	}
?>