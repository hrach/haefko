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


class FormDivRenderer extends FormRenderer
{


	/** @var Html */
	public $container = '';

	/** @var Html */
	public $body = 'div';

	/** @var Html */
	public $control = '';

	/** @var Html */
	public $label = '';

	/** @var Html */
	public $block = 'div';


	protected function prepareBody($attrs)
	{
		parent::prepareBody($attrs);
		if (!empty($attrs[0]))
			$this->body->prepend = "<h3>$attrs[0]</h3>";
		else
			$this->body->prepend = '';
	}


	protected function prepareBlock()
	{
		parent::prepareBlock();
		$this->block->toggleClass('odd');
	}


}