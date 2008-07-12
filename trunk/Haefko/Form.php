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



require_once dirname(__FILE__) . '/Form/Items.php';
require_once dirname(__FILE__) . '/Form/MultiItems.php';
require_once dirname(__FILE__) . '/Form/Condition.php';
require_once dirname(__FILE__) . '/Html.php';



/**
 * Trida pro tvorbu formularu
 */
class Form implements ArrayAccess
{



    public static $counter = 0;

    const
        EQUAL      = 'equal',
        FILLED     = 'filled',
        NOTFILLED  = 'notfilled',
        URL        = 'url',
        EMAIL      = 'email',
        NUMERIC    = 'numeric',
        LENGTH     = 'length',
        MINLENGTH  = 'minlength',
        MAXLENGTH  = 'maxlength',
        INARRAY    = 'inarray',
        NOTINARRAY = 'notinarray';

    public $js = true;
    public $data;
    public $name;

    private $el;
    private $form;
    private $errors = array();
    private $rawValidation;



    /**
     * Kontruktor
     * @param   string  url formulare
     * @param   boole   jedna se o interni url
     * @return  void
     */
    public function __construct($url = null, $internalUrl = true, $name = 'form')
    {
        if ($internalUrl === true)
            $url = Application::getInstance()->controller->url($url);

        if ($name == 'form' && self::$counter == 0)
            $this->name = 'form';
        elseif ($name == 'form')
            $this->name = 'form' . ++self::$counter;
        else
            $this->name = $name;

        $this->form['url'] = $url;
    }



    /**
     * Prida textove vtupni pole
     * @param   string  jmeno elementu
     * @param   bool    viceradkove vstupni pole
     * @return  Form
     */
    public function addText($name, $multiLine = false)
    {
        if ($multiLine)
            $this->form['elements'][$name] = new FormTextareaItem($this, $name);
        else
            $this->form['elements'][$name] = new FormTextItem($this, $name);

        return $this;
    }



    /**
     * Prida chranene textove vstupni pole
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addPassword($name)
    {
        $this->form['elements'][$name] = new FormTextPasswordItem($this, $name);

        return $this;
    }



    /**
     * Prida vstupni pole pro soubor
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addFile($name)
    {
        $this->form['enctype'] = 'multipart/form-data'; 
        $this->form['elements'][$name] = new FormFileItem($this, $name);

        return $this;
    }



    /**
     * Prida skryte textove vstupni pole
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addHidden($name)
    {
        $this->form['elements'][$name] = new FormTextHiddenItem($this, $name);

        return $this;
    }



    /**
     * Prida select vstupni pole
     * @param   string  jmeno elementu
     * @param   array   options
     * @return  Form
     */
    public function addSelect($name, array $options)
    {
        $this->form['elements'][$name] = new FormSelectItem($this, $name, $options);

        return $this;
    }



    /**
     * Prida multiple-select vstupni pole
     * @param   string  jmeno elementu
     * @param   array   options
     * @return  Form
     */
    public function addMultiSelect($name, array $options)
    {
        $this->form['elements'][$name] = new FormMultiSelectItem($this, $name, $options);

        return $this;
    }



    /**
     * Prida zaskrtavaci vstupni pole
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addCheckbox($name)
    {
        $this->form['elements'][$name] = new FormCheckboxItem($this, $name);

        return $this;
    }



    /**
     * Prida zaskrtavaci vstupni pole
     * @param   string  jmeno elementu
     * @param   array   options
     * @return  Form
     */
    public function addMultiCheckbox($name, array $options)
    {
        $this->form['elements'][$name] = new FormMultiCheckboxItem($this, $name, $options);

        return $this;
    }



    /**
     * Prida radio vstupni pole
     * @param   string  jmeno elementu
     * @param   array   options
     * @return  Form
     */
    public function addRadio($name, array $options)
    {
        $this->form['elements'][$name] = new FormRadioItem($this, $name, $options);

        return $this;
    }



    /**
     * Prida odesilaci tlacitko
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addSubmit($name = 'submit')
    {
        $this->form['elements'][$name] = new FormSubmitItem($this, $name);

        return $this;
    }



    /**
     * Prida tlacitko reset
     * @param   string  jmeno elementu
     * @return  Form
     */
    public function addReset($name = 'reset')
    {
        $this->form['elements'][$name] = new FormResetItem($this, $name);

        return $this;
    }



    /**
     * Prida sury js kod do js validacni funkce
     * @param   string  js kod
     */
    public function setRawValidation($js)
    {
        $this->rawValidation = $js;
    }



    /**
     * Vyrenderuje pocatecni tag formulare
     * @param   array   atributy tagu
     * @return  string
     */
    public function start($attrs = array())
    {
        $jsTag              = null;
        $this->el           = Html::element('form');
        $this->el['id']     = $this->name;
        $this->el['action'] = $this->form['url'];
        $this->el['method'] = 'post';

        if (isset($this->form['enctype']))
            $this->el['enctype'] = $this->form['enctype'];

        if ($this->js) {
            $this->el['onsubmit'] = "return validate{$this->name}();";

            $jsValidation = "function validate{$this->name}() {\n"
                          . "$('#{$this->name} label[generated=true]').empty().hide();\n"
                          . $this->rawValidation
                          . "var valid = true;\n";

            foreach ($this->form['elements'] as $item) {
                foreach ($item->conditions as $condition)
                    $jsValidation .= $condition->js();
            }

            $jsValidation .= "return valid;\n}";
            $jsValidation = "<script type=\"text/javascript\">\n//<![CDATA[\n$jsValidation\n//]]>\n</script>\n";

            if (class_exists('Application', false))
                Application::getInstance()->controller->view->helper('js')->need('validate');
        }

        $this->el->setAttributes($attrs);

        return $jsValidation . $this->el->renderStart();
    }



    /**
     * Vyrenderuje uzavirajici tag formulare
     * @return  string
     */
    public function end()
    {
        $hidden = null;

        foreach ($this->form['elements'] as $item) {
            if ($item instanceof FormTextHiddenItem)
                $hidden .= $item->element();
        }

        return $hidden . $this->el->renderEnd();
    }



    /**
     * Zjisti, zda byl formular odeslan skrze $button
     * @param   string  jmeno odesilaciho tlacika
     * @return  bool
     */
    public function isSubmit($button = 'submit')
    {
        foreach ($this->form['elements'] as $name => $item) {
            if ($item instanceof FormFileItem && isset($_FILES[$this->name]['name'][$name]) && $_FILES[$this->name]['error'][$name] != 4) {
                $this->data[$name] = $this->getFileData($name);
                $this->value = & $this->data[$name]['name'];
            } elseif(isset($_POST[$this->name][$name])) {
                $value = $_POST[$this->name][$name];
                if ($item->getEmptyValue() == $value)
                    $value = '';

                $this->data[$name] = $value;
                $item->setValue($this->data[$name]);
            }
        }

        if (isset($this->data[$button])) {
            unset($this->data[$button]);
            return true;
        } else {
            return false;
        }
    }



    /**
     * Zjisti, zda je formaluar validni
     * @return  bool
     */
    public function isValid()
    {
        $return = true;

        foreach ($this->form['elements'] as $item) {
            if (!$item->isValid())
                $return = false;
        }

        return $return;
    }




    /**
     * Vrati odeslana formularova data
     * @return  array
     */
    public function getData()
    {
        return $this->data;
    }



    /**
     * Nastavi vstupnim polim vychozi hodnoty
     * @param   array   pole: array($fieldName => $value);
     * @return  void
     */
    public function setDefaults(array $defaults)
    {
        foreach ($defaults as $name => $value) {
            if (isset($this->form['elements'][$name]))
                $this->form['elements'][$name]->setValue($value);
        }
    }



    /**
     * Vrati url formulare
     * @return  string
     */
    public function getUrl()
    {
        return $this->form['url'];
    }



    /**
     * Prida chybovou zpravu
     * @param   string  zprava
     * @return  void
     */
    public function addError($message, $name = null)
    {
        $this->errors[] = array($name, $message);
    }



    /**
     * Ma formular nejake chyby
     * @return  bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }



    /**
     * Vrati pole s chybovymi zpravami
     * @return  array
     */
    public function getErrors()
    {
        return $this->errors;
    }



    /**
     * Vygeneruje seznam s chybovymi zpravami
     * @return  string
     */
    public function getErrorsList()
    {
        if (!$this->hasErrors()) return;

        $list = "<ul class=\"errors\">\n";
        foreach ($this->errors as $error) {
            if (!empty($error[0]))
                $list .= "<li><label class=\"error\" for=\"$error[0]\">$error[1]</label></li>\n";
            else
                $list .= "<li><label class=\"error\">$error[1]</label></li>\n";
        }
        $list .= "</ul>\n";

        return $list;
    }



    /**
     * Vyrenderuje zakladni jednoduchou kostru formulare
     * @return  string
     */
    public function renderForm()
    {
        $form = $this->start() . "\n<table>";

        foreach ($this->form['elements'] as $name => $el) {
            if ($el instanceof FormTextHiddenItem)
                continue;

            $form .= "\n<tr><td>";

            if ($el instanceof FormSubmitItem || $el instanceof FormResetItem)
                $form .= '</td><td>' . $el->element(ucfirst($name));
            elseif ($el instanceof FormCheckboxItem)
                $form .= '</td><td>' . $el->element() . ' ' . $el->label(ucfirst($name));
            elseif ($el instanceof FormMultiCheckboxItem || $el instanceof FormRadioItem)
                $form .= '</td><td>' . $el->render();
            else
                $form .= $el->label(ucfirst($name)) . '</td><td>' . $el->element();

            $form .= '</td></tr>';
        }

        return $form . "\n</table>" . $this->end();
    }



    /**
     * Array-access pro ulozeni objektu vstupniho pole
     * Nevolejte primo!
     * @return  void
     */
    public function offsetSet($key, $value)
    {
        $this->form['elements'][$key] = $value;
    }



    /**
     * Array-access pro cteni objektu vstupniho pole
     * Nevolejte primo!
     * @return  FormItem
     */
    public function offsetGet($key)
    {
        if (isset($this->form['elements'][$key]))
            return $this->form['elements'][$key];
    }



    /**
     * Array-access pro zruseni objektu vstupniho pole
     * Nevolejte primo!
     * @return  void
     */
    public function offsetUnset($key)
    {
        if (isset($this->form['elements'][$key]))
            unset($this->form['elements'][$key]);
    }



    /**
     * Array-access pro zjiteni existence objektu vstupniho pole
     * Nevolejte primo!
     * @return  void
     */
    public function offsetExists($key)
    {
        return isset($this->form['elements'][$key]);
    }



    /**
     * Automaticky render pri pokusu vypsat objekt
     * @return  string
     */
    public function __toString()
    {
        return $this->renderForm();
    }



    /**
     * Ziska dat uploadnuteho souboru
     * @param   string  name
     * @return  array
     */
    private function getFileData($name)
    {
        $files = & $_FILES[$this->name];
        return array(
            'name' => $files['name'][$name],
            'type' => $files['type'][$name],
            'size' => $files['size'][$name],
            'tmp_name' => $files['tmp_name'][$name],
            'error' => $files['error'][$name]
        );
    }



}