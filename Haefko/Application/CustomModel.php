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
        Db::connect();

        $this->controller = $controller;
        if (!empty($this->table)) {
            $this->name = $this->table;
        }

        parent::__construct();
    }



}