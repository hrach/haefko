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
 */


ob_start();
$startTime = microtime(true);
require_once dirname(__FILE__) . '/libs/loaders/haefko-loader.php';


$haefkoLoader = new HaefkoLoader();
$haefkoLoader->register();


/**
 * Processes the framework url
 * @param string $url url
 * @param array $args rewrite args
 * @param array|false $params rewrite params
 * @return string
 */
function frameworkUrl($url, $args = array(), $params = false) {
	if (empty($url))
		$url = Http::$request->request;
	else
		$url = preg_replace('#\<\:([a-z0-9]+)\>#ie', "isset(\$args['\1']) ? \$args['\1'] : ''", $url);

	if ($params !== false) {
		$p = array();
		$params = array_merge($_GET, (array) $params);
		foreach ($params as $name => $value) {
			if ($value == null) continue;
			$p[] = urlencode($name) . '=' . urlencode($value);
		}

		if (!empty($p))
			$url .= '?' . implode('&', $p);
	}

	return Http::$baseURL . '/' . ltrim($url, '/');
}