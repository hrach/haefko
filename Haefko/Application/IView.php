<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://hf.programujte.com
 * @version     0.6 alfa
 * @package     HF
 */



interface IView
{

    function render();

    function __set($name, $value);

    function &__get($name);

}