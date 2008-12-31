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


	protected function prepareBlock()
	{
		parent::prepareBlock();
		$this->block->toggleClass('odd');
	}


}