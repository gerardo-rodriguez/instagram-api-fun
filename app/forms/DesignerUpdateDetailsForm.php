<?php
class DesignerUpdateDetailsForm extends Zend_Form
{
	private $statesArr = array(
		'AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming"
		);
	
	private $radioGroupArr = array(
		"fax" => "I will fax it.",
		"attach" => "I will upload a copy."
	);
	
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
		// $this->setEnctype('multipart/form-data');
             
        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
				'EmailAddress'/*
				,
								array('Db_NoRecordExists', false, array(
									'table' => 'admin',
									'field' => 'email',
									'exclude' => array(
										'field' => 'id', 
										'value' => $this->request->get('id')
									)
								))*/
				
			),
            'required'   => true,
            'label'      => 'Email *'
        ));

/*
        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(5, 50))
            ),
            'required'   => false,
			'renderPassword' => true,
            'label'      => 'Password '
        ));

        $this->addElement('password', 'password_confirm', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(5, 50)),
				array('Identical', false, array('token' => 'password'))
            ),
            'required'   => false,
			'renderPassword' => true,
            'label'      => 'Confirm Password '
        ));
*/

        $this->addElement('text', 'business_name', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Business Name *'
        ));

        $this->addElement('text', 'owner_first_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				// array('Alpha', array('allowWhiteSpace'=>true))
            ),
            'required'   => true,
            'label'      => 'Owner First Name *'
        ));

        $this->addElement('text', 'owner_last_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				// array('Alpha', array('allowWhiteSpace'=>true))
            ),
            'required'   => true,
            'label'      => 'Owner Last Name *'
        ));

        $this->addElement('text', 'address', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Address *'
        ));

        $this->addElement('text', 'city', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'City *'
        ));

        $this->addElement('select', 'state', array(
            'filters'    => array(),
			'multiOptions' => $this->statesArr,
            'required'   => true,
            'label'      => 'State *'
        ));

        $this->addElement('text', 'zip', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				array('PostCode', false, array('locale' => 'en_US'))
            ),
            'required'   => true,
            'label'      => 'Zip Code *'
        ));

        $this->addElement('text', 'phone_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				// 'Digits'
            ),
            'required'   => true,
            'label'      => 'Phone Number *'
        ));

        $this->addElement('text', 'fax_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				// 'EmailAddress'
            ),
            'required'   => false,
            'label'      => 'Fax Number'
        ));

/*
		$this->addElement('radio', 'tax_id_delivery', array(
			'required' => 'true',
			'multiOptions' => $this->radioGroupArr,
			'label' => 'A Tax ID is required. You can upload a new document here if you have not done so already.'
		));
*/

/*
		$this->addElement('file', 'tax_id_document', array(
			'label' => 'Tax ID Documentation. Accepted formats: .pdf, .doc, .docx, .jpg, .png, .gif. (Required if you chose "I will upload a copy" above. *)',
			'destination' => APPLICATION_PATH . $this->config->paths->designerDocuments,
			'required' => false,
			'validators' => array(
				array(
					'Count', false, 1
				),
				array(
					'Size', false, 7372800 //900 kb
				),
				array(
					'Extension', false, 'pdf,doc,docx,jpg,png,gif'
				)
			)
		));
*/

		$this->addElement('button', 'submit', array(
			'label' => 'Update Details',
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