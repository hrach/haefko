<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8
 * @package     Haefko_Forms
 */


class FormDlRenderer extends FormRenderer
{


	/** @var Html */
	public $container = '';

	/** @var Html */
	public $body = 'dl';

	/** @var Html */
	public $control = 'dd';

	/** @var Html */
	public $label = 'dt';

	/** @var Html */
	public $block;


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