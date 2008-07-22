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



    public static function controllerFile($name, $namespace = false)
    {
        if (!$namespace)
            return 'controllers/' . strDash($name) . '.php';
        else
            return 'controllers/' . strDash($namespace) . '/' . strDash($name) . '.php';
    }



    public static function modelClass($name, $namespace = null)
    {
        return strCamelize($namespace) . strCamelize($name) . 'Model';
    }



    public static function modelFile($name, $namespace = false)
    {
        if (!$namespace)
            return 'models/' . strDash($name) . '.php';
        else
            return 'models/' . strDash($namespace) . '/' . strDash($name) . '.php';
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
        return strCamelize($name) . 'Action';
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