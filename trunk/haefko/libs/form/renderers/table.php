<?php


class FormTableRenderer extends FormRenderer
{


	/** @var Html */
	public $container = '';

	/** @var Html */
	public $body = 'table';

	/** @var Html */
	public $control = 'td';

	/** @var Html */
	public $label = 'th';

	/** @var Html */
	public $block = 'tr';


	protected function prepareBody($attrs)
	{
		parent::prepareBody($attrs);
		if (!empty($attrs[0]))
			$this->body->prepend = "<h3>$attrs[0]</h3>";
		else
			$this->body->prepend = '';
	}


	protected function prepareControl()
	{
		parent::prepareControl();
		$this->control->toggleClass('odd');
	}


}