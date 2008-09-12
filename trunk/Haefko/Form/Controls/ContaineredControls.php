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


abstract class FormContaineredControl extends FormControl
{

	protected $container = false;

	public function __construct(Form $form, $name, $label = null)
	{
		if (preg_match('#(.+)\[\]$#', $name, $match)) {
			$name = $match[1];
			$this->container = true;
		}

		parent::__construct($form, $name, $label);

		if ($this->container)
			$this->control->name = "{$this->form->name}[$name][]";
	}

	public function setValue($value)
	{
		if (!$this->container)
			return parent::setValue($value);

		$this->value = array();
		foreach ((array) $value as $key => $val) {
			$val = $this->filter($val);
			if (!empty($val))
				$this->value[$key] = $val;
		}
	}

	protected function getControlValue()
	{
		if (!$this->container)
			return parent::getControlValue();

		if (empty($this->value[$this->counter]) && $this->value[$this->counter] !== '0')
			return $this->emptyValue;
		else
			return $this->value[$this->counter];
	}

	public function isValid()
	{
		if (!$this->container)
			return parent::isValid();

		$valid = true;
		foreach ($this->conditions as $condition) {
			$rule = $condition->rule;
			foreach ($rule->control->getValue() as $i => $val) {
				if (Rule::isValid($rule->rule, $val, $rule->arg)) {
					foreach ($condition->rules as $r) {
						if (!Rule::isValid($r->rule, $val, $r->arg)) {
							$rule->control->errors[] = array($i, $this->message($r->rule, $r->message, $r->arg));
							$valid = false;
						}
					}
				}
			}
		}

		foreach ($this->rules as $rule) {
			foreach ($rule->control->getValue() as $i => $val) {
				if (!Rule::isValid($rule->rule, $val, $rule->arg)) {
					$rule->control->errors[] = array($i, $this->message($rule->rule, $rule->message, $rule->arg));
					$valid = false;
				}
			}
		}

		return $valid;
	}

	public function error($asArray = false)
	{
		if (!$this->container)
			return parent::error($asArray);

		$errors = array();
		foreach ($this->errors as $error) {
			if ($this->counter == $error[0])
			$errors[] = "<label class=\"error\" for=\"{$this->id}" . $this->counter(false) . "\">$error[1]</label>";
		}

		if ($asArray)
			return $errors;
		else
			return implode("\n", $errors);
	}

}


abstract class FormInputContaineredControl extends FormContaineredControl
{

	protected $tag = 'input';

	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->value = $this->getControlValue();
		$this->control->type = $this->type;
	}

}


class FormTextareaControl extends FormContaineredControl
{

	protected $tag = 'textarea';
	protected $classes = array('textarea');

	public function prepareControl()
	{
		parent::prepareControl();
		$this->control->setContent($this->getControlValue());
	}

}


class FormTextControl extends FormInputContaineredControl
{

	public $filters = array('trim');
	protected $classes = array('text');
	protected $type = 'text';

}


class FormFileControl extends FormInputContaineredControl
{

	protected $classes = array('file');
	protected $type = 'file';

}


class FormPasswordControl extends FormInputContaineredControl
{

	protected $classes = array('text', 'password');
	protected $type = 'password';

}

class FormSelectControl extends FormContaineredControl
{

	protected $tag = 'select';
	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;

		foreach ($this->options as $key => $option) {
			if (is_array($option)) {
				foreach (array_keys($option) as $val)
					$this->values[] = $val;
			} else {
				$this->values[] = $key;
			}
		}
	}

	public function setValue($value)
	{
		if ($this->container) {
			foreach ($value as $val) {
				if (!$this->isAllowedValue($val))
					return false;
			}
		} else {
			if (!$this->isAllowedValue($value))
				return false;
		}

		parent::setValue($value);
	}

	protected function prepareControl()
	{
		$this->control->setContent($this->getOptions(), false);
	}

	protected function isAllowedValue($value)
	{
		if (!in_array($value, $this->values))
			return false;

		return true;
	}

	protected function getOptions()
	{
		$html = null;
		foreach ($this->options as $name => $value) {

			if (is_array($value)) {
				$html .= "\n<optgroup label=\"$name\">\n";
				foreach ($value as $key => $opt)
					$html .= $this->getOption($key, $opt);
				$html .= "</optgroup>";
			} else {
				$html .= $this->getOption($name, $value);
			}

		}

		return $html;
	}

	protected function getOption($name, $value)
	{
		$el = Html::el('option', $value);
		$el->value = $name;

		if ($this->getControlValue() === $name)
			$el->selected = 'selected';

		return $el->render();
	}

}