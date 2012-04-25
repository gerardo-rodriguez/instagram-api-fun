<?php
class QuantityForm extends Zend_Form
{
	// private $config;
	

    public function init()
    {
		/* Config via ini */
		// $this->config = new Zend_Config_Ini('../app/configs/config.ini', getenv('APPLICATION_ENVIRONMENT'));

        $this->setMethod('post');
             
        $this->addElement('text', 'quantity', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
				'Digits','Int'
			),
            'required'   => true,
            'label'      => 'Quantity:'
        ));

        $this->addElement('hidden', 'basket_item_id', array(
			'decorators' => array(
				'ViewHelper'
			),
            'filters'    => array('StringTrim'),
            'required'   => true
        ));

        $this->addElement('hidden', 'item_id', array(
			'decorators' => array(
				'ViewHelper'
			),
            'filters'    => array('StringTrim'),
            'required'   => true
        ));

        $this->addElement('hidden', 'form_id', array(
			'decorators' => array(
				'ViewHelper'
			),
            'filters'    => array('StringTrim'),
            'required'   => true
        ));

		$this->addElement('button', 'submit', array(
			'decorators' => array(
				'ViewHelper'
			),
			'label' => 'Update',
			'type' => 'submit',
			'name' => 'updateButton'
		));
    }

	public function setQuantityValue($quantity)
	{
		$this->quantity->setValue($quantity);
	}

	public function setBasketItemID($id)
	{
		$this->basket_item_id->setValue($id);
	}

	public function setItemID($id)
	{
		$this->item_id->setValue($id);
	}

	public function setFormID($id)
	{
		$this->form_id->setValue($id);
	}
}
?>