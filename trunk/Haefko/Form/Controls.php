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



/**
 * Abstraktni trida pro formularove vstupni pole
 */
abstract class FormControl
{

	/** @var mixed */
	public $emptyValue;



	/** @var Html */
	protected $control;

	/** @var Html|bool */
	protected $label;



	/** @var string */
	protected $tag = 'input';

	/** @var string */
	protected $name;

	/** @var Form */
	protected $form;

	/** @var array */
	protected $classes = array();

	/** @var array */
	protected $filters = array();



	/** @var array */
	private $rules = array();

	/** @var mixed */
	private $value;



	/**
	 * Constructor
	 * @param   Form    form
	 * @param   string  control name
	 * @param   string  tag name
	 * @return  void
	 */
	public function __construct(Form $form, $name, $label = null)
	{
		$this->name = $name;
		$this->form = $form;

		$this->control = Html::element($this->tag);
		$this->control['id'] = "{$this->form->name}-{$this->name}";
		$this->control['name'] = "{$this->form->name}[{$this->name}]";

		if ($label === false) {
			$this->label = false;
		} else {
			if (is_null($label))
				$label = ucfirst($name);

			$this->label = Html::element('label');
			$this->label->setContent($label);
			$this->label['for'] = $this->control['id'];
			$this->label['id'] = $this->control['id'] . '-label';
		}
	}



	/**
	 * Set the control value
	 * @param   mixed   new value
	 * @return  bool
	 */
	public function setValue($value)
	{
		foreach ($this->filters as $filter)
			$value = (string) call_user_func($filter, $value);

		$this->value = $value;
	}



	/**
	 * Return value
	 * @return  mixed
	 */
	public function getValue()
	{
		return $this->value;
	}



	/**
	 * Add rule to input element
	 * @param   string|callback  rule name or callback
	 * @param   mixed            additional validation argument
	 * @param   string           error message
	 * @return  FormItem         return $this
	 */
	public function addRule($rule, $argument = null, $message = null)
	{
		$this->rules['empty'] = new FormCondition($this);
		return $this;
	}



	/**
	 * Add condition to input element
	 * @param   string|callback  rule name or callback
	 * @param   mixed            additional validation argument
	 * @return  FormCondition
	 */
	public function addCondition($rule, $argument = null)
	{
		$this->rules[] = new FormCondition($this, $rule, $argument);
		return end($this->rules);
	}



	/**
	 * Render control block - label and control tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function block($attributes = array())
	{
		$div = Html::element('div');
		$div->setAttributes($attributes);
		$div->addClass($this->classes);

		if ($this->label === false)
			$div->setContent($this->control(), true);
		else
			$div->setContent($this->label() . $this->control(), true);

		return $div->render();
	}



	/**
	 * Render label tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function label($attributes = array())
	{
		$this->label->setAttributes($attributes);
		return $this->label->render();
	}



	/**
	 * Render html input tag
	 * @param   array   attributes
	 * @return  string
	 */
	public function control($attributes = array())
	{
		$this->control['value'] = $this->getControlValue();
		$this->control->setAttributes($attributes);
		return $this->control->render();
	}



	/**
	 * Interface
	 */
	public function __get($name)
	{
		if (in_array($name, array('label', 'control', 'block'))) {
			return $this->{$name}();
		} else {
			throw new Exception("from item error $name");
		}
	}



	/**
	 * Return default value for control
	 * @return  string
	 */
	protected function getControlValue()
	{
		if (empty($this->value) && $this->value !== '0')
			return $this->emptyValue;
		else
			return $this->value;
	}



}



abstract class FormInputControl extends FormControl
{

	protected $type = 'text';

	public function control($attributes = array())
	{
		$this->control['type'] = $this->type;
		return parent::control($attributes);
	}

}



class FormTextControl extends FormInputControl
{

	protected $filters = array('trim');
	protected $classes = array('text');
	protected $type = 'text';

}



abstract class FormButtonControl extends FormInputControl
{

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->control['value'] = $label;
	}

}



class FormSubmitControl extends FormButtonControl
{

	protected $classes = array('submit');
	protected $type = 'submit';

}



class FormResetControl extends FormButtonControl
{

	protected $classes = array('reset');
	protected $type = 'reset';

}



class FormPasswordControl extends FormControl
{

	protected $classes = array('text', 'password');

	public function control($attributes = array())
	{
		$this->control['type'] = 'password';
		$this->control->setAttributes($attributes);
		return $this->control->render();
	}

}



class FormHiddenControl extends FormInputControl
{

	protected $type = 'hidden';

	public function __construct(Form $form, $name, $label)
	{
		parent::__construct($form, $name, false);
	}

}



class FormFileControl extends FormInputControl
{

	protected $classes = array('file');
	protected $type = 'file';

}



class FormTextareaControl extends FormControl
{

	protected $tag = 'textarea';
	protected $classes = array('textarea');

	public function control($attributes = array())
	{
		$this->control->setContent($this->getControlValue());
		$this->control->setAttributes($attributes);

		return $this->control->render();
	}

}



class FormSelectControl extends FormControl
{

	protected $options = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, 'select', $label);
		$this->options = $options;
	}

	public function control(array $attrs = array())
	{
		$this->control->setAttributes($attrs);
		$this->control->setContent($this->factoryOptions(), true);

		return $this->control->render();
	}

	private function factoryOptions()
	{
		$html = null;

		foreach ($this->options as $name => $value) {
			$el = Html::element('option');
			$el['value'] = $name;
			$el->setContent($value);

			if ($this->getControlValue() == $name)
				$el['selected'] = 'selected';

			$html .= $el->render();
		}

		return $html;
	}

}



class FormCheckboxItem extends FormControl
{

	public function setValue($value)
	{
		$this->value = (bool) $value;
	}

	public function element(array $attrs = array())
	{
		$this->control['type']  = 'checkbox';
		$this->control['value'] = 'true';

		if ($this->getValue())
			$this->control['checked'] = 'checked';

		$this->control->addClass('checkbox');
		$this->control->setAttributes($attrs);

		return $this->control->render();
	}

}






/* =================================================== */
/**
 * Formularove vicevyberove zaskrtavaci pole
 */
class FormMultiCheckboxItem extends FormControl
{



	private $options = array();



	/**
	 * Konstruktor
	 * @param   Form    formular
	 * @param   string  jmeno elementu
	 * @param   array   options
	 * @return  void
	 */
	public function __construct($form, $name, array $options)
	{
		parent::__construct($form, $name);
		$this->options = $options;
	}



	/**
	 * @throw   Exception
	 */
	public function label()
	{
		throw new Exception('Label neni u FormMultiCheckbox povolen!');
	}



	/**
	 * Vygeneruje html tagy podle predaneho vzoroveho retezce
	 * @param   string  retezec - pouzijte meta-sekvence {element} a {label}
	 * @return  string
	 */
	public function render($string = '{element} {label}<br/>')
	{
		$html = '';

		foreach ($this->options as $name => $value) {
			$iname = $this->form->name . '-' . $this->name . '-' . $name;

			$el = Html::element('input');
			$el['type'] = 'checkbox';
			$el['id'] = $iname;
			$el['name'] = "{$this->form->name}[{$this->name}][]";
			$el['value'] = $name;

			if (is_array($this->getControlValue()) && in_array($name, $this->getControlValue()))
				$el['checked'] = 'checked';

			$label = Html::element('label');
			$label['for'] = $iname;
			$label['id'] = $iname . '-label';
			$label->setContent($value);

			$html .= str_replace(array('{label}', '{element}'), array($label->render(), $el->render()), $string);
		}

		return $html;
	}



}



/**
 * Formularove vicevyberove pole
 */
class FormMultiSelectItem extends FormSelectControl
{



	public $value = array();



	/**
	 * Vrati html tag elementu vstupniho pole
	 * @param   array   atributy tagu
	 * @return  string
	 */
	public function element($attrs = array())
	{
		$el = Html::element('select');
		$el['id'] = $this->form->name . '-' . $this->name;
		$el['multiple'] = 'multiple';
		$el['name'] = "{$this->form->name}[{$this->name}][]";
		$el->setContent($this->factoryOptions(), true);

		return $el->render();
	}



	/**
	 * Vygeneruje html options tagu
	 * @return  string
	 */
	protected function factoryOptions()
	{
		$html = '';

		foreach ($this->options as $name => $value) {
			$el = Html::element('option');
			$el['value'] = $name;
			$el->setContent($value);

			if (in_array($name, $this->getControlValue()))
				$el['selected'] = 'selected';

			$html .= $el->render();
		}

		return $html;
	}



	/**
	 * Je hodnota pripustna
	 * @return  bool
	 */
	protected function isValueValid()
	{
		foreach ($this->getControlValue() as $key) {
			if (!isset($this->options[$key]))
				return false;
		}

		return true;
	}



}



/**
 * Formularove vicevyberove pole
 */
class FormRadioItem extends FormControl
{



	private $options = array();



	/**
	 * Konstruktor
	 * @param   Form    formular
	 * @param   string  jmeno elementu
	 * @param   array   options
	 * @return  void
	 */
	public function __construct($form, $name, array $options)
	{
		parent::__construct($form, $name);
		$this->options = $options;
	}



	/**
	 * @throw   Exception
	 */
	public function label()
	{
		throw new Exception('Label neni u FormRadioItem povolen!');
	}



	/**
	 * Vygeneruje html tagy podle predaneho vzoroveho retezce
	 * @param   string  retezec - pouzijte meta-sekvence {element} a {label}
	 * @return  string
	 */
	public function render($string = '{element} {label}<br/>')
	{
		$html = '';

		foreach ($this->options as $name => $value) {
			$iname = $this->form->name . '-' . $this->name . '-' . $name;

			$el = Html::element('input');
			$el['type'] = 'radio';
			$el['id'] = $iname;
			$el['name'] = "{$this->form->name}[{$this->name}]";
			$el['value'] = $name;

			if ($name == $this->value)
				$el['checked'] = 'checked';

			$label = Html::element('label');
			$label['for'] = $iname;
			$label['id'] = $iname . '-label';
			$label->setContent($value);

			$html .= str_replace(array('{label}', '{element}'), array($label->render(), $el->render()), $string);
		}

		return $html;
	}



}