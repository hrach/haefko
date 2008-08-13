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



require_once dirname(__FILE__) . '/Html.php';
require_once dirname(__FILE__) . '/Form/Controls.php';
require_once dirname(__FILE__) . '/Form/Condition.php';



/**
 * Trida pro tvorbu formularu
 */
class Form implements ArrayAccess
{

	/** @var int */
	private static $counter = 0;

	/** @var array Submitted data */
	public $data;

	/** @var string Jmeno formulare */
	public $name;

	/** @var Html */
	private $form;

	/** @var bool|string Has been form submited? */
	private $submitBy = false;

	/** @var array */
	private $controls = array();

	/** @var array */
	private $errors = array();




	/**
	 * Validate $value by $rule with $arg
	 * @param   string|callback  rule
	 * @param   mixed            value
	 * @param   mixed            additional valdiate argument
	 * @return  bool
	 */
	public static function validate($rule, $value, $argument = null)
	{
		static $rules = array('equal', 'filled', 'numeric', 'length', 'range', 'array', 'email', 'url', 'alfanumeric');

		if (is_string($rule))
			$rule = strtolower($rule);

		if ((is_string($rule) && !in_array($rule, $rules)) || is_callable($rule))
			return call_user_func_array($rule, array($value, $argument));

		switch ($rule) {
			case 'equal':
				return $value == $argument;
			case 'filled':
				return ($value === '0') ? true : !empty($value);
			case 'numeric':
				return is_numeric($value);
			case 'length':
				return strlen($value) == $argument;
			case 'range':
				if (is_string($value))
					$value = strlen($value);
				return $value >= $argument[0] && $value <= $argument[1];
			case 'array':
				return in_array($value, (array) $argument);
			case 'email':
				return preg_match('#^[^@]+@[^@]+\.[a-z]{2,6}$#i', $value);
			case 'url':
				return preg_match('#^.+\.[a-z]{2,6}(\\/.*)?$#i', $value);
			case 'alfanumeric':
				return preg_match('#^[a-z0-9]+$#i', $value);
			default:
				throw new Exception("Unsupported validation rule $rule.");
		}
	}



	/**
	 * Constructor
	 * @param   string  action - url
	 * @param   string  form name
	 * @return  string  form name
	 */
	public function __construct($url = null, $name = 'form')
	{
		if ($name == 'form' && self::$counter++ == 0)
			$this->name = 'form';
		elseif ($name == 'form')
			$this->name = 'form' . self::$counter++;
		else
			$this->name = $name;

		$this->form = Html::element('form');
		$this->form['name'] = $this->name;
		$this->form['method'] = 'post';

		if (!empty($url))
			$this->form['action'] = $url;

		return $this->name;
	}



	/**
	 * Add text input
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addText($control, $label = null)
	{
		$this->controls[$control] = new FormTextControl($this, $control, $label);
		return $this;
	}



	/**
	 * Add textarea input
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addTextarea($control, $label = null)
	{
		$this->controls[$control] = new FormTextareaControl($this, $control, $label);
		return $this;
	}



	/**
	 * Add password input
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addPassword($control, $label = null)
	{
		$this->controls[$control] = new FormPasswordControl($this, $control, $label);
		return $this;
	}



	/**
	 * Add file input
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addFile($control, $label = null)
	{
		$this->form['enctype'] = 'multipart/form-data'; 
		$this->controls[$control] = new FormFileControl($this, $control, $label);
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



	/**
	 * Add select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addSelect($control, $options, $label = null)
	{
		$this->controls[$control] = new FormSelectControl($this, $control, $options, $label, false);
		return $this;
	}



	/**
	 * Add multiple-select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addMultiSelect($control, $options, $label = null)
	{
		$this->controls[$control] = new FormSelectControl($this, $control, $options, $label, true);
		return $this;
	}



	/**
	 * Add single checkbox input
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addSingleCheckbox($control, $label = null)
	{
		$this->controls[$control] = new FormSingleCheckboxControl($this, $control, $label);
		return $this;
	}



	/**
	 * Add checkbox inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addCheckbox($control, $options, $label = null)
	{
		$this->controls[$control] = new FormCheckboxControl($this, $control, $options, $label);
		return $this;
	}



	/**
	 * Add radion inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   string  control label
	 * @return  Form    return $this
	 */
	public function addRadio($control, $options, $label = null)
	{
		$this->controls[$control] = new FormRadioControl($this, $control, $options, $label);
		return $this;
	}



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



	/**
	 * Render form html start tag
	 * @param   bool    render js validation?
	 * @param   array   attributes
	 * @return  string
	 */
	public function start($js = true, $attributes = array())
	{
		$render = '';

		if ($js) {
			$js = $this->name . ' = ' . json_encode($this->validation);
			$render .= "<script type=\"text/javascript\">\n//<![CDATA[\n$js\n//]]>\n</script>";
			$this->form['onsubmit'] = "return formValidate({$this->name});";

			if (class_exists('Application', false))
				Application::getInstance()->controller->view->helper('js')->need('hfvalidate');
		}

		$this->form->setAttributes($attributes);
		return $this->form->renderStart();
	}



	/**
	 * Render form end tag with hidden inputs
	 * @return  string
	 */
	public function end()
	{
		$render = '';
		foreach ($this->controls as $Control) {
			if ($Control instanceof FormHiddenControl)
				$render .= $Control->block();
		}

		$render .= $this->form->renderEnd();
		return $render;
	}



	/**
	 * 
	 * @param   string  jmeno odesilaciho tlacika
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
	 * Zjisti, zda je formaluar validni
	 * @return  bool
	 */
	public function isValid()
	{
		$valid = true;
		foreach ($this->controls as $control) {
			foreach ($control->rules as $rule) {
				if (!$rule->isValid())
					$valid = false;
			}
		}

		return $valid;
	}



	/**
	 * Set default values
	 * @param   array   array $fieldName => $value
	 * @return  void
	 */
	public function setDefaults(array $defaults)
	{
		foreach ($defaults as $id => $value) {
			if (isset($this->controls[$id]))
				$this->controls[$id]->setValue($value);
		}
	}




	/**
	 * Vrati url formulare
	 * @return  string
	 */
	public function getUrl()
	{
		return $this->form['url'];
	}



	/**
	 * Add error message
	 * @param   string  input name
	 * @param   string  error message
	 * @return  void
	 */
	public function addError($id, $message)
	{
		$this->errors[] = array($id, $message);
	}



	/**
	 * Has form any errors?
	 * @return  bool
	 */
	public function hasErrors()
	{
		return count($this->errors) > 0;
	}



	/**
	 * Return array of errors
	 * @return  array
	 */
	public function getErrors()
	{
		return $this->errors;
	}



	/**
	 * Render error list
	 * @return  string
	 */
	public function errorList()
	{
		if (!$this->hasErrors()) return;

		$list = "<ul class=\"errors\">\n";
		foreach ($this->errors as $error) {
			if (!empty($error[0]))
				$list .= "<li><label class=\"error\" for=\"$this->name-$error[0]\">$error[1]</label></li>\n";
			else
				$list .= "<li><label class=\"error\">$error[1]</label></li>\n";
		}
		$list .= "</ul>\n";

		return $list;
	}



	/**
	 * Vyrenderuje zakladni jednoduchou kostru formulare
	 * @return  string
	 */
	public function renderForm()
	{
		echo 'TODO';
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetSet($id, $value)
	{
		throw new Exception("Unsupported method for set the form input Control '$id'.");
	}



	/**
	 * Array-access
	 * @return  FormControl
	 */
	public function offsetGet($id)
	{
		if (isset($this->controls[$id]))
			return $this->controls[$id];
	}



	/**
	 * Array-access
	 * @return  void
	 */
	public function offsetUnset($id)
	{
		if (isset($this->controls[$id]))
			unset($this->controls[$id]);
	}



	/**
	 * Array-access
	 * @return  bool
	 */
	public function offsetExists($id)
	{
		return isset($this->controls[$id]);
	}



	/**
	 * Load submited data
	 * @return  void
	 */
	private function loadData()
	{
		foreach ($this->controls as $id => $control) {
			if ($control instanceof FormFileControl) {
				$this->data[$id] = new FormUploadedFile($control);
			} elseif (isset($_POST[$this->name][$name])) {
				if ($control instanceof FormSubmitControl) {
					$this->submitBy = $name;
				} else {
					$control->setValue($_POST[$this->name][$name]);
					$this->data[$name] = $control->getvalue();
				}
			}
		}
	}



}