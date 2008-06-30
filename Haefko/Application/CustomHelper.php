<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.6
 * @package     Haefko
 */



/**
 * Zakladni trida pro view helpery
 */
class CustomHelper
{



    protected $controller;



    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->controller = Application::getInstance()->controller;
    }



}