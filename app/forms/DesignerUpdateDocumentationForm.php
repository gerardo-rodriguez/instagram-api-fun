<?php
class DesignerUpdateDocumentationForm extends Zend_Form
{
	private $config;
	
/*
	public function __construct($config)
	{
		$this->config = $config;
		
		$this->init();
	}
*/
	

    public function init()
    {
		/* Config via ini */
		$this->config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));

        // $this->setName("des");
        $this->setMethod('post');
		$this->setEnctype('multipart/form-data');
             

		$this->addElement('file', 'tax_id_document', array(
			'label' => 'Tax ID Documentation. Accepted formats: .pdf, .doc, .docx, .jpg, .png, .gif.',
			'destination' => APPLICATION_PATH . $this->config->paths->designerDocuments,
			'required' => true,
			'validators' => array(
				array(
					'Count', false, 1
				),
				array(
					'Size', false, $this->config->uploads->maxSize
				),
				array(
					'Extension', false, 'pdf,doc,docx,jpg,png,gif'
				)
			)
		));

		$this->addElement('button', 'submit', array(
			'label' => 'Upload new document',
			'type' => 'submit',
			'name' => 'submitButton'
		));


  		// We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
/*
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
*/
    }
}
?>