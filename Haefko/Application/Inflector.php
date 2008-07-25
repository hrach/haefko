<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.7
 * @package     Haefko
 */



/**
 * Trida Inflector spravuje jmenne konvence
 */
class Inflector
{



    public static function controllerClass($name, $namespace = null)
    {
        return strCamelize($namespace) . strCamelize($name) . 'Controller';
    }



    public static function controllerFile($class)
    {
        return 'controllers/' . strDash($class) . '.php';
    }



    public static function modelClass($name, $namespace = null)
    {
        return strCamelize($namespace) . strCamelize($name) . 'Model';
    }



    public static function modelFile($class)
    {
        return 'models/' . strDash($class) . '.php';
    }



    public static function helperClass($name)
    {
        return ucfirst(strtolower($name)) . 'Helper';
    }



    public static function helperFile($name)
    {
        return 'helpers/' . strDash($name) . '.php';
    }



    public static function actionName($name, $ajax = false)
    {
        return strCamelize($name);
    }



    public static function layoutFile($ext, $name, $namespace, $theme)
    {
        $path  = 'views/';
        $path .= ($theme) ? "$theme/" : '';
        $path .= ($namespace) ? strDash($namespace) . '-' : '';
        $path .= strDash($name) .".$ext";

        return $path;
    }



    public static function viewFile($ext, $name, $namespace, $theme, $controller, $service)
    {
        $path  = 'views/';
        $path .= ($theme) ? "$theme/" : '';
        $path .= ($namespace) ? strDash($namespace) . '-' : '';
        $path .= strDash($controller) . '/';
        $path .= ($service) ? 'service/' : '';
        $path .= strDash($name) . ".$ext";

        return $path;
    }



    public static function errorViewFile($ext, $name, $theme)
    {
        $path  = "views/";
        $path .= ($theme) ? "$theme/" : '';
        $path .= "errors/$name.$ext";

        return $path;
    }



    public static function elementFile($ext, $path)
    {
        return 'views/' . strSanitizeUrl($path) . ".$ext";
    }


}