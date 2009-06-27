<?php

/**
 * Haefko - your php5 framework
 *
 * @name 
 * @author      Jan Skrasek
 * @copyright   Copyright (c) 2007 - 2009, Jan Skrasek
 * @link        http://haefko.skrasek.com
 * @license     http://www.opensource.org/licenses/mit-license.html
 * @version     0.8.5 - $Id$
 * @package     Haefko_Application
 */


ob_start();
$startTime = microtime(true);
require_once dirname(__FILE__) . '/libs/loaders/haefko-loader.php';


$haefkoLoader = new HaefkoLoader();
$haefkoLoader->register();