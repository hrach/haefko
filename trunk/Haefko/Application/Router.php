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



require_once dirname(__FILE__) . '/../Http.php';
require_once dirname(__FILE__) . '/../Strings.php';



/**
 * Trida Router routuje celou vasi aplikace
 */
class Router
{



    public static $url = array();
    public static $namespace;
    public static $controller;
    public static $action;
    public static $service;
    public static $args = array();
    public static $routing = false;



    /**
     * Naparsuje url pro dalsi zpracovani
     */
    public static function __staticConstruct()
    {
        self::$url = Strings::urlToArray(Http::getRequestUrl());
    }



    /**
     * Vrati stavajici URL
     * @return  string
     */
    public static function getUrl()
    {
        static $url;
        if (empty($url)) {
            $url = Http::getRequestUrl();
        }

        return $url;;
    }



    /**
     * Prida sluzbu pro renderovani alternativniho obsahu
     * @param   string  jmeno sluzby
     * @return  bool
     */
    public static function addService($name)
    {
        $i = count(self::$url);
        if (!empty($name) && empty(self::$service) && $i > 0) {
            if (self::$url[$i - 1] == $name) {
                self::$service = $name;
                array_pop(self::$url);
                return true;
            }
        }
        return false;
    }



    /**
     * Pripoji se k url
     * @param   string  routovaci vyraz
     * @param   array   defaultni hodnoty
     * @return  bool
     */
    public static function connect($route, array $defaults = array())
    {
        if (self::$routing) {
            return false;
        }

        $router = array(
            'namespace' => '',
            'controller' => '',
            'action' => ''
        );

        $rule = Strings::urlToArray($route);

        if (isset($rule[count($rule) - 1]) && $rule[count($rule) - 1] == '*') {
            array_pop($rule);
            $i = 1;

            while (count($rule) < count(self::$url)) {
                $rule[] = ":arg$i";
                $i++;
            }
        }

        if (count($rule) < count(self::$url)) {
            return false;
        }

        foreach ($rule as $x => $value) {
            if (!isset(self::$url[$x])) {
                $urlVal = '';
            } else {
                $urlVal = self::$url[$x];
            }

            if (!empty($value) && $value{0} == ':') {
                $value = substr($value, 1);
                if (preg_match('#(.+){(.*)}#U', $value, $match)) {
                    if (empty($match[2]) || $match[2]{0} != '#') {
                        $match[2] = '#^' . $match[2] . '$#';
                    }

                    if (preg_match($match[2], $urlVal)) {
                        $router[$match[1]] = $urlVal;
                    } else {
                        return false;
                    }
                } else {
                    if (!empty($urlVal)) {
                        $router[$value] = $urlVal;
                    } else {
                        return false;
                    }
                }
            } else {
                if ($value != $urlVal) {
                    return false;
                }
            }
        }


        foreach ($defaults as $key => $default) {
            if (empty($router[$key])) {
                $router[$key] = $default;
            }
        }

        if (empty($router['action'])) {
            $router['action'] = 'index';
        }

        self::$namespace = Strings::camelize($router['namespace']);
        self::$controller = Strings::camelize($router['controller']);
        self::$action = Strings::lcfirst(Strings::camelize($router['action']));

        unset($router['namespace'], $router['controller'], $router['action']);

        self::$args = $router;
        self::$routing = true;
        return true;
    }



}



Router::__staticConstruct();