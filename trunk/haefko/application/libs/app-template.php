<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 * @subpackage  View
 */


require_once dirname(__FILE__) . '/../../libs/template.php';


/**
 * Extends Template class by templates extending and inheriting
 */
class AppTemplate extends Template
{


	/**
	 * Constrctor
	 * @return  void
	 */
	public function __construct($file = null, Cache $cache = null)
	{
		parent::__construct($file, $cache);
		$this->tplFunctions['link'] = '$controller->url';

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
		return parent::subTemplate(Application::get()->path . "/templates/$file.phtml");
	}

	

	/**
	 * Sets extending template filename
	 * @param string $file
	 * @return Template
	 */
	public function setExtendsFile($file = null)
	{
		if (empty($file))
			return parent::setExtendsFile($file);

		$app = Application::get()->path;
		$core = Application::get()->corePath . '/application';
		$file = "/templates/$file.phtml";
		if (!file_exists($app . $file)) {
			if (file_exists($core . $file))
				return parent::setExtendsFile($core . $file);
		}

		return parent::setExtendsFile($app . $file);
	}


}