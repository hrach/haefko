<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.9 - $Id: template.php 106 2009-06-07 13:45:54Z skrasek.jan
 * @package     Haefko
 * @package     Templates
 */


require_once dirname(__FILE__) . '/object.php';
require_once dirname(__FILE__) . '/cache.php';
require_once dirname(__FILE__) . '/itemplate.php';
require_once dirname(__FILE__) . '/template/filter-helper.php';


/**
 * Template class provides powerful templating system
 * @property string $file
 * @property string $temp
 * @property string $vars
 */
class Template extends Object implements ITemplate
{

	/** @var array */
	public static $defaultTplKeywords = array(
		'{if %%}' => '<?php if (\1): ?>',
		'{elseif %%}' => '<?php ; elseif (\1): ?>',
		'{for %%}' => '<?php for (\1): ?>',
		'{foreach %%}' => '<?php foreach (\1): ?>',
		'{while %%}' => '<?php while (\1): ?>',
		'{/if}' => '<?php endif; ?>',
		'{/for}' => '<?php endfor; ?>',
		'{/foreach}' => '<?php endforeach; ?>',
		'{/while}' => '<?php endwhile; ?>',
		'{else}' => '<?php ; else: ?>',
	);

	/** @var array */
	public static $defaultTplTriggers = array(
		'php' => array('Template', 'cbPhpTrigger'),
		'extends' => array('Template', 'cbExtendsTrigger'),
		'assign' => array('Template', 'cbAssignTrigger'),
	);

	/** @var array */
	public static $defaultTplFunctions = array(
		'include' => '$template->subTemplate',
		'mimetype' => '$template->setMimetype',
	);

	/** @var array */
	public static $defaultTplFilters = array();



	/** @var array */
	public $tplKeywords = array();

	/** @var array */
	public $tplTriggers = array();

	/** @var array */
	public $tplFunctions = array();

	/** @var array */
	public $tplFilters = array();

	/** @var Cache */
	protected $cache;

	/** @var string */
	protected $file;

	/** @var string */
	protected $extendsFile;

	/** @var array */
	protected $vars = array();

	/** @var array */
	protected $helpers = array();

	/** @var array */
	protected $registeredBlocks = array();

	/** @var bool */
	private $__hasExtends = false;

	/** @var bool */
	private $__hasBlocks = false;


	/**
	 * Constructor
	 * @param string $file template filename
	 * @param string $temp path for cache templates
	 * @return Template
	 */
	public function __construct($file = null, Cache $cache = null)
	{
		$this->tplKeywords = self::$defaultTplKeywords;
		$this->tplTriggers = self::$defaultTplTriggers;
		$this->tplFunctions = self::$defaultTplFunctions;
		$this->tplFilters = self::$defaultTplFilters;

		if (!($cache instanceof Cache))
			$cache = new Cache();

		if ($file !== null)
			$this->setFile($file);

		$this->cache = $cache;
		$this->getHelper('filter');
	}


	/**
	 * Sets template file name
	 * @param string $file template filename
	 * @throws RuntimeException
	 * @return Template
	 */
	public function setFile($file)
	{
		if (!file_exists($file))
			throw new RuntimeException("Template file '$file' was not found.");

		$this->file = $file;
	}


	/**
	 * Returns template file name
	 * @param string
	 */
	public function getFile()
	{
		return $this->file;
	}


	/**
	 * Sets variable
	 * @param string $key variable name
	 * @param mixed $val content
	 * @throws BadMethodCallException
	 * @return Template
	 */
	public function setVar($key, $val)
	{
		if (empty($key))
			throw new BadMethodCallException('Key must not be empty.');

		$this->vars[$key] = $val;
		return $this;
	}


	/**
	 * Returns variable value
	 * @param string $key variable name
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	public function getVar($key)
	{
		if (empty($key))
			throw new BadMethodCallException('Key must not be empty.');

		if (isset($this->vars[$key]))
			return $this->vars[$key];
		
		return null;
	}


	/**
	 * Sets multi variables values
	 * @param array $vars associative array of variables
	 * @return Template
	 */
	public function setVars($vars)
	{
		foreach ($vars as $key => $val)
			$this->setVar($key, $val);

		return $this;
	}

	
	/**
	 * Returns variables
	 * @return array
	 */
	public function getVars()
	{
		return $this->vars;
	}


	/**
	 * Checks if the variable is set
	 * @param string $key variable name
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->vars[$key]);
	}


	/**
	 * Unsets variable value
	 * @param string $key variable name
	 */
	public function __unset($key)
	{
		unset($this->vars[$key]);
	}


	/**
	 * Sets variable value
	 * @param string $key variable name
	 * @param mixed $value value
	 */
	public function __set($key, $value)
	{
		$this->setVar($key, $value);
	}


	/**
	 * Returns variable value
	 * @param string $key variable name
	 * @return mixed
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this->vars))
			return $this->vars[$key];
		else
			parent::__get($key);
	}


	/**
	 * Sets extending template filename
	 * @param string $file
	 * @return Template
	 */
	public function setExtendsFile($file = null)
	{
		if (empty($file)) {
			$file = null;
			$this->__hasExtends = false;
		} else {
			$this->__hasExtends = true;
		}

		$this->extendsFile = $file;
		return $this;
	}


	/**
	 * Returns extending template filename
	 * @return string|null
	 */
	public function getExtendsFile()
	{
		return $this->extendsFile;
	}


	/**
	 * Returns true if template has extending template
	 * @return bool
	 */
	public function hasExtendsFile()
	{
		return $this->extendsFile != null;
	}


	/**
	 * Returns clone of template
	 * @return Template
	 */
	public function getClone()
	{
		$clone = clone $this;
		return $clone->setExtendsFile();
	}


	/**
	 * Loads helper
	 * @param string $name helper name
	 * @param string $var variable name in which will be helper instance
	 * @return Helper
	 */
	public function getHelper($name, $var = null)
	{
		static $pairs = array();

		if (!array_key_exists($name, $pairs) || $pairs[$name] != $var) {
			if (empty($var))
				$var = strtolower($name);

			$class = ucfirst(strtolower($name)) . 'Helper';
			$pairs[$name] = $var;
			$this->helpers[$var] = new $class($this, $var);
		}

		return $this->helpers[$var];
	}


	/**
	 * Includes sub-template file
	 * @param string template filename
	 * @return string
	 */
	public function subTemplate($file)
	{
		$template = $this->getClone();
		$template->setFile($file);
		return $template->render();
	}


	/**
	 * Sends mimetype header
	 * @param string $mimetype
	 */
	protected function setMimetype($mimetype = 'text/html')
	{
		header("Content-type: $mimetype");
	}


	/**
	 * Register functions block
	 * @param string $id
	 * @param string $function function name
	 * @param string $mode mode - append / prepend
	 * @return Template
	 */
	public function registerBlock($id, $function, $mode = '')
	{
		if (!isset($this->registeredBlocks[$id])) {
			$this->registeredBlocks[$id] = array(
				'prepend' => array(),
				'append' => array(),
				'' => array()
			);
		}

		$this->registeredBlocks[$id][$mode][] = $function;
		return $this;
	}


	/**
	 * Renders functions blocks and append / preppend content
	 * @param string $id function name
	 * @param array $vars defined variables
	 * @return string
	 */
	public function getFilterBlock($id, $vars)
	{
		if (!isset($this->registeredBlocks[$id]))
			return;

		$render = '';
		$blocks = $this->registeredBlocks[$id];
		foreach (array_reverse($blocks['prepend']) as $func)
			$render .= call_user_func($func, $vars);

		if (isset($blocks[''][0]))
			$render .= call_user_func($blocks[''][0], $vars);

		foreach (array_reverse($blocks['append']) as $func)
			$render .= call_user_func($func, $vars);

		return $render;
	}


	/**
	 * Renders template a return content
	 * @return string
	 */
    public function render()
    {
		if (!file_exists($this->file))
			throw new Exception("Template file '{$this->file}' was not found.");

		$cacheFile = 'template_' . md5($this->file);
		if (!$this->cache->isCached($cacheFile))
			$this->createTemplateTemp($cacheFile);

		extract($this->vars);
		extract($this->helpers);

		$template = $this;
		if (class_exists('Application', false)) {
			$controller = Controller::get();
			$application = Application::get();
		}

		$___pre = ob_get_contents();
		ob_clean();

		include $this->cache->getFilename($cacheFile);
		$return = ob_get_contents();
		ob_clean();

		if ($this->hasExtendsFile()) {
			$clone = $this->getClone();
			$clone->setFile($this->getExtendsFile());
			$return = $clone->render() . $return;
		}

		echo $___pre;
		return $return;
	}


	/**
	 * Creates php template file from pseudo template style
	 * @param string $cacheFileName
	 */
	protected function createTemplateTemp($cacheFile)
	{
		$file = file_get_contents($this->file);

		# comments
		$file = preg_replace('#\{\*.+\*\}#s', '', $file);

		# keywords
		$keywords_k = $keywords_v = array();
		foreach ($this->tplKeywords as $key => $val) {
			$keywords_k[] = '#' . str_replace('%%', '(.+)',
				preg_quote($key, '#')) . '#U';
			$keywords_v[] = $val;
		}
		$file = preg_replace($keywords_k, $keywords_v, $file);

		# variables
		$file = preg_replace_callback('#\{(?:(?:=([^|\}]+?))|(\$[^|\}]+?))(?:\|([^\}]+?))?\}#U',
			array($this, '__cbVariables'), $file);

		# extending
		$file = preg_replace_callback('#\{block(?: (append|prepend))? (?:\#([^}]+))\}(.*)\{/block\}#Us',
			array($this, '__cbBlock'), $file);

		# triggers
		$triggers = implode('|', array_keys($this->tplTriggers));
		$file = preg_replace_callback('#\{(' . $triggers .')\s+([^|]+)?\}#U',
			array($this, '__cbTriggers'), $file);

		# functions
		$functions = implode('|', array_keys($this->tplFunctions));
		$file = preg_replace_callback('#\{(' . $functions .')(?:\s+([^|]+))?(?:\|([^\}]+?))?\}#U',
			array($this, '__cbFunctions'), $file);


		# we have extends file and no blocks
		if ($this->__hasExtends === true && $this->__hasBlocks === false) {
			$pos = strrpos($file, '//--EXLUDE--//'); # length 14 + 2
			if ($pos !== false) {
				$fileS = substr($file, 0, $pos + 16);
				$fileE = substr($file, $pos + 16);
				$fileE = $this->__cbBlock(array('', '', 'content', $fileE));
				$file = $fileS . $fileE;
			} else {
				$file = $this->__cbBlock(array('', '', 'content', $file));
			}
		}

		$this->cache->set($cacheFile, $file, array(
			'files' => array($this->file)
		));
	}


	/**
	 * Returns template code for variables
	 * @param array $matches
	 * @return string
	 */
	protected function __cbVariables($matches)
	{
		return '<?php echo ' . $this->parseFilters($matches[1] . @$matches[2],
			@$matches[3], @$matches[2]) . ' ?>';
	}


	/**
	 * Returns template code for blocks
	 * @param array $expression
	 * @return string
	 */
	protected function __cbBlock($matches)
	{
		$this->__hasBlocks = true;
		$id = substr(md5($matches[2]), 0, 10);
		if (!empty($matches[1]))
			$name = '_filter_block_' . $id . '_' . substr(md5($this->file), 0, 10);
		else
			$name = '_filter_block_' . $id;

		return "<?php if (!function_exists('$name')) { "
		     . "\$template->registerBlock('$id', '$name', '$matches[1]');"
		     . "function $name() { extract(func_get_arg(0)); ?>$matches[3]<?php }} "
			 . "if (!\$template->hasExtendsFile()) "
			 . "echo \$template->getFilterBlock('$id', get_defined_vars()); ?>";
	}


	/**
	 * Calls template trigger callback
	 * @param array $matches
	 * @return string|null
	 */
	protected function __cbTriggers($matches)
	{
		$cb = $this->tplTriggers[$matches[1]];
		return call_user_func($cb, $matches[2]);
	}


	/**
	 * Calls template function callback
	 * @param array $matches
	 * @return string|null
	 */
	protected function __cbFunctions($matches)
	{
		$expression = $this->tplFunctions[$matches[1]] . '(' . @$matches[2] . ')';
		if (!empty($matches[3]))
			$expression = $this->parseFilters($matches[3], $expression);

		return "<?php echo $expression ?>";
	}


	/**
	 * Callback for extending template
	 * @param string $expression
	 * @return string
	 */
	protected function cbExtendsTrigger($expression)
	{
		$this->__hasExtends = true;
		return "<?php \$template->setExtendsFile($expression) ?>";
	}


	/**
	 * Callback for php raw expression
	 * @param string $expression
	 * @return string
	 */
	protected function cbPhpTrigger($expression)
	{
		return "<?php $expression ?>";
	}


	/**
	 * Callback for assign function
	 * @param string $expression
	 * @return string
	 */
	protected function cbAssignTrigger($expression)
	{
		$space = strpos($expression, ' ');
		$var = substr($expression, 1, $space - 1);
		$val = substr($expression, $space);
		return "<?php \$template->setVar('$var', $val) //--EXLUDE--//?>";
	}


	/**
	 * Parses filter expression
	 * @param string $variable
	 * @param string $expression
	 * @return string
	 */
	private function parseFilters($variable, $expression, $varName = null)
	{
		$filters = array();
		if (empty($expression))
			$expression = array();
		else
			$expression = explode('|', $expression);

		foreach ($expression as $filter) {
			if (preg_match('#([^:]+)(?:\:("[^"]+"|[^\:]+))+#', $filter, $match)) {
				array_shift($match);
				$filters[array_shift($match)] = $match;
			} else {
				$filters[$filter] = array();
			}
		}

		if ($varName !== null) {
			if (preg_match('#^\$(\w+)#', $varName, $matches)) {
				if (isset($this->helpers[$matches[1]]))
					$varName = $matches[1];
				else
					$varName = null;
			} else {
				$varName = null;
			}
		}

		if ($varName === null) {
			if (isset($filters['raw']))
				unset($filters['raw']);
			elseif (!isset($filters['escape']))
				$filters['escape'] = array();
		}

		foreach ($filters as $name => $args) {
			if (isset($this->tplFilters[$name]))
				$name = $this->tplFilters[$name];
			array_unshift($args, $variable);
			$variable = "$name(" . implode(',', $args) . ")";
		}

		return $variable;
	}


}