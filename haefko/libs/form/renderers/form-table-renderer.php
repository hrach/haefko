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


require_once dirname(__FILE__) . '/form-renderer.php';


class FormTableRenderer extends FormRenderer
{

	/** @var array - Wrappers */
	public $wrappers = array(
		'part' => 'table',
		'pair' => 'tr',
		'label' => 'th',
		'control' => 'td',
		'button-separator' => null,
	);


	/**
	 * Prepares part
	 * @param Html $wrapper
	 * @param Html $heading
	 * @return Html
	 */
	protected function preparePart($wrapper, $heading)
	{
		if (empty($heading))
			return $wrapper;

		$heading = Html::el('h3', $heading);
		return $wrapper->prepend($heading->render(0));
	}


	/**
	 * Prepares pair
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function preparePair($wrapper, $control)
	{
		static $i = 0;
		if ($i++ % 2)
			$wrapper->class('odd');
		if ($control->htmlRequired)
			$wrapper->class('required');

		return $wrapper;
	}


	/**
	 * Prepares label
	 * @param Html $wrapper
	 * @param FormControl $contrl
	 * @return Html
	 */
	protected function prepareLabel($wrapper, $control)
	{
		if ($control->htmlRequired)
			$wrapper->class('required');

		return $wrapper;
	}


}