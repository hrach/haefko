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
 * Formularove vicevyberove zaskrtavaci pole
 */
class FormMultiCheckboxItem extends FormItem
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
            if (is_array($this->value) && in_array($name, $this->value)) {
                $el['checked'] = 'checked';
            }

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
class FormMultiSelectItem extends FormSelectItem
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

            if (in_array($name, $this->value)) {
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
        foreach ($this->value as $key) {
            if (!isset($this->options[$key])) {
                return false;
            }
        }

        return true;
    }



}



/**
 * Formularove vicevyberove pole
 */
class FormRadioItem extends FormItem
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
            if ($name == $this->value) {
                $el['checked'] = 'checked';
            }

            $label = Html::element('label');
            $label['for'] = $iname;
            $label['id'] = $iname . '-label';
            $label->setContent($value);

            $html .= str_replace(array('{label}', '{element}'), array($label->render(), $el->render()), $string);
        }

        return $html;
    }



}