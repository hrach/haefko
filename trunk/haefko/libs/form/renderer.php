<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8 - $Id$
 * @package     Haefko_Forms
 */


require_once dirname(__FILE__) . '/irenderer.php';


abstract class FormRenderer extends Object implements IFormRenderer
{


	/** @var bool */
	public $js = true;

	/** @var Html */
	public $container;

	/** @var Html */
	public $body;

	/** @var Html */
	public $control;

	/** @var Html */
	public $controlSeparator = 'br';

	/** @var Html */
	public $label;

	/** @var Html */
	public $block;

	/** @var Form */
	public $form;


	/**
	 * Constructor
	 * @return  void
	 */
	public function __construct(Form $form)
	{
		$this->form = $form;

		$this->container = Html::el($this->container);
		$this->body = Html::el($this->body, null, array('class' => 'form-container'));
		$this->block = Html::el($this->block);
		$this->control = Html::el($this->control);
		$this->controlSeparator = Html::el($this->controlSeparator);
		$this->label = Html::el($this->label);
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
			case 'body':
				return $this->renderBody($attrs);
			case 'block':
				return $this->renderBlock($attrs);
			default:
				throw new Exception("Unknow type $part of render method.");
		}
	}


	/**
	 * Renders form
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderForm($attrs)
	{
		return $this->renderStart($attrs)
		     . $this->renderBody()
		     . $this->renderEnd();
	}


	/**
	 * Renders form start tag
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderStart($attrs)
	{
		# set attributes
		$this->container->setAttrs(&$attrs[0]);

		return $this->container->startTag()
		     . $this->form->startTag();
	}


	/**
	 * Renders form end tag
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderEnd()
	{
		return $this->form->endTag()
		     . $this->container->endTag();
	}


	/**
	 * Renders body
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderBody($attrs = array())
	{
		$this->prepareBody($attrs);

		foreach ($this->form as $control) {
			if (
				# when is set control seletion, check if the control is on the list
				(!empty($attrs[1]) && !in_array($control->name, (array) $attrs[1])) ||
				# when is control selection empty, chech if the control has been rendered
				(empty($attrs[1]) && $control->isRendered())
			) continue;

			# all buttons render in one row
			if ($control instanceof FormSubmitControl) {
				$this->prepareBlock();
				$this->prepareControl();
				$this->block->setHtml($this->renderLabel($control->name));

				foreach ($this->form as $c) {
					if (!$c->isRendered() && ($c instanceof FormSubmitControl))
						$this->control->setHtml($c->control());
				}

				$this->block->setHtml($this->control->render());
				$this->body->setHtml($this->block->render());
			} else {
				$this->body->setHtml($this->renderBlock(array($control->name)));
			}
		}

		return $this->body->render();
	}


	/**
	 * Renders block of control and label
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderBlock($attrs)
	{
		if (!isset($this->form[$attrs[0]]))
			throw new Exception('Undefined form control in render-block.');

		if ($this->form[$attrs[0]] instanceof FormHiddenControl)
			return;

		$this->prepareBlock();

		$this->block->setHtml($this->renderLabel($attrs[0]))
		            ->setHtml($this->renderControl($attrs[0]));

		return $this->block->render();
	}


	/**
	 * Renders control
	 * @param   string  control name
	 * @return  string
	 */
	protected function renderControl($name)
	{
		$this->prepareControl();

		$this->control->setHtml($this->form[$name]->control());
		if ($this->form[$name] instanceof FormCheckboxControl)
			$this->control->setHtml($this->form[$name]->label());

		$this->control->setHtml($this->form[$name]->errors());

		return $this->control->render();
	}


	/**
	 * Renders label
	 * @param   string  control name
	 * @return  string
	 */
	protected function renderLabel($name)
	{
		$this->prepareLabel();


		if ($this->form[$name] instanceof FormCheckboxControl)
			return $this->label->render();

		$this->label->setHtml($this->form[$name]->label());
		return $this->label->render();
	}


	protected function prepareBody($attrs)
	{
		$this->body->clear();
		$this->body->setAttrs((array) @$attrs[2]);
	}


	protected function prepareBlock()
	{
		$this->block->clear();
	}


	protected function prepareControl()
	{
		$this->control->clear();
	}


	protected function prepareLabel()
	{
		$this->label->clear();
	}


}