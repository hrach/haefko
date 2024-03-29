<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Forms
 */


require_once dirname(__FILE__) . '/tools.php';
require_once dirname(__FILE__) . '/html.php';
require_once dirname(__FILE__) . '/http.php';
require_once dirname(__FILE__) . '/object.php';

require_once dirname(__FILE__) . '/form/rule.php';
require_once dirname(__FILE__) . '/form/condition.php';
require_once dirname(__FILE__) . '/form/controls/controls.php';


/**
 * Form class
 * Creates, observes, renders html forms
 * @property-read IFormRenderer $renderer
 */
class Form extends Object implements ArrayAccess, IteratorAggregate
{

	/** @var string - Name of control with hash */
	public static $SECURITY_CONTROL = 'csrf_protection';

	/** @var array - Submitted data */
	public $data = array();

	/** @var string - Form name */
	public $name;

	/** @var Html */
	private $form;

	/** @var bool|string - Submit button name */
	private $submitBy = false;

	/** @var array */
	private $controls = array();

	/** @var bool - Is form CSRF protected? */
	private $protected = false;

	/** @var FormRenderer */
	private $renderer;


	/**
	 * Constructor
	 * @param string $url
	 * @param string $name form name
	 * @param string $method form method
	 * @return Form
	 */
	public function __construct($url = null, $name = null, $method = 'post')
	{
		# application url proccesing
		if (class_exists('Application', false) && !empty($url))
			$url = call_user_func(array(Controller::get(), 'url'), $url);

		if (empty($name))
			$name = 'form';

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
	}


	/* ========== Controls ========== */


	/**
	 * Adds CSRF protection
	 * @param string $errorMessage
	 * @return Form
	 */
	public function addProtection($errorMessage = 'Security token did not match - possible CSRF attack!')
	{
		if (!class_exists('Session'))
			throw new Exception('Form protection needs loaded Session class.');

		$this->protected = true;
		$this->controls[self::$SECURITY_CONTROL] = new FormHiddenControl($this, self::$SECURITY_CONTROL);

		$session = Session::getNamespace('Form.csrf-protection');
		$key = $this->name;

		if ($session->exists($key)) {
			$hash = $session->get($key);
		} else {
			$hash = md5(Session::getName());
			$session->set($key, $hash);
		}

		$this->controls[self::$SECURITY_CONTROL]->setValue($hash);
		$this->controls[self::$SECURITY_CONTROL]->addRule(Rule::EQUAL, $hash, $errorMessage);
		return $this;
	}


	/**
	 * Adds text input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addText($control, $label = null)
	{
		$this[$control] = new FormTextControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds textarea input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addTextarea($control, $label = null)
	{
		$this[$control] = new FormTextareaControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds password input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addPassword($control, $label = null)
	{
		$this[$control] = new FormPasswordControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds datepicker input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addDatepicker($control, $label = null)
	{
		$this[$control] = new FormDatepickerControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds file input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addFile($control, $label = null)
	{
		$this->form->enctype = 'multipart/form-data';
		$this[$control] = new FormFileControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds select input
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addSelect($control, $options, $label = null)
	{
		$this[$control] = new FormSelectControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds checkbox input
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addCheckbox($control, $label = null)
	{
		$this[$control] = new FormCheckboxControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds radio inputs
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addRadio($control, $options, $label = null)
	{
		$this[$control] = new FormRadioControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds hidden input
	 * @param string $control control name
	 * @return Form
	 */
	public function addHidden($control)
	{
		$this[$control] = new FormHiddenControl($this, $control);
		return $this;
	}


	/* ========== Multi Controls ========== */


	/**
	 * Adds multiple select input
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addMultiSelect($control, $options, $label = null)
	{
		$this[$control] = new FormMultipleSelectControl($this, $control, $options, $label);
		return $this;
	}


	/**
	 * Adds multi checkbox inputs
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addMultiCheckbox($control, $options, $label = null)
	{
		$this[$control] = new FormMultiCheckboxControl($this, $control, $options, $label);
		return $this;
	}


	/* ========== Button Controls ========== */


	/**
	 * Adds submit button
	 * @param string $control control name
	 * @param string $control control  label
	 * @return Form
	 */
	public function addSubmit($control = 'submit', $label = null)
	{
		$this[$control] = new FormSubmitControl($this, $control, $label);
		return $this;
	}


	/**
	 * Adds image submit button
	 * @param string $control control name
	 * @param   string  image src
	 * @return Form
	 */
	public function addImageSubmit($control = 'submit', $src = null)
	{
		$this[$control] = new FormImageSubmitControl($this, $control, $src);
		return $this;
	}


	/**
	 * Adds reset button
	 * @param string $control control name
	 * @param string $control control  label
	 * @return Form
	 */
	public function addReset($control = 'reset', $label = null)
	{
		$this[$control] = new FormResetControl($this, $control, $label);
		return $this;
	}


	/* ========== Mehtods ========== */


	/**
	 * Renders form html start tag
	 * @param array $attrs attributes
	 * @return string
	 */
	public function startTag($attrs = array())
	{
		return $this->form->setAttrs($attrs)->startTag();
	}


	/**
	 * Renders form end tag with hidden inputs
	 * @return string
	 */
	public function endTag()
	{
		$render = '';
		foreach ($this->controls as /** @var FormControl */$control) {
			if ($control instanceof FormHiddenControl && !$control->isRendered())
				$render .= $control->control() . $control->error();
		}

		$render .= $this->form->endTag();
		return $render;
	}


	/**
	 * Returns true/false if the form has been submitted
	 * Arguments: no submit button name = check only if form has been submitted
	 *            buton name/names = check if form has been submitted by button/buttons
	 * @param string $name button name
	 * @return bool
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
	 * Returns true if the form is valid
	 * @return bool
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
	 * Checks whether form has errors
	 * @return bool
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
	 * @param array $defaults default values - format is array with $controlName => $value
	 * @param bool $checkSubmitted shoul this method chech if form is submitted?
	 * @return Form
	 */
	public function setDefaults($defaults, $checkSubmitted = true)
	{
		if ($checkSubmitted && $this->isSubmit())
			return $this;

		foreach ((array) $defaults as $id => $value) {
			if (isset($this->controls[$id]))
				$this->controls[$id]->setValue($value);
		}

		return $this;
	}


	/**
	 * Sets / creates renderer instance
	 * @param IFormRenderer|string $renderer renderer name
	 * @throws Exception
	 * @return Form
	 */
	public function setRenderer($renderer)
	{
		if (is_object($renderer)) {
			if (!($renderer instanceof IFormRenderer))
				throw new Exception('Renderer must be instance of IFormRenderer.');

			$this->renderer = $renderer;
		} else {
			$name = Tools::dash($renderer);
			require_once dirname(__FILE__) . "/form/renderers/form-$name-renderer.php";
			$class= "Form{$renderer}Renderer";
			$this->renderer = new $class();
		}

		$this->renderer->setForm($this);
		return $this;
	}


	/**
	 * Returns form
	 * @return Html
	 */
	public function getForm()
	{
		return $this->form;
	}


	/**
	 * Returns Renderer
	 * @return IFormRenderer
	 */
	public function getRenderer()
	{
		if (!($this->renderer instanceof IFormRenderer))
			$this->setRenderer('table');

		return $this->renderer;
	}


	/**
	 * Array-access interface
	 */
	public function offsetSet($id, $value)
	{
		$this->controls[$id] = $value;
	}


	/**
	 * Array-access interface
	 * @return FormControl
	 */
	public function offsetGet($id)
	{
		if (isset($this->controls[$id]))
			return $this->controls[$id];

		throw new Exception("Undefined form control with name '$id'.");
	}


	/**
	 * Array-access interface
	 * @throws Exception
	 */
	public function offsetUnset($id)
	{
		if (isset($this->controls[$id]))
			unset($this->controls[$id]);

		throw new Exception("Undefined form control with name '$id'.");
	}


	/**
	 * Array-access interface
	 * @return bool
	 */
	public function offsetExists($id)
	{
		return isset($this->controls[$id]);
	}


	/**
	 * ArrayIterator interface
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->controls);
	}


	/**
	 * toString interface
	 * @return string
	 */
	public function __toString()
	{
		try {
			if (!($this->renderer instanceof IFormRenderer))
				$this->getRenderer();

			$render = $this->renderer->render();
		} catch (Exception $e) {
			return $e->getMessage();
		}

		return $render;
	}


	/**
	 * Loads submited data into the form
	 * @return Form
	 */
	private function loadData()
	{
		$data = Http::$request->getForm();
		if (isset($data[$this->name]))
			$data = $data[$this->name];
		else
			return $this;

		foreach ($this->controls as $id => $control) {
			if (!isset($data[$id])) {
				if ($control instanceof FormMultiCheckboxControl || $control instanceof FormMultipleSelectControl)
					$data[$id] = array();
				else
					continue;
			}

			if ($control instanceof FormFileControl) {
				$this->data[$id] = new FormUploadedFile($control, $data[$id]);
				$control->setValue($this->data[$id]->name);
			} elseif ($control instanceof FormSubmitControl) {
				$this->submitBy = $id;
			} else {
				$control->setValue($data[$id]);
				$this->data[$id] = $control->getValue();
			}
		}

		if ($this->protected) {
			unset($this->data[self::$SECURITY_CONTROL]);
			$session = Session::getNamespace('Form.csrf-protection');
			$session->delete($this->name);
		}

		return $this;
	}


}