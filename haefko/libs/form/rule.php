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


class Rule extends Object
{


	/** @var FormControl */
	public $control;

	/** @var mixed */
	public $arg;

	/** @var string */
	public $message;

	/** @var string */
	public $rule;

	/** @var array Default messages */
	public static $messages = array(
		Form::EQUAL => 'Value must be equal to "%s".',
		Form::FILLED => 'Value is required.',
		Form::NUMERIC => 'Value must be numeric.',
		Form::INTEGER => 'Value must be integer number.',
		Form::LENGTH => 'Value must have length %d',
		Form::RANGE => 'Value must be in range %d - %d.',
		Form::INARRAY => 'Value must be from list %a',
		Form::REGEXP => 'Value must passed by regular expression (%s).',
		Form::URL => 'Value must be valid URL.',
		Form::EMAIL => 'Value must be valid email.',
		Form::ALFANUMERIC => 'Value must be alfa-numeric.'
	);


	public function __construct($control, $rule, $arg = null, $message = null)
	{
		dump($this->control);
		$this->control = $control;
		$this->rule = $rule;
		$this->arg = $arg;
		$this->message = $message;
	}
	
	
	public function isValid()
	{
		$valid = self::validate($this->rule, $this->control->getValue(), $this->arg);
		if ($valid)
			return true;

		$this->control->addAutoError($this->rule, $this->arg, $this->message);
		return false;
	}


	/**
	 * Method validate $value by $rule with $arg
	 * @static
	 * @param   string  validation rule name
	 * @param   mixed   value for validation
	 * @param   mixed   argument for validation
	 * @$valid =  bool
	 */
	public static function validate($rule, $value, $arg = null)
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
		case Form::EQUAL:
			$valid = $value == $arg;
			break;
		case Form::FILLED:
			$valid = ($value === '0') ? true : !empty($value);
			break;
		case Form::NUMERIC:
			$valid = is_numeric($value);
			break;
		case Form::INTEGER:
			$valid = preg_match('#^\d*$#', $value);
			break;
		case Form::LENGTH:
			$value = strlen($value);
		case Form::RANGE:
			if (is_array($arg) && count($arg) == 2)
				$valid = $value >= $arg[0] && $value <= $arg[1];
			else
				$valid = $value == $arg;
			break;
		case Form::INARRAY:
			$valid = in_array($value, (array) $arg);
			break;
		case Form::REGEXP:
			$valid = preg_match($arg, $value);
			break;
		case Form::EMAIL:
			$valid = preg_match('#^[^@\s]+@[^@\s]+\.[a-z]{2,10}$$#i', $value);
			break;
		case Form::URL:
			$valid = preg_match('#^.+\.[a-z]{2,6}(\\/.*)?$#i', $value);
			break;
		case Form::ALFANUMERIC:
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