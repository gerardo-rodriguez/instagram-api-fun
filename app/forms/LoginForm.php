<?php
class LoginForm extends Zend_Form
{
    public function init()
    {
        // $this->setName("designerForm");
        $this->setMethod('post');
             
        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                // array('StringLength', false, array(0, 50)),
				'EmailAddress'
            ),
            'required'   => true,
            'label'      => 'Email *',
        ));

        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            // 'validators' => array(
            //     array('StringLength', false, array(0, 50)),
            // ),
            'required'   => true,
            'label'      => 'Password *',
        ));

		$this->addElement('button', 'submit', array(
			'label' => 'login',
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