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
 * FormCondition
 */
class FormCondition
{

	/** @var bool|array */
	private $condition = array();

	/** @var array */
	private $rules = array();



	/**
	 * Constructor
	 * @param   Form
	 * @return  void
	 */
	public function __construct(& $element, $rule, $argument = null)
	{
		$this->condition = array($element, $rule, $argument);
	}



	/**
	 * Check condition a validate rules
	 * @return  bool
	 */
	public function isValid()
	{
		if (!Form::validate($this->contion[1], $this->contion[0]->getValue(), $this->contion[2]))
			return true;

		$valid = true;
		foreach ($this->rules as $rule) {
			if (!Form::validate($rule[1], $rule[0]->getValue(), $rule[2])) {
				$rule[0]->addError($rule[3]);
				$valid = false;
			}
		}

		return $valid;
	}


	/**
	 * Add rule to form contol
	 * @param   string|callback  rule name or callback
	 * @param   mixed            additional validation argument
	 * @param   string           error message
	 * @return  FormCondition    return $this
	 */
	public function addRule($element, $rule, $argument = null, $message = null)
	{
		$this->rules[] = array($element, $rule, $argument, $message);
		return $this;
	}




	/**
	 * Vrati javascriptovou validaci pro aktualni podminku
	 * @return  string
	 */
	public function js()
	{
		if (empty($this->rules)) return;

		$js = null;
		$id = $this->field->el['id'];
		$value = ($this->field instanceof FormCheckBoxItem) ? "$('#$id').attr('checked')" : "$('#$id').val()";


		foreach ($this->rules as $item) {
			if ($this->field instanceof FormPasswordItem && $item['rule'] == 'equal' && is_string($item['arg']))
				continue;

			$rule = ($this->field instanceof FormCheckBoxItem) ? 'expression' : $item['rule'];
			$arg = $this->jsFieldArg($item['rule'], $item['arg']);
			$js .= "if (!HFisValid('$rule', $value, $arg)) { valid = false; HFcreateErrorLabel('$id', '" . addslashes($item['message']) . "'); }\n";
		}


		if (!is_null($this->rule)) {
			$rule = ($this->field instanceof FormCheckBoxItem) ? 'expression' : $this->rule;
			$arg = $this->jsFieldArg($this->rule, $this->arg);
			$js = "if (HFisValid('$rule', $value, $arg)) { $js }\n";
		}


		return $js;
	}



	/**
	 * Vrati js vyraz pro argument v zavislosti na podmince a typu predaneho argumentu
	 * @param   string  podminka
	 * @param   mixed   argument
	 * @return  string
	 */
	private function jsFieldArg($rule, $arg)
	{
		if (in_array($rule, array('filled', 'notfilled'))) {
			return "'{$this->field->getEmptyValue()}'";
		} else {
			if ($arg instanceof FormItem)
				return "$('#{$arg->el['id']}').val()";
			elseif (is_array($arg))
				return toJsArray($arg);
			else
				return "'$arg'";
		}
	}







}