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
		$this->body = Html::el($this->body);
		$this->block = Html::el($this->block);
		$this->control = Html::el($this->control);
		$this->label = Html::el($this->label);
	}


	/**
	 * Render form $part
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
	 * Render form
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
	 * Render form
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderStart($attrs)
	{
		# set attributes
		$this->container->setAttrs($attrs[0]);

		return $this->form->startTag()
		     . $this->container->startTag();
	}


	/**
	 * Render form
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderEnd()
	{
		return $this->container->endTag()
		     . $this->form->endTag();
	}


	/**
	 * Render body
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

			$this->body->setHtml($this->renderBlock(array($control->name)));
		}

		return $this->body->render();
	}


	/**
	 * Render block of control and label
	 * @param   array  params and attributes
	 * @return  string
	 */
	protected function renderBlock($attrs)
	{
		$this->prepareBlock();

		$this->block->setHtml($this->renderLabel($attrs[0]))
		            ->setHtml($this->renderControl($attrs[0]));

		$this->form[$attrs[0]]->increment();
		return $this->block->render();
	}


	/**
	 * Render control
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
	 * Render label
	 * @param   string  control name
	 * @return  string
	 */
	protected function renderLabel($name)
	{
		$this->prepareLabel();

		if ($this->form[$name]->htmlRequired)
			$this->label->class = 'required';

		if ($this->form[$name] instanceof FormCheckboxControl)
			return $this->label->render();

		$this->label->setHtml($this->form[$name]->label());
		return $this->label->render();
	}


	protected function prepareBody($attrs)
	{
		$this->body->clearContent();
		$this->body->setAttrs((array) $attrs[2]);
	}


	protected function prepareBlock()
	{
		$this->block->clearContent();
	}


	protected function prepareControl()
	{
		$this->control->clearContent();
	}


	protected function prepareLabel()
	{
		$this->label->clearContent();
	}


}