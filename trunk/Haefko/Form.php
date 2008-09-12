<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */


require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/Html.php';

require_once dirname(__FILE__) . '/Form/Rule.php';
require_once dirname(__FILE__) . '/Form/Condition.php';

require_once dirname(__FILE__) . '/Form/Controls/FormControl.php';
require_once dirname(__FILE__) . '/Form/Controls/Controls.php';
require_once dirname(__FILE__) . '/Form/Controls/ContaineredControls.php';
require_once dirname(__FILE__) . '/Form/Controls/MultiControls.php';


/**
 * Trida pro tvorbu formularu
 */
class Form implements ArrayAccess
{


	/** @var int */
	const
		EQUAL        = 'equal',
		FILLED       = 'filled',
		NUMERIC      = 'numeric',
		LENGTH       = 'length',
		RANGE        = 'range',
		INARRAY      = 'inarray',
		REGEXP       = 'regexp',
		URL          = 'url',
		EMAIL        = 'email',
		ALFANUMERIC  = 'alfanumeric';

	/** @var array Submitted data */
	public $data;

	/** @var string Form name */
	public $name;

	/** @var Html */
	private $form;

	/** @var bool|string Submit button name */
	private $submitBy = false;

	/** @var array */
	private $controls = array();

	/** @var array */
	private $errors = array();


	/**
	 * Constructor
	 * @param   string  action - url
	 * @param   string  form name
	 * @return  string  form name
	 */
	public function __construct($url = '', $method = 'post', $name = 'form')
	{
		static $counter = 0;

		if ($name == 'form' && $counter++ == 0)
			$this->name = 'form';
		elseif ($name == 'form')
			$this->name = 'form' . $counter++;
		else
			$this->name = $name;

		$this->form = Html::el('form', array(
			'name' => $this->name,
			'method' => 'post',
			'action' => $url
		));

		return $this->name;
	}


	/* ======== Containered Controls ======== */


	/**
	 * Add text input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addText($control, $label = null)
	{
		$this->controls[strRightTrim($control, '[]')] = new FormTextControl($this, $control, $label);
		return $this;
	}


	/**
	 * Add textarea input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addTextarea($control, $label = null)
	{
		$this->controls[strRightTrim($control, '[]')] = new FormTextareaControl($this, $control, $label);
		return $this;
	}


	/**
	 * Add password input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addPassword($control, $label = null)
	{
		$this->controls[strRightTrim($control, '[]')] = new FormPasswordControl($this, $control, $label);
		return $this;
	}


	/**
	 * Add file input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addFile($control, $label = null)
	{
		$this->form['enctype'] = 'multipart/form-data'; 
		$this->controls[strRightTrim($control, '[]')] = new FormFileControl($this, $control, $label);
		return $this;
	}


	/**
	 * Add select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addSelect($control, $options, $label = null)
	{
		$this->controls[strRightTrim($control, '[]')] = new FormSelectControl($this, $control, $options, $label);
		return $this;
	}


	/* ======== Controls ======== */


	/**
	 * Add checkbox input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addCheckbox($control, $label = null)
	{
		$this->controls[$control] = new FormCheckboxControl($this, $control);
		return $this;
	}


	/**
	 * Add radio inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addRadio($control, $options, $label = null)
	{
		$this->controls[$control] = new FormRadioControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Add hidden input
	 * @param   string  control name
	 * @return  Form    return $this
	 */
	public function addHidden($control)
	{
		$this->controls[$control] = new FormHiddenControl($this, $control);
		return $this;
	}


	/* ======== Multi Controls ======== */


	/**
	 * Add multiple select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addMultiSelect($control, $options, $label = null)
	{
		$this->controls[$control] = new FormMultipleSelectControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Add multi checkbox inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    return $this
	 */
	public function addMultiCheckbox($control, $options, $label = null)
	{
		$this->controls[$control] = new FormMultiCheckboxControl($this, $control, $options, $label);
		return $this;
	}


	/* ======== Button Controls ======== */


	/**
	 * Add submit button
	 * @param   string  control label
	 * @param   string  control name
	 * @return  Form    return $this
	 */
	public function addSubmit($label = null, $control = 'submit')
	{
		$this->controls[$control] = new FormSubmitControl($this, $control, $label);
		return $this;
	}


	/**
	 * Add image submit button
	 * @param   string  image src
	 * @param   string  control name
	 * @return  Form    return $this
	 */
	public function addImageSubmit($src = null, $control = 'submit')
	{
		$this->controls[$control] = new FormImageSubmitControl($this, $control, $src);
		return $this;
	}


	/**
	 * Add reset button
	 * @param   string  control label
	 * @param   string  control name
	 * @return  Form    return $this
	 */
	public function addReset($label = null, $control = 'reset')
	{
		$this->controls[$control] = new FormResetControl($this, $control, $label);
		return $this;
	}


	/* ======== Mehtods ======== */


	/**
	 * Render form html start tag
	 * @todo    Javascript validation
	 * @param   bool    render js validation?
	 * @param   array   attributes
	 * @return  string
	 */
	public function startTag($js = true, $attrs = array())
	{
		$this->form->setAttributes($attrs);
		return $this->form->startTag();
	}


	/**
	 * Render form end tag with hidden inputs
	 * @return  string
	 */
	public function endTag()
	{
		$render = '';
		foreach ($this->controls as $control) {
			if ($control instanceof FormHiddenControl)
				$render .= $control->block();
		}

		$render .= $this->form->endTag();
		return $render;
	}


	/**
	 * Return true/false if the form has been submitted
	 * Arguments: no submit button name = check only if form has been submitted
	 *            buton name/names = check if form has been submitted by button/buttons
	 * @param   string  button name
	 * @return  bool
	 */
	public function isSubmit()
	{
		if (empty($this->data))
			$this->loadData();

		$buttons = func_get_args();
		if (empty($buttons))
			return (bool) $this->submitBy;
		else
			return in_array($this->submitBy, $buttons);
	}


	/**
	 * Return true/false if the form is valid
	 * @return  bool
	 */
	public function isValid()
	{
		$valid = true;
		foreach ($this->controls as $control) {
			if (!$control->isValid())
				$valid = false;
		}

		return $valid;
	}


	/**
	 * Set default values for controls
	 * @param   array   values - format is array with $controlName => $value
	 * @return  void
	 */
	public function setDefaults($defaults)
	{
		foreach ((array) $defaults as $id => $value) {
			if (isset($this->controls[$id]))
				$this->controls[$id]->setValue($value);
		}
	}


	/**
	 * Return form url
	 * @return  string
	 */
	public function getUrl()
	{
		return $this->form['url'];
	}


	/**
	 * Render form controls and tags
	 * @return  string
	 */
	public function render()
	{
		$r = $this->startTag();

		foreach ($this->controls as $control) {
			if (!($control instanceof FormHiddenItem))
				$r .= $control->block();
		}

		$r .= $this->endTag();
		return $r;
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetSet($id, $value)
	{
		throw new Exception("Unsupported acces to form definition. Use methods add*.");
	}


	/**
	 * Array-access interface
	 * @return  FormControl
	 */
	public function offsetGet($id)
	{
		if (isset($this->controls[$id]))
			return $this->controls[$id];
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetUnset($id)
	{
		if (isset($this->controls[$id]))
			unset($this->controls[$id]);
	}


	/**
	 * Array-access interface
	 * @return  bool
	 */
	public function offsetExists($id)
	{
		return isset($this->controls[$id]);
	}


	/**
	 * Magic method
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}


	/**
	 * Load submited data into the form
	 * @return  void
	 */
	private function loadData()
	{
		foreach ($this->controls as $id => $control) {
			if ($control instanceof FormFileControl) {
				$this->data[$id] = new FormUploadedFile($control);
			} elseif (isset($_POST[$this->name][$id])) {
				if ($control instanceof FormSubmitControl) {
					$this->submitBy = $id;
				} else {
					$control->setValue($_POST[$this->name][$id]);
					$this->data[$id] = $control->getvalue();
				}
			}
		}
	}


}