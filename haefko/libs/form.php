<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Forms
 */


require_once dirname(__FILE__) . '/tools.php';
require_once dirname(__FILE__) . '/html.php';
require_once dirname(__FILE__) . '/object.php';

require_once dirname(__FILE__) . '/form/rule.php';
require_once dirname(__FILE__) . '/form/condition.php';
require_once dirname(__FILE__) . '/form/controls.php';


class Form extends Object implements ArrayAccess, IteratorAggregate
{


	/** @var string - Name of control with hash */
	public static $SECURITY_CONTROL = 'csrf_protection';

	/** @var array - Submitted data */
	public $data = array();

	/** @var string - Form name */
	public $name;

	/** @var array - Rules without control */
	public $rules = array();

	/** @var string|FormRenderer */
	public $renderer = 'dl';

	/** @var Html */
	private $form;

	/** @var bool|string - Submit button name */
	private $submitBy = false;

	/** @var array */
	private $controls = array();

	/** @var array */
	private $errors = array();

	/** @var bool - Is form CSRF protected? */
	private $protected = false;


	/**
	 * Constructor
	 * @param   string  url
	 * @param   string  form name
	 * @param   string  method
	 * @return  string  form name
	 */
	public function __construct($url = '', $name = 'form', $method = 'post')
	{
		# application url proccesing
		if (class_exists('Application', false))
			$url = call_user_func_array(array(Controller::get(), 'url'), (array) (empty($url) ? '<:url:>' : $url));


		static $counter = 0;
		if ($name == 'form' && $counter++ == 0)
			$this->name = 'form';
		elseif ($name == 'form')
			$this->name = 'form' . $counter++;
		else
			$this->name = $name;

		$this->form = Html::el('form', null, array(
			'id' => $this->name,
			'method' => $method,
			'action' => $url
		));

		
		return $this->name;
	}


	/* ========== Controls ========== */


	/**
	 * Adds CSRF protection
	 * @param   string  error message
	 * @return  Form    $this
	 */
	public function addProtection($errorMessage = 'Security token did not match - possible CSRF attack!')
	{
		if (!class_exists('Session'))
			throw new Exception('Form protection works only with Session class.');


		# control
		$this->protected = true;
		$this->controls[self::$SECURITY_CONTROL] = new FormHiddenControl($this, self::$SECURITY_CONTROL);


		if (Session::exists('CSRF.protection.' . $this->name)) {
			$hash = Session::read('CSRF.protection.' . $this->name);
		} else {
			$hash = md5(Session::getName());
			Session::write($key, $hash);
		}


		$this->controls[self::$SECURITY_CONTROL]->setValue($hash);
		$this->rules[] = new Rule($this->controls[self::$SECURITY_CONTROL], Form::EQUAL, $hash, $message);
		return $this;
	}


	/**
	 * Adds text input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addText($control, $label = null)
	{
		$this[$control] = new FormTextControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds textarea input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addTextarea($control, $label = null)
	{
		$this[$control] = new FormTextareaControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds password input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addPassword($control, $label = null)
	{
		$this[$control] = new FormPasswordControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds datepicker input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addDatepicker($control, $label = null)
	{
		$this[$control] = new FormDatepickerControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds file input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addFile($control, $label = null)
	{
		$this->form->enctype = 'multipart/form-data'; 
		$this[$control] = new FormFileControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addSelect($control, $options, $label = null)
	{
		$this[$control] = new FormSelectControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds checkbox input
	 * @param   string  control name
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addCheckbox($control, $label = null)
	{
		$this[$control] = new FormCheckboxControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds radio inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addRadio($control, $options, $label = null)
	{
		$this[$control] = new FormRadioControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds hidden input
	 * @param   string  control name
	 * @return  Form    $this
	 */
	public function addHidden($control)
	{
		$this[$control] = new FormHiddenControl($this, $control);
		return $this;
	}


	/* ========== Multi Controls ========== */


	/**
	 * Adds multiple select input
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addMultiSelect($control, $options, $label = null)
	{
		$this[$control] = new FormMultipleSelectControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds multi checkbox inputs
	 * @param   string  control name
	 * @param   array   options
	 * @param   mixed   label (null = from name, false = no label)
	 * @return  Form    $this
	 */
	public function addMultiCheckbox($control, $options, $label = null)
	{
		$this[$control] = new FormMultiCheckboxControl($this, $control, $options, $label);
		return $this;
	}


	/* ========== Button Controls ========== */


	/**
	 * Adds submit button
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    $this
	 */
	public function addSubmit($control = 'submit', $label = null)
	{
		$this[$control] = new FormSubmitControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds image submit button
	 * @param   string  control name
	 * @param   string  image src
	 * @return  Form    $this
	 */
	public function addImageSubmit($control = 'submit', $src = null)
	{
		$this[$control] = new FormImageSubmitControl($this, $control, $src);
		return $this;
	}


	/**
	 * Adds reset button
	 * @param   string  control name
	 * @param   string  control label
	 * @return  Form    $this
	 */
	public function addReset($control = 'reset', $label = null)
	{
		$this[$control] = new FormResetControl($this, $control, $label);
		return $this;
	}


	/* ========== Mehtods ========== */


	/**
	 * Renders form html start tag
	 * @todo    Javascript validation
	 * @param   array   attributes
	 * @return  string
	 */
	public function startTag($attrs = array())
	{
		return $this->form->setAttrs($attrs)->startTag();
	}


	/**
	 * Renders form end tag with hidden inputs
	 * @return  string
	 */
	public function endTag()
	{
		$render = '';
		foreach ($this->controls as /** @var FormControl */$control) {
			if ($control instanceof FormHiddenControl && !$control->isRendered())
				$render .= $control->control();
		}

		$render .= $this->form->endTag();
		return $render;
	}


	/**
	 * Returns true/false if the form has been submitted
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
	 * Returns true/false if the form is valid
	 * @return  bool
	 */
	public function isValid()
	{
		$valid = true;

		foreach ($this->rules as $rule) {
			if (!$rule->isValid())
				$valid = false;
		}

		foreach ($this->controls as $control) {
			if (!$control->isValid())
				$valid = false;
		}

		return $valid;
	}


	/**
	 * Checks whether form has errors
	 * @return  bool
	 */
	public function hasErrors()
	{
		foreach ($this->controls as $control) {
			if ($control->hasError())
				return true;
		}

		return false;
	}


	/**
	 * Sets default values for controls (only if the form is not submitted)
	 * @param   array   values - format is array with $controlName => $value
	 * @return  void
	 */
	public function setDefaults($defaults, $checkSubmitted = true)
	{
		if ($checkSubmitted && $this->isSubmit())
			return;

		foreach ((array) $defaults as $id => $value) {
			if (isset($this->controls[$id]))
				$this->controls[$id]->setValue($value);
		}
	}


	/**
	 * Returns form url
	 * @return  string
	 */
	public function getUrl()
	{
		return $this->form->action;
	}


	/**
	 * Returns form
	 * @return  Html
	 */
	public function getForm()
	{
		return $this->form;
	}


	/**
	 * Loads renderer class
	 * @param   string    renderer name
	 * @return  Renderer
	 */
	public function renderer($name)
	{
		require_once dirname(__FILE__) . '/form/irenderer.php';
		require_once dirname(__FILE__) . '/form/renderer.php';
		require_once dirname(__FILE__) . '/form/renderers/' . Tools::dash($name) . '.php';

		$name = "Form{$name}Renderer";
		$this->renderer = new $name($this);
		return $this->renderer;
	}


	/**
	 * Renders form controls and tags
	 * @param   string  render part
	 * @param   mixed   arg n0
	 * @return  string
	 */
	public function render($part = 'form')
	{
		if (!($this->renderer instanceof FormRenderer)) {
			if (empty($this->renderer))
				throw new Exception('Define renderer name.');

			$this->renderer($this->renderer);
		}

		$attrs = func_get_args();
		array_shift($attrs);
		return $this->renderer->render($part, $attrs);
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetSet($id, $value)
	{
		$this->controls[$id] = $value;
		
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
	 * ArrayIterator interface
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->controls);
	}


	/**
	 * To string interface
	 * @return  string
	 */
	public function __toString()
	{
		try {
			$render = $this->render();
		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $render;
	}


	/**
	 * Loads submited data into the form
	 * @return  void
	 */
	private function loadData()
	{
		foreach ($this->controls as $id => $control) {
			if ($control instanceof FormFileControl && isset($_FILES[$this->name]['name'][$id])) {
				$this->data[$id] = new FormUploadedFile($control, $_FILES[$this->name]);
				$control->setValue($this->data[$id]->name);
			} elseif (isset($_POST[$this->name][$id])) {
				if ($control instanceof FormSubmitControl) {
					$this->submitBy = $id;
				} else {
					$control->setValue($_POST[$this->name][$id]);
					$this->data[$id] = $control->getValue();
				}
			}
		}

		if ($this->protected) {
			unset($this->data[self::$SECURITY_CONTROL]);
			Session::delete('CSRF.protection.' . $this->name);
		}
	}


}