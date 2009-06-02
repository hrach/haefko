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


require_once dirname(__FILE__) . '/irenderer.php';
require_once dirname(__FILE__) . '/renderers/javascript/jquery-validator.php';


abstract class FormRenderer extends Object implements IFormRenderer
{

	/** @var IFormJsValidator */
	public $javascript = null;
	
	public $wrappers = array(
		'part' => null,
		'pair' => null,
		'label' => null,
		'control' => null,
		'button-separator' => null,
		'list-separator' => 'br',
	);

	
	protected $form;

	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct(Form $form)
	{
		$this->form = $form;
		$this->javascript = new JqueryFormJsValidator();
		$this->javascript->name = $this->form->name;
	}


	/**
	 * Renders form $part
	 * @param   string  part
	 * @param   string  title
	 * @param   array   attributes
	 * @return  string
	 */
	public function render($part, $attrs)
	{
		switch ($part) {
			case 'form':
				return $this->renderForm($attrs);
			case 'start':
				return $this->renderStart($attrs);
			case 'end':
				return $this->renderEnd();
			case 'part':
				return $this->renderPart(array_shift($attrs), array_shift($attrs), array_shift($attrs));
			case 'block':
				return $this->renderBlock($attrs);
			default:
				throw new Exception("Unknow type $part of render method.");
		}
	}


	/**
	 * Renders form
	 * @return  string
	 */
	protected function renderForm()
	{
		return $this->renderStart()
		     . $this->renderPart()
		     . $this->renderEnd();
	}


	/**
	 * Renders form start tag
	 * @param   array   html attributes
	 * @return  string
	 */
	protected function renderStart()
	{
		return $this->form->startTag();
	}


	/**
	 * Renders form end tag
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderEnd()
	{
		return $this->form->endTag() . "\n";
	}


	/**
	 * Renders body
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderPart($controls = array(), $heading = '', $attrs = array())
	{
		$partW = $this->preparePart($this->getWrapper('part'), $heading);
		$partW->setAttrs($attrs);


		foreach ($this->form as $name => /** @var FormControl */$control) {
			if (empty($controls) && $control->isRendered())
				continue;

			if (!empty($controls) && !in_array($control->name, $controls))
				continue;

			if ($control instanceof FormHiddenControl)
				continue;


			if ($control instanceof FormButtonControl) {
				$controlW = $this->getWrapper('control');
				foreach ($this->form as $control) {
					if ($control instanceof FormButtonControl && ((in_array($control->name, $controls) && !empty($controls)) || empty($controls)))
						$controlW->addHtml($control->control()->render())
						         ->addHtml($this->getWrapper('button-separator')->render());
				}

				$pairW = $this->preparePair($this->getWrapper('pair'));
				$pairW->addHtml($this->getWrapper('label'))
				      ->addHtml($controlW);

				$partW->addHtml($pairW);
			} else {
				$partW->addHtml($this->renderPair($name));
			}

			if ($this->javascript instanceof IFormJsValidator) {
				foreach ($control->getRules() as $rule)
					$this->javascript->addRule($rule);

				foreach ($control->getConditions() as $condition)
					$this->javascript->addCondition($condition);
			}
		}

		return $partW->render(0) . $this->javascript->getCode();
	}


	/**
	 * Renders block of control and label
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderPair($name)
	{
		if (!isset($this->form[$name]))
			throw new Exception('Undefined form control in render-block.');

		$pairW = $this->preparePair($this->getWrapper('pair'));
		$pairW->addHtml($this->renderLabel($name))
		      ->addHtml($this->renderControl($name));

		return $pairW->render(0);
	}


	/**
	 * Renders control
	 * @param   string  control name
	 * @return  string
	 */
	protected function renderControl($name)
	{
		$control = $this->form[$name];
		$controlW = $this->prepareControl($this->getWrapper('control'));

		if ($control instanceof FormRadioControl || $control instanceof FormMultiCheckboxControl) {
			foreach ($control->control() as $item)
				$controlW->addHtml($item)
				         ->addHtml($this->getWrapper('list-separator')->render());
		} else {
			$controlW->addHtml($control->control()->render());
		}

		$controlW->addHtml($control->error()->render());

		return $controlW->render(1);
	}


	/**
	 * Renders label
	 * @param   string  control name
	 * @return  string
	 */
	protected function renderLabel($name)
	{
		$labelW = $this->getWrapper('label');
		
		$label = $this->form[$name]->label();
		if ($label instanceof Html)
			$labelW->addHtml($label->render());

		return $labelW->render(1);
	}


	protected function getWrapper($type)
	{
		if (!array_key_exists($type, $this->wrappers))
			throw new Exception("Wrapper $type does not exists.");

		return ($this->wrappers[$type] instanceof Html) ? clone $this->wrappers[$type] : Html::el($this->wrappers[$type]);
	}

	protected function preparePart($wrapper) { return $wrapper; }
	protected function preparePair($wrapper) { return $wrapper; }
	protected function prepareControl($wrapper) { return $wrapper; }
	protected function prepareLabel($wrapper) { return $wrapper; }

}