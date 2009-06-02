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


class FormTableRenderer extends FormRenderer
{


	public $wrappers = array(
		'part' => 'table',
		'pair' => 'tr',
		'label' => 'th',
		'control' => 'td',
		'button-separator' => null,
		'list-separator' => 'br',
	);



	protected function preparePart($wrapper, $heading)
	{
		if (empty($heading))
			return $wrapper;

		$heading = Html::el('h3', $heading);
		return $wrapper->prepend($heading->render(0));
	}


	protected function preparePair($wrapper)
	{
		static $i = 0;

		if ($i++ % 2)
			$wrapper->class('odd');

		return $wrapper;
	}


}