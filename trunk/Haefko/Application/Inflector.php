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



require_once dirname(__FILE__) . '/../Strings.php';



/**
 * Trida Inflector spravuje jmenne konvence
 */
class Inflector
{



    public static function controllerClass($name, $namespace = null)
    {
        return Strings::camelize($namespace) . Strings::camelize($name) . 'Controller';
    }



    public static function controllerFile($name, $namespace = false)
    {
        if (!$namespace)
            return 'controllers/' . Strings::dash($name) . '.php';
        else
            return 'controllers/' . Strings::dash($namespace) . '/' . Strings::dash($name) . '.php';
    }



    public static function modelClass($name, $namespace = null)
    {
        return Strings::camelize($namespace) . Strings::camelize($name) . 'Model';
    }



    public static function modelFile($name, $namespace = false)
    {
        if (!$namespace)
            return 'models/' . Strings::dash($name) . '.php';
        else
            return 'models/' . Strings::dash($namespace) . '/' . Strings::dash($name) . '.php';
    }



    public static function helperClass($name)
    {
        return ucfirst(strtolower($name)) . 'Helper';
    }



    public static function helperFile($name)
    {
        return 'helpers/' . strtolower($name) . '.php';
    }



    public static function actionName($name)
    {
        return Strings::camelize($name) . 'Action';
    }



    public static function layoutFile($ext, $name, $namespace, $theme)
    {
        $path  = 'views/';
        $path .= ($theme) ? "$theme/" : '';
        $path .= ($namespace) ? Strings::dash($namespace) . '-' : '';
        $path .= Strings::dash($name) .".$ext";

        return $path;
    }



    public static function viewFile($ext, $name, $namespace, $theme, $controller, $service)
    {
        $path  = 'views/';
        $path .= ($theme) ? "$theme/" : '';
        $path .= ($namespace) ? Strings::dash($namespace) . '-' : '';
        $path .= Strings::dash($controller) . '/';
        $path .= ($service) ? 'service/' : '';
        $path .= Strings::dash($name) . ".$ext";

        return $path;
    }



    public static function errorViewFile($ext, $name)
    {
        return "views/errors/$name.$ext";
    }



    public static function elementFile($ext, $path)
    {
        return 'views/' . Strings::sanitizeUrl($path) . ".$ext";
    }


}