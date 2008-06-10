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
 * Abstraktni trida pro formularove vstupni pole
 */
abstract class FormItem
{



    public $name;
    public $form;
    public $value;
    public $conditions = array();

    protected $sanitize = false;
    protected $required = false;

    private $empty;


    /**
     * Konstruktor
     * @param   Form    formular
     * @param   string  jmeno elementu
     * @return  void
     */
    public function __construct(Form & $form, $name)
    {
        $this->name = $name;
        $this->form = $form;
    }



    /**
     * Nastavi "prazdnou" hodnotu
     * @param   mixed   vyhozi hodnota
     * @return  void
     */
    public function setEmptyValue($value)
    {
        $this->value = $value;
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
     * Vrati hodnotu vstupniho pole - se zohlednenim na "prazdnou hodnotu"
     * @return  mixed
     */
    public function getValue()
    {
        if (!empty($this->empty) && $this->empty == $this->value) {
            return null;
        } else {
            return $this->value;
        }
    }



    /**
     * Zjisti, zda je vstupni pole validni
     * @return  void
     */
    public function isValid()
    {
        if ($this->sanitize) {
            $this->value = trim($this->value);
        }

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
        $cond = new FormCondition($this, null, null);
        $cond->addRule($rule, $message, $arg);
        $this->conditions[] = $cond;

        if ($rule == Form::FILLED) {
            $this->required = true;
        }

        return $this;
    }



    /**
     * Vrati html tag popisku vstupniho pole
     * @param   string  popisek
     * @param   array   atributy tagu
     * @return  string
     */
    public function label($label, $attrs = array())
    {
        $id = $this->form->name . '-' . $this->name;

        $el = Html::element('label');
        $el['for'] = $id;
        $el['id'] = $id . '-label';

        if ($this->required) {
            $el['class'] .= ' required';
        }

        $el->setContent($label);
        $el->setAttributes($attrs);

        return $el->render();
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
        $el = Html::element('input');
        $el['type'] = 'submit';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['value'] = $value;
        $el['class'] = 'submit';

        $el->setAttributes($attrs);
        return $el->render();
    }



    /**
     * Zjisti, zda je vstupni pole validni
     * @return  void
     */
    public function isValid()
    {
        parent::isValid();
        unset($this->form->data[$this->name]);
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
        $el = Html::element('input');
        $el['type'] = 'text';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['value'] = $this->value;
        $el['class'] = 'text';

        $el->setAttributes($attrs);
        return $el->render();
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
        $el = Html::element('input');
        $el['type'] = 'password';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['class'] = 'text password';

        $el->setAttributes($attrs);
        return $el->render();
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
        $el = Html::element('input');
        $el['type'] = 'hidden';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['value'] = $this->value;


        $el->setAttributes($attrs);
        return $el->render();
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
        $el = Html::element('input');
        $el['type'] = 'file';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['class'] = 'file';


        $el->setAttributes($attrs);
        return $el->render();
    }



}



/**
 * Formularove viceradkove textove pole
 */
class FormTextareaItem extends FormItem
{



    /**
     * Vrati html tag elementu vstupniho pole
     * @param   array   atributy tagu
     * @return  string
     */
    public function element($attrs = array())
    {
        $el = Html::element('textarea');
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el->setContent($this->value);


        $el->setAttributes($attrs);
        return $el->render();
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
        parent::__construct($form, $name);
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
        $el = Html::element('select');
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
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

            if ($this->value == $name) {
                $el['selected'] = 'selected';
            }

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
        $el = Html::element('input');
        $el['type'] = 'checkbox';
        $el['id'] = $this->form->name . '-' . $this->name;
        $el['name'] = "{$this->form->name}[{$this->name}]";
        $el['value'] = 'true';
        $el['class'] = 'checkbox';
        if ($this->value) {
            $el['checked'] = 'checked';
        }


        $el->setAttributes($attrs);
        return $el->render();
    }



}