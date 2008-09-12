<?php

/**
 * Haefko - your php5 framework
 *
 * @author      Jan Skrasek <skrasek.jan@gmail.com>
 * @copyright   Copyright (c) 2008, Jan Skrasek
 * @link        http://haefko.programujte.com
 * @version     0.8
 * @package     Haefko
 */



/**
 * DbDriver interface
 */
interface IDbDriver
{



    public function connect(array $config);
    public function query($sql);
    public function fetch($assoc);
    public function escape($value, $type);
    public function affectedRows();
    public function columnsMeta();
    public function rowCount();



}