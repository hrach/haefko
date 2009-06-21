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


class FormCheckboxControl extends FormInputControl
{

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('checkbox')->class('checkbox');
	}

	public function setValue($value)
	{
		$this->value = (bool) $value;
	}
	
	protected function getControl()
	{
		return parent::getControl()->value(null)->checked($this->value);
	}

}


class FormMultiCheckboxControl extends FormInputControl
{

	public $listSeparator = '<br />';
	protected $options = array();

	public function __construct($form, $name, $options, $label)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
	}

	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl()->type('checkbox')->class('checkbox');
		$control->name .= '[]';
		return $control;
	}

	public function getControl()
	{
		$label = Html::el('label');
		$control = parent::getControl();
		$container = Html::el('div')->id($control->id)->class('multi-inputs');

		$i = 0;
		$id = $control->id;
		foreach ($this->options as $key => $val) {
			$i++;

			$control->id = $id . '-' . $key;
			$control->value = $key;
			$control->checked = in_array($key, (array) $this->getHtmlValue());

			$label->for = $id . '-' . $key;
			$label->setText($val);

			$container->addHtml($control->render()
			                  . $label->render()
			                  . ($this->listSeparator instanceof Html ? $this->listSeparator->render() : $this->listSeparator));
		}

		return $container;
	}


}