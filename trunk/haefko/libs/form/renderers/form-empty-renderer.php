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


class FormEmptyRenderer extends FormRenderer
{

	/** @var array - Wrappers */
	public $wrappers = array(
		'part' => null,
		'pair' => null,
		'label' => null,
		'control' => null,
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


}