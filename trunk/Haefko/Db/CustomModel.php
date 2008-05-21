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



abstract class CustomModel extends DibiTable
{



    protected $controller;
    protected $table;



    public function __construct(& $controller)
    {
        Db::connect();

        $this->controller = $controller;
        if (!empty(self::$table)) {
            $this->name = self::$table;
        }

        parent::__construct();
    }



}