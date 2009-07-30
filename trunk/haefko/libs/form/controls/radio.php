<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id$
 * @package     Haefko
 * @subpackage  Forms
 */


class FormRadioControl extends FormInputControl
{

	/** @var string - Control separator */
	public $listSeparator = '<br />';

	/** @var array */
	protected $options = array();

	/** @var array - Options without tree structure */
	protected $values = array();


	/**
	 * Constructor
	 * @param Form $form
	 * @param string $name control name
	 * @param array $options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormRadioControl
	 */
	public function __construct($form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
		$this->values = array_keys($options);
	}


	/**
	 * Returns Html object of form control
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('radio');
	}


	/**
	 * Returns html control
	 * @param mixed $key key name of requested radio
	 * @return Html
	 */
	public function getControl($key = null)
	{
		$label = Html::el('label');
		$radio = parent::getControl();
		if ($key === null)
			$container = Html::el('div')->id($radio->id)->class('multi-inputs');
		elseif (!isset($this->options[$key]))
			return null;

		$id = $radio->id;
		foreach ($this->options as $name => $val) {
			if ($key !== null && $key != $name)
				continue;

			$radio->id = $id . $name;
			$radio->value = $name;
			$radio->checked = (string) $name === $this->getHtmlValue();

			if ($key !== null)
				return $radio;

			$label->for = $id . $name;
			$label->setText($val);
			$container->addHtml($radio->render() . $label->render() . $this->listSeparator);
		}

		return $container;
	}


	/**
	 * Returns html label
	 * @return Html
	 */
	protected function getLabel()
	{
		return parent::getLabel()->for(null);
	}


}