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
 * Interface dbDriveru
 */
interface IDbDriver
{


    public function connect(array $config);
    public function query($sql);
    public function fetch($type);
    public function quote($string, $type);
    public function escape($string);
    public function getColumnsMeta();
    public function rowCount();


}