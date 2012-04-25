<?php

	require_once( '../app/modules/admin/models/CollectionModel.php' );
	require_once( '../app/modules/admin/models/ItemModel.php' );

	/**
	 * Admin_ItemsController - The controller for our Admin Collections Items view.
	 */
	class Admin_ItemsController extends Zend_Controller_Action
	{
		private $collectionModel;
		private $itemModel;
		private $itemForm;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');

			// our models
			$this->collectionModel = new CollectionModel();
			$this->itemModel = new ItemModel();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('admin_logged_in');
			
			//Access to helper
			//$helper = $this->_helper->HelperName;

		}
		/**
		 * indexAction - The default index action
		 */
		public function indexAction()
		{
			//var_dump( $this->getRequest()->getParams() );
			$this->_redirect("/admin/collections/");
		}
		/**
		 * deleteAction - Handles our delete action
		 */
		public function deleteAction()
		{
			// let's grab the ids
			$itemID = $this->getRequest()->getParam('itemID');
			$collectionID = $this->getRequest()->getParam('collectionID');

			$where = $this->itemModel->getAdapter()->quoteInto('id = ?', $itemID);
 
			$rowsDeleted = $this->itemModel->delete($where);
			
			$this->_redirect("/admin/collections/view/collectionID/$collectionID");
		}
		/**
		 * editAction - Handles our edit action
		 */
		public function editAction()
		{
			// let's grab the collection & item id
			$collectionID = $this->getRequest()->getParam('collectionID');
			$itemID = $this->getRequest()->getParam('itemID');
			// let's get the collection data
			$collectionDataArr = $this->collectionModel->fetchSingleCollectionData($collectionID)->toArray();
			$this->view->collectionDataArr = $collectionDataArr[0];
			// let's get the collection items data
			$itemDataArr = $this->itemModel->fetchSingleItemData($itemID)->toArray();
			$this->view->itemDataArr = $itemDataArr[0];

			// Create the forms
			$this->createItemForm($itemDataArr[0]);
			
			$this->handleUpdateSubmission($collectionID, $itemDataArr[0]);
		}
		/**
		 * handleUpdateSubmission - Will update the item details for given item.
		 */
		private function handleUpdateSubmission($collectionID, $itemDataArr)
		{
			$customErrorMessages = array();
			$customSuccessMessages = array();

			// if form submitted
			if( $this->getRequest()->isPost() )
			{
				// if not valid
				if( !$this->itemForm->isValid($this->getRequest()->getParams()) ) {
					// show errors
					echo $this->itemForm->isValid($this->getRequest()->getParams());
				} else {
					// grab our data
					$updateItemID = $itemDataArr['id'];
					$newPhotoReference = $this->itemForm->getValue('photo_reference');
					$newStockNumber = $this->itemForm->getValue('stock_number');
					$newDescription = $this->itemForm->getValue('description');
					$newNotes = $this->itemForm->getValue('notes');

					$insertData = array(
						'photo_reference'=>$newPhotoReference,
						'stock_number'=>$newStockNumber,
						'description'=>$newDescription,
						'notes'=>$newNotes
					);
					$where = $this->itemModel->getAdapter()->quoteInto('id = ?', $updateItemID);
					$updateItemID = $this->itemModel->update($insertData, $where);

					// if( $updateItemID ) {
						$this->_redirect("/admin/collections/view/collectionID/$collectionID");
					// } else {
						// array_push($customErrorMessages, 'We were unable to add the collection. Please try again.');
					// }
				}
			}
			
			$this->view->customSuccessMessages = $customSuccessMessages;
			$this->view->customErrorMessages = $customErrorMessages;
		}
		/**
		 * createItemForm - Creates our item form
		 */
		private function createItemForm($itemDataArr)
		{
			// create the contact form
			$this->itemForm = new Zend_Form();
			$this->itemForm
				->setAttribs(array(
					'id'=>'itemForm'
				))
				->setAction('/admin/items/edit/collectionID/'.$this->getRequest()->getParam('collectionID').'/itemID/'.$this->getRequest()->getParam('itemID'))
				->setMethod('post');
			
			// let's create our form elements/inputs
			$this->itemForm->addElements(array(
				array( // the photo reference input
					'text', 'photo_reference', array(
						// 'value' => $itemDataArr['photo_reference'],
						'label' => 'Photo reference *',
						'required' => true,
						'autofocus' => 'autofocus',
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
						// 'value' => $itemDataArr['stock_number'],
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
						// 'value' => $itemDataArr['description'],
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
						// 'value' => $itemDataArr['notes'],
						'label' => 'Notes: ',
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
						'label' => 'Update Item Details',
						'type' => 'submit',
						'name' => 'submitButton'
					)
				)
			));
			
			$this->itemForm->populate($itemDataArr);
			
			// Allow our view access to the form.
			$this->view->itemForm = $this->itemForm;
		}
	}
?>