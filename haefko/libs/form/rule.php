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


class Rule
{


	/** @var mixed */
	public $this;

	/** @var FormControl */
	public $control;

	/** @var mixed */
	public $arg;

	/** @var array */
	public $thiss = array();

	/** @var bool */
	public $isNegative = false;

	/** @var array Default messages */
	public static $messages = array(
		Form::EQUAL => 'Value must be equal to "%s".',
		Form::FILLED => 'Value is required.',
		Form::NUMERIC => 'Value must be numeric.',
		Form::LENGTH => 'Value must have length %d',
		Form::RANGE => 'Value must be in range %d - %d.',
		Form::INARRAY => 'Value must be from list %a',
		Form::REGEXP => 'Value must passed by regular expression (%s).',
		Form::URL => 'Value must be valid URL.',
		Form::EMAIL => 'Value must be valid email.',
		Form::ALFANUMERIC => 'Value must be alfa-numeric.'
	);


	/**
	 * Method validate $value by $rule with $arg
	 * @static
	 * @param   string  validation rule name
	 * @param   mixed   value for validation
	 * @param   mixed   argument for validation
	 * @$valid =  bool
	 */
	public static function isValid($rule, $value, $arg = null)
	{
		if ($arg instanceof FormControl)
			$arg = $arg->getValue();

		if (ord($rule[0]) > 127) {
			$rule = ~$rule;
			$negative = true;
		} else {
			$negative = false;
		}

		switch ($rule) {
		case 'equal':
			$valid = $value == $arg;
			break;
		case 'filled':
			$valid = ($value === '0') ? true : !empty($value);
			break;
		case 'numeric':
			$valid = is_numeric($value);
			break;
		case 'length':
			$value = strlen($value);
		case 'range':
			if (is_array($arg) && count($arg) == 2)
				$valid = $value >= $arg[0] && $value <= $arg[1];
			else
				$valid = $value == $arg;
			break;
		case 'inarray':
			$valid = in_array($value, (array) $arg);
			break;
		case 'email':
			$valid = preg_match('#^[^@]+@[^@]+\.[a-z]{2,6}$#i', $value);
			break;
		case 'url':
			$valid = preg_match('#^.+\.[a-z]{2,6}(\\/.*)?$#i', $value);
			break;
		case 'alfanumeric':
			$valid = preg_match('#^[a-z0-9]+$#i', $value);
			break;
		default:
			if (is_callable($rule))
				$valid = call_user_func_array($rule, array($value, $arg));
			else
				throw new Exception("Unsupported validation rule $rule.");
		}

		if ($negative)
			return !$valid;
		else
			return $valid;
	}


}