<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.6
 * @package     Haefko
 */



/**
 * Trida pro podminkovani validace a samotnou validaci
 */
class FormCondition
{



    private $field;
    private $rule;
    private $arg;
    private $rules = array();



    /**
     * Konstruktor
     * @param   FormItem    obejkt vstupniho pole
     * @param   mixed       validacni pravidlo
     * @param   mixrd       argument
     */
    public function __construct(FormItem & $field, $rule, $arg)
    {
        $this->field = $field;
        $this->rule = $rule;
        $this->arg = $arg;
    }



    /**
     * Zjisti zda je vstupni pole validni v zadane podmince
     * @param   mixed   hodnota
     * @return  bool
     */
    public function isValid($value)
    {
        if (!is_null($this->rule)) {
            if (is_object($this->arg)) {
                $arg = $this->arg->value;
            } else {
                $arg = $this->arg;
            }

            if (!$this->valid($this->rule, $value, $this->arg)) {
                return true;
            }
        }

        $valid = true;

        foreach ($this->rules as $rule) {
            if (is_object($rule['arg'])) {
                $arg = $rule['arg']->getValue();
            } else {
                $arg = $rule['arg'];
            }

            if (!$this->valid($rule['rule'], $value, $arg)) {
                $this->field->form->addError($rule['message']);
                $valid = false;
            }
        }

        return $valid;
    }



    /**
     * Prida validacni pravidlo
     * @param   mixed           validacni pravidlo (konstanta / regex)
     * @param   string          chybova zprava
     * @param   mixed           argument
     * @return  FormCondition
     */
    public function addRule($rule, $message, $arg = null)
    {
        $this->rules[] = array(
            'rule'    => $rule,
            'message' => $message,
            'arg'     => $arg,
        );
        
        return $this;
    }



    /**
     * Zvaliduje podle predaneho pravidla predanou hodnotu
     * @param   mixed   pravidlo
     * @param   mixed   hodnota
     * @param   mixed   argument
     * @return  bool
     */
    private function valid($rule, $value, $arg)
    {
        switch ($rule) {
            case Form::EQUAL:       return $value == $arg;
            case Form::FILLED:      return ($value === '0') ? true : !empty($value);
            case Form::EMAIL:       return preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $value);
            case Form::URL:         return preg_match('/^.+\.[a-z]{2,6}(\\/.*)?$/i', $value);
            case Form::NUMERIC:     return is_numeric($value);
            case Form::MINLENGTH:   return strlen($value) >= $arg;
            case Form::MAXLENGTH:   return strlen($value) <= $arg;
            case Form::LENGTH:      return strlen($value) == $arg;
            case Form::INARRAY:     return in_array($value, (array) $arg);
            case Form::NOTINARRAY:  return !in_array($value, (array) $arg);
            default:                return preg_match($rule, $value);
        }
    }



}