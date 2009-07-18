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
 * @property
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

		$this->tplFunctions['link'] = array($this, 'cbLinkFunction');

		$this->getHelper('html');
		$this->getHelper('filter');
		$this->setVar('base', Http::$baseUri);
	}


	protected function cbLinkFunction($expression)
	{
		return '<?php echo $controller->url(' . $expression . ') ?>';
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

	

	public function setExtendsFile($file = null)
	{
		if (empty($file)) {
			$this->extendsFile = '';
			return $this;
		}

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