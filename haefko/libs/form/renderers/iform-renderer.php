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


interface IFormRenderer
{


	/**
	 * Sets Form
	 * @param Form $form
	 * @return Form
	 */
	public function setForm(Form $form);


	/**
	 * Renders form (or part of form)
	 * @param string $part
	 * @return string
	 */
	public function render($part = null);


}