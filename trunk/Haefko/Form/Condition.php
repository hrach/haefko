<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
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
                $this->field->addError($rule['message']);
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
            $js .= "if (!HFisValid('$rule', $value, $arg)) { valid = false; HFcreateErrorLabel('$id', '$item[message]'); }\n";
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
            if ($arg instanceof FormItem) {
                return "$('#{$arg->el['id']}').val()";
            } elseif (is_array($arg)) {
                return toJsArray($arg);
            } else {
                return "'$arg'";
            }
        }
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
            case Form::EQUAL:      return $value == $arg;
            case Form::FILLED:     return ($value === '0') ? true : !empty($value);
            case Form::NOTFILLED:  return ($value === '0') ? false : empty($value);
            case Form::EMAIL:      return preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $value);
            case Form::URL:        return preg_match('/^.+\.[a-z]{2,6}(\\/.*)?$/i', $value);
            case Form::NUMERIC:    return is_numeric($value);
            case Form::MINLENGTH:  return strlen($value) >= $arg;
            case Form::MAXLENGTH:  return strlen($value) <= $arg;
            case Form::LENGTH:     return strlen($value) == $arg;
            case Form::INARRAY:    return in_array($value, (array) $arg);
            case Form::NOTINARRAY: return !in_array($value, (array) $arg);
            default:               return preg_match($rule, $value);
        }
    }



}