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
 * Abstraktni trida pro formularove vstupni pole
 */
abstract class FormItem
{



    public $name;
    public $form;
    public $conditions = array();
    public $el;

    protected $label;
    protected $value;
    protected $empty;
    protected $validation = array();
    protected $sanitize = false;



    /**
     * Konstruktor
     * @param   Form    formular
     * @param   string  jmeno elementu
     * @param   string  tag
     * @return  void
     */
    public function __construct(Form $form, $name, $element = 'input')
    {
        $this->name = $name;
        $this->form = $form;

        $this->el = Html::element($element);
        $this->el['id']   = "{$this->form->name}-{$this->name}";
        $this->el['name'] = "{$this->form->name}[{$this->name}]";
        
        $this->label = Html::element('label');
        $this->label['for']  = $this->el['id'];
        $this->label['id']   = $this->el['id'] . '-label';

    }



    /**
     * Nastavi "prazdnou" hodnotu
     * @param   mixed   vyhozi hodnota
     * @return  void
     */
    public function setEmptyValue($value)
    {
        $this->empty = $value;
    }



    /**
     * Vrati "prazdnou" hodnotu
     * @return  mixed
     */
    public function getEmptyValue()
    {
        return $this->empty;
    }



    /**
     * Nastavi hodnotu vstupniho pole
     * @param   mixed   hodnota
     * @return  void
     */
    public function setValue($value)
    {
        if ($value != $this->empty) {
            if ($this->sanitize && is_string($value)) {
                $value = trim($value);
            }

            $this->value = $value;
        }
    }



    /**
     * Vrati hodnotu vstupniho pole
     * @return  mixed
     */
    public function getValue()
    {
        return $this->value;
    }



    /**
     * Zjisti, zda je vstupni pole validni
     * @return  void
     */
    public function isValid()
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->isValid($this->getValue())) {
                return false;
            }
        }

        return true;
    }




    /**
     * Prida podminuku, pri ktere se bude validovat
     * @param   mixed           validacni pravidlo (konstanta / regex)
     * @param   mixed           argument
     * @return  FormCondition
     */
    public function addCondition($rule, $arg = null)
    {
        return $this->conditions[] = new FormCondition($this, $rule, $arg);
    }



    /**
     * Prida validacni pravidlo
     * @param   mixed       validacni pravidlo (konstanta / regex)
     * @param   string      chybova zprava
     * @param   mixed       argument
     * @return  FormItem
     */
    public function addRule($rule, $message, $arg = null)
    {
        if ($rule == Form::FILLED) {
            $this->label->addClass('required');
        }

        return $this->addCondition(null)->addRule($rule, $message, $arg);
    }



    /**
     * Prida chybovou zpravu pro aktualni vstupni pole
     * @param   string  chybova zprava
     * @return  void
     */
    public function addError($message)
    {
        $this->form->addError($message, $this->el['id']);
    }



    /**
     * Vrati html tag popisku vstupniho pole
     * @param   string  popisek
     * @param   array   atributy tagu
     * @return  string
     */
    public function label($label, $attrs = array())
    {

        $this->label->setAttributes($attrs);
        $this->label->setContent($label);

        return $this->label->render();
    }



}



/**
 * Formularove odesilaci tlacitko
 */
class FormSubmitItem extends FormTextItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   string  popisek
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($value, $attrs = array())
    {
        $this->el['type']  = 'submit';
        $this->el['value'] = $value;

        $this->el->addClass('submit');
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



    /**
     * Zjisti, zda je vstupni pole validni
     * @return  void
     */
    public function isValid()
    {
        unset($this->form->data[$this->name]);
        return true;
    }



}



/**
 * Formularove tlacitko reset
 */
class FormResetItem extends FormItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   string  popisek
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($value, $attrs = array())
    {
        $el = Html::element('input');
        $el['type']  = 'reset';
        $el['value'] = $value;

        $el->addClass('reset');
        $el->setAttributes($attrs);

        return $el->render();
    }



    /**
     * Zjisti, zda je vstupni pole validni
     * @return  void
     */
    public function isValid()
    {
        return true;
    }



}



/**
 * Formularove textove pole
 */
class FormTextItem extends FormItem
{



    protected $sanitize = true;



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el['type']  = 'text';
        $this->el['value'] = empty($this->value) ? $this->empty : $this->value;

        $this->el->addClass('text');
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}



/**
 * Formularove textove pole s heslem
 */
class FormTextPasswordItem extends FormItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el['type'] = 'password';

        $this->el->addClass('text', 'password');
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}



/**
 * Formularove skryte pole
 */
class FormTextHiddenItem extends FormItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el['type']  = 'hidden';
        $this->el['value'] = $this->value;

        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}



/**
 * Formularove pole pro upload souboru
 */
class FormFileItem extends FormItem
{



    protected $sanitize = true;



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el['type'] = 'file';

        $this->el->addClass('file');
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}



/**
 * Formularove viceradkove textove pole
 */
class FormTextareaItem extends FormItem
{



    /**
     * Konstruktor
     * @param   Form    formular
     * @param   string  jmeno elementu
     * @return  void
     */
    public function __construct(Form $form, $name)
    {
        parent::__construct($form, $name, 'textarea');
    }



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el->setContent(empty($this->value) ? $this->empty : $this->value);
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}



/**
 * Formularove vyberove pole
 */
class FormSelectItem extends FormItem
{



    protected $options = array();



    /**
     * Konstruktor
     * @param   Form    formular
     * @param   string  jmeno elementu
     * @param   array   options
     * @return  void
     */
    public function __construct($form, $name, array $options)
    {
        parent::__construct($form, $name, 'select');
        $this->options = $options;
    }



    /**
     * Zjisti, zda je hodnota vstupniho pole validni
     * @return  void
     */
    public function isValid()
    {
        if (!$this->isValueValid()) {
            $this->value = null;
        }

        return parent::isValid();
    }



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el->setAttributes($attrs);
        $this->el->setContent($this->factoryOptions(), true);

        return $this->el->render();
    }



    /**
     * Vygeneruje html options tagu
     * @return  string
     */
    protected function factoryOptions()
    {
        $html = null;

        foreach ($this->options as $name => $value) {
            $el = Html::element('option');
            $el['value'] = $name;
            $el->setContent($value);

            if ($this->value == $name)
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
        return isset($this->options[$this->value]);
    }



}



/**
 * Formularove zaskrtavaci pole
 */
class FormCheckBoxItem extends FormItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $this->el['type']  = 'checkbox';
        $this->el['value'] = 'true';

        if ($this->value)
            $el['checked'] = 'checked';

        $this->el->addClass('checkbox');
        $this->el->setAttributes($attrs);

        return $this->el->render();
    }



}