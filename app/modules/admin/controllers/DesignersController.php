<?php
	//-------------------------------------------------
	// Imports
	//-------------------------------------------------
	require_once( '../app/modules/admin/models/DesignerModel.php' );

	/**
	 * Admin Designers Controller
	 *
	 * The controller for the Admin Designers view.
	 *  
	 * @author Gerardo Rodriguez
	 * @created 01/09/2012
	 */
	class Admin_DesignersController extends Zend_Controller_Action
	{
		private $designerModel;
		private $redirector;
		private $config;
		private $mailHelper;
		
		public function init() {
			//$this->session_alert = new Zend_Session_Namespace('');

			// if not logged in, redirect to the login form
			$authHelper = $this->_helper->getHelper('Authenticate');
			if( !$authHelper->isAuthenticated('Admin_Session_Namespace') ) $this->_helper->getHelper('Redirector')->gotoSimple('index','index','admin');

			// our models
			$this->designerModel = new DesignerModel();
			
			//Sets alternative layout
			$this->_helper->layout->setLayout('admin_logged_in');
			
			//Access to helpers
			$this->redirector = $this->_helper->getHelper('Redirector');
			$this->mailHelper = $this->_helper->getHelper('Mail');

			/* Config via ini */
			$this->config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));
		}
		//-------------------------------------------------
		// Public Methods
		//-------------------------------------------------
		public function indexAction()
		{
			// fetch and pass along to the view the pending deisgners from the db
			$this->view->pendingDesignersDataArr = $this->designerModel->fetchDesigners('pending')->toArray();
			// fetch and pass along to the view the approved deisgners from the db
			$this->view->approvedDesignersDataArr = $this->designerModel->fetchDesigners('approved')->toArray();
			// fetch and pass along to the view the denied deisgners from the db
			$this->view->deniedDesignersDataArr = $this->designerModel->fetchDesigners('denied')->toArray();
		}
		
		public function viewAction()
		{
			// let's grab the designer id
			$designerID = $this->getRequest()->getParam('designerID');

			// Let's grab the data for the designer
			$designerDataArr = $this->designerModel->fetchDesignerData($designerID)->toArray();
			$this->view->designerDataArr = $designerDataArr[0];
		}
		
		public function approveAction()
		{
			$status = 'approved';
			
			// let's grab the designer id
			$designerID = $this->getRequest()->getParam('designerID');
			// udpate the designer status
			$this->designerModel->updateDesignerStatus($status,$designerID);
			// grab the designer data
			$designerData = $this->designerModel->fetchDesignerData($designerID)->toArray();
			$designerData = $designerData[0];
			
			// send an email to the designer
			$this->sendDesignerEmail($status, $designerData);
			
			$this->redirector->gotoSimple('index','designers','admin');
		}
		
		public function denyAction()
		{
			$status = 'denied';
			
			// let's grab the designer id
			$designerID = $this->getRequest()->getParam('designerID');
			// update the status
			$this->designerModel->updateDesignerStatus($status,$designerID);
			// grab the designer data
			$designerData = $this->designerModel->fetchDesignerData($designerID)->toArray();
			$designerData = $designerData[0];
			
			// send an email to the designer
			$this->sendDesignerEmail($status, $designerData);
			
			$this->redirector->gotoSimple('index','designers','admin');
		}
		//-------------------------------------------------
		// Private Methods
		//-------------------------------------------------
		/**
		 * private sendDesignerEmail - Will send an email to the designer after registering.
		 */
		private function sendDesignerEmail($status, $designerData)
		{
			// grab the current logged in user data
			$adminDetails = Zend_Auth::getInstance()->getStorage()->read();
			
			$senderName = $adminDetails->first_name . " " . $adminDetails->last_name;
			$senderEmail = $adminDetails->email;

			$recipientName = $designerData['owner_first_name'] . " " . $designerData['owner_last_name'];
			$recipientEmail = $designerData['email'];
			

			// create view object for the email
			$html = new Zend_View();
			$html->setScriptPath(APPLICATION_PATH . '/../app/templates/email/');

			// assign valeues
			$html->assign('name', $recipientName);
			$html->assign('status', $status);
			$html->assign('loginURL', $this->config->urls->designer);

			// render view
			$body = $html->render('toDesigner_statusUpdateEmail.phtml');


			$newDesignerMessageData = array(
				'sender' => array(
					'email' => $senderEmail,
					'name' => $senderName
				),
				'recipient' => array(
					'email' => $recipientEmail,
					'name' => $recipientName
				),
				'email' => array(
					'subject' => 'Your Isabella Collection application has been reviewed.',
					'body' => $body
				)
			);
			$this->mailHelper->sendMessage($newDesignerMessageData);
		}
	}
	
?>