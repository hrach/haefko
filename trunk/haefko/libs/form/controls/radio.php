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


class FormRadioControl extends FormInputControl
{

	public $listSeparator = '<br />';
	protected $options = array();
	protected $values = array();

	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
		$this->values = array_keys($options);
	}

	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('radio');
	}

	protected function getControl()
	{
		$label = Html::el('label');
		$radio = parent::getControl();
		$container = Html::el('div')->id($radio->id)->class('multi-inputs');

		$i = 0;
		$id = $radio->id;
		foreach ($this->options as $key => $val) {
			$i++;

			$radio->id = $id . $i;
			$radio->value = $key;
			$radio->checked = (string) $key === $this->getHtmlValue();

			$label->for = $id . $i;
			$label->setText($val);

			$container->addHtml($radio->render()
			                  . $label->render()
			                  . ($this->listSeparator instanceof Html ? $this->listSeparator->render() : $this->listSeparator));
		}

		return $container;
	}

	protected function getLabel()
	{
		return parent::getLabel()->for(null);
	}

}