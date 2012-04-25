<?php

//Modify include path to library
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '../library');

define( 'APPLICATION_PATH', realpath(dirname(__FILE__)) );

//Zend Framework Includes
require_once "Zend/Loader.php";

Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Controller_Exception');
Zend_Loader::loadClass('Zend_Config');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table_Abstract');
// require_once('../library/Zend/Db/Table/Abstract.php');
Zend_Loader::loadClass('Zend_Controller_Action_Helper_Abstract');
Zend_Loader::loadClass('Zend_Controller_Action_HelperBroker');
Zend_Loader::loadClass('Zend_View_Helper_Abstract');
Zend_Loader::loadClass('Zend_View');
Zend_Loader::loadClass('Zend_Session');
Zend_Loader::loadClass('Zend_Session_Namespace');
Zend_Loader::loadClass('Zend_Layout');

Zend_Loader::loadClass('Zend_Controller_Router_Rewrite');
Zend_Loader::loadClass('Zend_Form');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
Zend_Loader::loadClass('Zend_Auth_Storage_Session');
Zend_Loader::loadClass('Zend_Mail');

Zend_Loader::loadClass('Zend_Validate_NotEmpty');

//StartMVC
$mvc_options = array(
    'layout'     => 'default',
    'layoutPath' => '../app/layouts',
);
$layout = Zend_Layout::startMvc($mvc_options);

/* Config via ini */
$config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));

/* Error Reporting */
$errors = $config->errors->toArray();
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', (int)$errors['display']);

//Start Session
Zend_Session::start();

//Set Database
$dbAdapter = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);

//Register Helpers with Brokers
Zend_Controller_Action_HelperBroker::addPath('../app/helpers/actions', 'Helper_');

//Set View Helpers
$view = new Zend_View();
$view->addHelperPath('../app/helpers/views/', 'View_Helper_');			
$renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
$renderer->setView($view);

//Get Front Controller
$front = Zend_Controller_Front::getInstance();

//Set Controllers
// $front->setControllerDirectory('../app/controllers');
//Setting up modules
$front->addModuleDirectory('../app/modules');
$front->setDefaultModule('default');

$front->throwExceptions((int)$errors['throwExceptions']);
$front->dispatch();
?>