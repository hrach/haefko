<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko
 */


class FormRadioControl extends FormInputControl
{

	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
		$this->values = array_keys($options);
	}

	public function control($attrs = array())
	{
		$this->htmlRendered = true;

		$s = '';
		$label = Html::el('label', null, array('class' => 'radio'));
		$el = Html::el('input', null, array('type' => 'radio', 'name' => $this->control->name));

		foreach ($this->options as $key => $value) {
			$el->value($key)
			   ->id("{$this->htmlId}-$key")
			   ->checked($key == $this->getHtmlValue() ? 'checked' : null);

			$label->for("{$this->htmlId}-$key")
			      ->id("{$this->htmlId}-$key-label")
			      ->clearContent()
			      ->setText($value);

			$s .= $el->render() . $label->render() . '<br />';
		}

		return $s;
	}

	protected function prepareLabel()
	{}


}