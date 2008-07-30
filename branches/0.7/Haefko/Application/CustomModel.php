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
 * Abstraktní tøída pro model pro zapouzdtøedí bidi knihovny
 */
abstract class CustomModel extends DibiTable
{



    protected $controller;
    protected $table;



    /**
     * Konstruktor
     * @param   CustomController    controller
     * @return  void
     */
    public function __construct(CustomController & $controller)
    {
        $this->controller = $controller;
        Db::connect();

        if (empty($this->name))
            $this->name = strtolower(strRightTrim(get_class($this), 'Model'));

        parent::__construct();
    }



    /**
     * Metoda init je zavolana vzdy pred zavolanim action
     */
    public function init()
    {}



    /**
     * Metoda renderInit je zavolana vzdy pred vyrenderovanim sablony, po zavolani action
     */
    public function prepareView()
    {}



    /**
     * Metoda prepareLayout je zavolana vzdy pred vyrenderovanim layout sablony
     */
    public function prepareLayout()
    {}



}