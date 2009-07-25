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
 * @subpackage  Templates
 */


require_once dirname(__FILE__) . '/../../libs/template.php';


class AppTemplate extends Template
{

	/** Application */
	protected $application;


	/**
	 * Constrctor
	 * @param string $file template file
	 * @param Cache $cache
	 * @return AppTemplate
	 */
	public function __construct($file = null, Cache $cache = null)
	{
		parent::__construct($file, $cache);
		$this->tplFunctions['link'] = '$controller->url';
		$this->tplTriggers['extends'] = array($this, 'cbExtendsTrigger');

		$this->application = Application::get();
		$this->getHelper('html');
		$this->getHelper('filter');
		$this->setVar('base', Http::$baseURL);
	}


	/**
	 * Includes templatefile
	 * @param   string    filename
	 * @return  string
	 */
	public function subTemplate($file)
	{
		$file = $this->application->path . "/templates/$file."
		      . $this->application->controller->routing->ext;
		return parent::subTemplate($file);
	}


	/**
	 * Callback for extending template
	 * @param string $expression
	 * @return string
	 */
	protected function cbExtendsTrigger($expression)
	{
		$expression = $this->application->path . '/templates/'
			. substr($expression, 1, -1) . '.'
			. $this->application->controller->routing->ext;
		return parent::cbExtendsTrigger("'$expression'");
	}


}