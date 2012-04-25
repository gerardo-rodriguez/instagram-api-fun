<?php
class DesignerUpdatePasswordForm extends Zend_Form
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
         
        $this->addElement('password', 'current_password', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(5, 50))
            ),
            'required'   => true,
			'renderPassword' => true,
            'label'      => 'Current Password *'
        ));

        $this->addElement('password', 'new_password', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(5, 50))
            ),
            'required'   => true,
			'renderPassword' => true,
            'label'      => 'New Password *'
        ));

        $this->addElement('password', 'new_password_confirm', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(5, 50)),
				array('Identical', false, array('token' => 'new_password'))
            ),
            'required'   => true,
			'renderPassword' => true,
            'label'      => 'Confirm New Password *'
        ));

		$this->addElement('button', 'submit', array(
			'label' => 'Update Password',
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