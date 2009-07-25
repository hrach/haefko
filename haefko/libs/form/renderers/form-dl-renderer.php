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


/**
 * Form renderer which wraps into definition list
 */
class FormDlRenderer extends FormRenderer
{


	/** @var array - Wrappers */
	public $wrappers = array(
		'part' => 'dl',
		'pair' => null,
		'label' => 'dt',
		'control' => 'dd',
		'button-separator' => null,
		'list-separator' => 'br',
	);


	/**
	 * Prepares part
	 * @param   Html   wrapper
	 * @param   Html   heading
	 * @return  Html
	 */
	protected function preparePart($wrapper, $heading)
	{
		if (empty($heading))
			return $wrapper;

		$heading = Html::el('h3', $heading);
		return $wrapper->prepend($heading->render(0));
	}


	/**
	 * Prepares control
	 * @param   Html         wrapper
	 * @param   FormControl
	 * @return  Html
	 */
	protected function prepareControl($wrapper, $control)
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
	 * @param   Html          wrapper
	 * @param   FormControl
	 * @return  Html
	 */
	protected function prepareLabel($wrapper, $control)
	{
		if ($control->htmlRequired)
			$wrapper->class('required');

		return $wrapper;
	}


}